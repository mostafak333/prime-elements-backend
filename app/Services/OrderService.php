<?php

namespace App\Services;

use App\Events\UserNotificationCreated;
use App\Models\AddressDetail;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\OrderStatusNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderService
{
    public function __construct(
        private ?EbookAccessCodeService $ebookAccessCodeService = null
    ) {
        if ($this->ebookAccessCodeService === null) {
            $this->ebookAccessCodeService = app(EbookAccessCodeService::class);
        }
    }

    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Order::with('orderItems.product.images', 'paymentMethod', 'deliveryMethod', 'addressDetail', 'user');

        if (! empty($filters['search'])) {
            $query->where('order_number', 'like', "%{$filters['search']}%");
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): Order
    {
        return Order::with('orderItems.product.images', 'paymentMethod', 'deliveryMethod', 'addressDetail', 'user')
            ->findOrFail($id);
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $this->validateStatusTransition($order->status, $status);
        $previousStatus = $order->status;

        $updatedOrder = DB::transaction(function () use ($order, $status, $previousStatus) {
            $order->update(['status' => $status]);

            if ($order->wasChanged('status') && $previousStatus !== $status) {
                $notificationData = [
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'order_reference' => $order->order_number,
                    'status' => $status,
                ];

                DB::afterCommit(function () use ($notificationData) {
                    $user = User::find($notificationData['user_id']);
                    if (! $user) {
                        return;
                    }

                    $notification = new OrderStatusNotification(
                        $notificationData['order_id'],
                        $notificationData['order_reference'],
                        $notificationData['status']
                    );
                    $notification->id = (string) Str::uuid();
                    $user->notifyNow($notification, ['database']);

                    event(new UserNotificationCreated(
                        $user->id,
                        $notification->id,
                        'order_status_changed',
                        $notification->toDatabase($user),
                        now()->toISOString()
                    ));
                });
            }

            return $order->fresh();
        });

        if ($status === 'confirmed' && $this->hasEbookItems($order)) {
            app(EbookDeliveryService::class)->deliverEbooks($updatedOrder);
        }

        return $updatedOrder->load('orderItems.product.images', 'paymentMethod', 'deliveryMethod', 'addressDetail', 'user');
    }

    private function hasEbookItems(Order $order): bool
    {
        return $order->orderItems()->whereHas('product', function ($q) {
            $q->where('is_e_copy', true);
        })->exists();
    }

    private function validateStatusTransition(string $current, string $new): void
    {
        if ($new === 'cancelled') {
            if ($current === 'pending') {
                // Allow cancellation while pending
            } elseif ($current === 'confirmed' || $current === 'out_for_delivery') {
                throw ValidationException::withMessages([
                    'status' => [__('messages.order.cannot_cancel_after_confirmation')],
                ]);
            } else {
                throw ValidationException::withMessages([
                    'status' => [__('messages.order.invalid_status_transition', ['from' => $current, 'to' => $new])],
                ]);
            }
        }

        $flow = [
            'pending' => ['confirmed'],
            'confirmed' => ['out_for_delivery'],
            'out_for_delivery' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        if ($new !== 'cancelled') {
            if (! in_array($new, $flow[$current] ?? [])) {
                throw ValidationException::withMessages([
                    'status' => [__('messages.order.invalid_status_transition', ['from' => $current, 'to' => $new])],
                ]);
            }
        }
    }

    public function getUserOrders(int $perPage = 15)
    {
        $this->ensureUserIsActive();

        $userId = auth()->guard('api-user')->id();

        return Order::with('orderItems.product.images', 'paymentMethod', 'deliveryMethod', 'addressDetail')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function findForUser(int $id): ?Order
    {
        $userId = auth()->guard('api-user')->id();

        return Order::with('orderItems.product.images', 'paymentMethod', 'deliveryMethod', 'addressDetail')
            ->where('user_id', $userId)
            ->find($id);
    }

    public function cancelOrderForUser(Order $order): Order
    {
        $this->ensureUserIsActive();

        if ($order->user_id !== auth()->guard('api-user')->id()) {
            throw ValidationException::withMessages([
                'order' => [__('messages.order.not_found')],
            ]);
        }

        if ($order->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => [__('messages.order.cannot_cancel_after_confirmation')],
            ]);
        }

        return $this->cancelOrderInternal($order);
    }

    private function cancelOrderInternal(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order->refresh();
            if ($order->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => [__('messages.order.cannot_cancel_after_confirmation')],
                ]);
            }
            $this->ebookAccessCodeService->releaseCodesForOrder($order->id);
            $order->update(['status' => 'cancelled']);
            return $order->fresh()->load('orderItems.product.images', 'paymentMethod', 'deliveryMethod', 'addressDetail');
        });
    }

    public function createOrder(array $data): Order
    {
        $this->ensureUserIsActive();

        $userId = auth()->guard('api-user')->id();
        $user = auth()->guard('api-user')->user();
        $setting = Setting::first();
        $cartItems = CartItem::with('product')
            ->where('user_id', $userId)
            ->get();

        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => [__('messages.order.cart_empty')],
            ]);
        }

        return DB::transaction(function () use ($data, $userId, $user, $cartItems, $setting) {
            $this->validateStock($cartItems);

            // Reserve codes atomically for ebooks if needed
            $reservedCodesByProduct = [];
            foreach ($cartItems as $item) {
                if ($item->product->is_e_copy) {
                    $available = \App\Models\EbookAccessCode::where('product_id', $item->product_id)
                        ->whereNull('used_at')
                        ->lockForUpdate()
                        ->count();
                    if ($available < $item->quantity) {
                        throw ValidationException::withMessages([
                            'cart' => [__('messages.ebook.insufficient_codes')],
                        ]);
                    }
                }
            }

            $address = AddressDetail::create([
                'user_id' => $userId,
                'full_name' => $data['address']['full_name'],
                'phone' => $data['address']['phone'],
                'address_line1' => $data['address']['address_line1'],
                'address_line2' => $data['address']['address_line2'] ?? null,
                'city' => $data['address']['city'],
                'state' => $data['address']['state'] ?? null,
                'postal_code' => $data['address']['postal_code'],
                'country' => $data['address']['country'],
            ]);

            $subtotal = 0;
            $totalDiscount = 0;
            $orderItemsData = [];

            foreach ($cartItems as $item) {
                $itemSubtotal = $item->product->price * $item->quantity;
                $perUnitDiscount = (float) ($item->product->discount ?? 0);
                $itemDiscount = round($perUnitDiscount * $item->quantity, 2);

                $subtotal += $itemSubtotal;
                $totalDiscount += $itemDiscount;

                $orderItemsData[] = [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'discount' => $perUnitDiscount,
                ];
            }

            $shipping = $data['shipping'] ?? $setting->delivery_fee;
            $taxPercentage = $setting->vat_enabled ? $setting->vat_percentage : 0;
            $taxableAmount = $subtotal - $totalDiscount;
            $tax = $taxableAmount * ($taxPercentage / 100);
            $total = $taxableAmount + $shipping + $tax;

            $order = Order::create([
                'user_id' => $userId,
                'order_number' => $this->generateOrderNumber(),
                'address_details_id' => $address->id,
                'payment_method_id' => $data['payment_method_id'],
                'delivery_method_id' => $data['delivery_method_id'],
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'discount' => $totalDiscount,
                'tax' => $tax,
                'total' => $total,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'terms_and_condition_agreed' => $data['terms_and_condition_agreed'],
                'privacy_policy_agreed' => $data['privacy_policy_agreed'] ?? false,
                'user_full_name' => $data['address']['full_name'],
                'email' => $user->email,
                'phone_to_number' => $data['address']['phone'],
                'notes' => $data['notes'] ?? null,
            ]);

            $createdOrderItems = [];
            foreach ($orderItemsData as $itemData) {
                $createdOrderItems[] = $order->orderItems()->create($itemData);
            }

            // Reserve codes for ebooks
            foreach ($cartItems as $item) {
                if ($item->product->is_e_copy) {
                    $reserved = $this->ebookAccessCodeService->reserveCodes($item->product, $item->quantity, $order->id);
                    // Map reserved codes to the corresponding order item
                    $orderItem = $order->orderItems()->where('product_id', $item->product_id)->latest('id')->first();
                    if ($orderItem && ! empty($reserved)) {
                        $this->ebookAccessCodeService->attachCodesToOrderItem($order, $orderItem, $reserved);
                    }
                }
            }

            $this->decrementStock($cartItems);

            CartItem::where('user_id', $userId)->delete();

            return $order->load(['orderItems.product.images', 'paymentMethod', 'deliveryMethod', 'addressDetail']);
        });
    }

    private function ensureUserIsActive(): void
    {
        $user = auth()->guard('api-user')->user();

        if (! $user || $user->status === 'blocked') {
            throw new HttpException(403, __('messages.order.account_blocked'));
        }
    }

    private function validateStock($cartItems): void
    {
        foreach ($cartItems as $item) {
            if ($item->product->is_e_copy) {
                $available = $this->ebookAccessCodeService->getAvailableCount($item->product);
                if ($available < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => [__('messages.ebook.insufficient_codes')],
                    ]);
                }
                continue;
            }
            if ($item->product->stock < $item->quantity) {
                throw ValidationException::withMessages([
                    'cart' => [__('messages.order.insufficient_stock', ['product' => $item->product->name_en, 'available' => $item->product->stock, 'requested' => $item->quantity])],
                ]);
            }
        }
    }

    private function decrementStock($cartItems): void
    {
        foreach ($cartItems as $item) {
            if ($item->product->is_e_copy) {
                // Stock already synced based on available codes
                continue;
            }
            $item->product->decrement('stock', $item->quantity);
        }
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('YmdHis').'-'.strtoupper(Str::random(4));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
