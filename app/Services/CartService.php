<?php

namespace App\Services;

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getUserCart(): Collection
    {
        $userId = auth()->guard('api-user')->id();

        return CartItem::with('product.images', 'product.detail')
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function add(array $data): CartItem
    {
        $userId = auth()->guard('api-user')->id();

        $existing = CartItem::where('user_id', $userId)
            ->where('product_id', $data['product_id'])
            ->first();

        if ($existing) {
            $existing->increment('quantity', $data['quantity']);
            return $existing->fresh()->load('product.images', 'product.detail');
        }

        return CartItem::create([
            'user_id'    => $userId,
            'product_id' => $data['product_id'],
            'quantity'   => $data['quantity'],
        ])->load('product.images', 'product.detail');
    }

    public function updateQuantity(CartItem $cartItem, int $quantity): ?CartItem
    {
        $this->ensureOwnership($cartItem);

        if ($quantity < 1) {
            $cartItem->delete();
            return null;
        }

        $cartItem->update(['quantity' => $quantity]);
        return $cartItem->fresh()->load('product.images', 'product.detail');
    }

    public function remove(CartItem $cartItem): void
    {
        $this->ensureOwnership($cartItem);
        $cartItem->delete();
    }

    public function clear(): void
    {
        $userId = auth()->guard('api-user')->id();

        CartItem::where('user_id', $userId)->delete();
    }

    public function getCartSummary(Collection $cartItems): array
    {
        $subtotal = 0;
        $totalItems = 0;

        foreach ($cartItems as $item) {
            $price = $item->product?->price ?? 0;
            $discount = $item->product?->discount ?? 0;
            $effectivePrice = max(0, $price - $discount);
            $subtotal += $effectivePrice * $item->quantity;
            $totalItems += $item->quantity;
        }

        return [
            'total_items' => $totalItems,
            'subtotal'    => round($subtotal, 2),
        ];
    }

    private function ensureOwnership(CartItem $cartItem): void
    {
        $userId = auth()->guard('api-user')->id();

        if ($cartItem->user_id !== $userId) {
            abort(403, 'This cart item does not belong to you.');
        }
    }
}
