<?php

namespace App\Services;

use App\Models\Review;
use App\Services\ReviewEligibilityService;
use Illuminate\Validation\ValidationException;

class UserReviewService
{
    public function __construct(
        private ReviewEligibilityService $eligibilityService
    ) {}

    public function create(array $data): Review
    {
        $user = auth()->guard('api-user')->user();

        if (!$this->eligibilityService->canReview($user, $data['product_id'])) {
            throw ValidationException::withMessages([
                'product_id' => ['You can review this product after completing your order.'],
            ]);
        }

        return Review::create([
            'product_id' => $data['product_id'],
            'user_id' => $user->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'status' => true,
        ]);
    }
    public function getAllForUsers(array $filters = [], int $perPage = 15)
    {
        $query = Review::with(['user', 'product']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['status'])) {
            $boolStatus = $filters['status'] === 'active';
            $query->where('status', $boolStatus);
        }

        return $query->latest()->paginate($perPage);
    }
}
