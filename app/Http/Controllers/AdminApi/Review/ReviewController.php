<?php

namespace App\Http\Controllers\AdminApi\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Review\UpdateReviewStatusRequest;
use App\Http\Resources\Admin\AdminReviewResource;
use App\Models\Review;
use App\Services\ReviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['product_id', 'user_id', 'status']);
        $perPage = $request->get('per_page', 15);

        $reviews = $this->reviewService->getAll($filters, $perPage);

        return $this->success([
            'reviews' => AdminReviewResource::collection($reviews),
            'pagination' => [
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage(),
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
            ],
        ], 'Reviews retrieved successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $review = $this->reviewService->find($id);

        return $this->success(
            new AdminReviewResource($review),
            'Review retrieved successfully.'
        );
    }

    public function updateStatus(UpdateReviewStatusRequest $request, Review $review): JsonResponse
    {
        $updatedReview = $this->reviewService->updateStatus($review, $request->input('status'));

        return $this->success(
            new AdminReviewResource($updatedReview),
            'Review status updated successfully.'
        );
    }
}
