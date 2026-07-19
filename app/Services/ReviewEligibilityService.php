<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;

class ReviewEligibilityService
{
    public function canReview(User $user, int $productId): bool
    {
        return Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->whereHas('orderItems', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->exists();
    }
}
