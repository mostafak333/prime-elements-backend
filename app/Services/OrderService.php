<?php

namespace App\Services;

use App\Models\AddressDetail;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function getUserOrders(int $perPage = 15)
    {
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

    public function createOrder(array $data): Order
    {
        $userId = auth()->guard('api-user')->id();
        $user = auth()->guard('api-user')->user();

        $cartItems = CartItem::with('product')
            ->where('user_id', $userId)
            ->get();

        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => ['Your cart is empty. Add items before placing an order.'],
            ]);
        }

        return DB::transaction(function () use ($data, $userId, $user, $cartItems) {
            $this->validateStock($cartItems);

            $address = AddressDetail::create([
                'user_id'       => $userId,
                'full_name'     => $data['address']['full_name'],
                'phone'         => $data['address']['phone'],
                'address_line1' => $data['address']['address_line1'],
                'address_line2' => $data['address']['address_line2'] ?? null,
                'city'          => $data['address']['city'],
                'state'         => $data['address']['state'] ?? null,
                'postal_code'   => $data['address']['postal_code'],
                'country'       => $data['address']['country'],
            ]);

            $subtotal = 0;
            $totalDiscount = 0;
            $orderItemsData = [];

            foreach ($cartItems as $item) {
                $itemSubtotal = $item->product->price * $item->quantity;
                $itemDiscount = ($item->product->discount ?? 0) * $item->quantity;

                $subtotal += $itemSubtotal;
                $totalDiscount += $itemDiscount;

                $orderItemsData[] = [
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->product->price,
                    'discount'   => $item->product->discount ?? 0,
                ];
            }

            $shipping = $data['shipping'] ?? 0;
            $tax = $data['tax'] ?? 0;
            $total = $subtotal - $totalDiscount + $shipping + $tax;

            $order = Order::create([
                'user_id'                      => $userId,
                'order_number'                 => $this->generateOrderNumber(),
                'address_details_id'           => $address->id,
                'payment_method_id'            => $data['payment_method_id'],
                'delivery_method_id'           => $data['delivery_method_id'],
                'subtotal'                     => $subtotal,
                'shipping'                     => $shipping,
                'discount'                     => $totalDiscount,
                'tax'                          => $tax,
                'total'                        => $total,
                'status'                       => 'pending',
                'payment_status'               => 'unpaid',
                'terms_and_condition_agreed'   => $data['terms_and_condition_agreed'],
                'user_full_name'               => $data['address']['full_name'],
                'email'                        => $user->email,
                'phone_to_number'              => $data['address']['phone'],
                'notes'                        => $data['notes'] ?? null,
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->orderItems()->create($itemData);
            }

            $this->decrementStock($cartItems);

            CartItem::where('user_id', $userId)->delete();

            return $order->load(['orderItems.product.images', 'paymentMethod', 'deliveryMethod', 'addressDetail']);
        });
    }

    private function validateStock($cartItems): void
    {
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                throw ValidationException::withMessages([
                    'cart' => ["Insufficient stock for \"{$item->product->name_en}\". Available: {$item->product->stock}, requested: {$item->quantity}."],
                ]);
            }
        }
    }

    private function decrementStock($cartItems): void
    {
        foreach ($cartItems as $item) {
            $item->product->decrement('stock', $item->quantity);
        }
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
