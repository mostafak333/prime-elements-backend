<?php

namespace App\Services;

use App\Models\Review;

class ReviewService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Review::with(['user', 'product']);

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['status'])) {
            $boolStatus = $filters['status'] === 'active';
            $query->where('status', $boolStatus);
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(int $id): Review
    {
        return Review::with(['user', 'product'])->findOrFail($id);
    }

    public function updateStatus(Review $review, string $status): Review
    {
        $review->update(['status' => $status === 'active']);

        return $review->fresh()->load(['user', 'product']);
    }
}
