<?php

namespace App\Services;

use App\Models\Wishlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WishlistService
{
    public function getUserWishlist(int $perPage = 15): LengthAwarePaginator
    {
        $userId = auth()->guard('api-user')->id();

        return Wishlist::with('product.images', 'product.detail')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function add(array $data): Wishlist
    {
        $userId = auth()->guard('api-user')->id();

        return Wishlist::create([
            'user_id' => $userId,
            'product_id' => $data['product_id'],
        ]);
    }

    public function delete(Wishlist $wishlist): void
    {
        $this->ensureOwnership($wishlist);
        $wishlist->forceDelete();
    }

    private function ensureOwnership(Wishlist $wishlist): void
    {
        $userId = auth()->guard('api-user')->id();

        if ($wishlist->user_id !== $userId) {
            abort(403, 'This wishlist item does not belong to you.');
        }
    }
}
