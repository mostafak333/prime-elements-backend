<?php

namespace App\Http\Controllers\UserApi\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Review\StoreReviewRequest;
use App\Http\Resources\User\ReviewResource;
use App\Http\Resources\User\UserReviewResource;
use App\Services\UserReviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserReviewController extends Controller
{
    use ApiResponse;

    public function __construct(
        private UserReviewService $userReviewService
    ) {}

    public function store(StoreReviewRequest $request): JsonResponse
    {
        try {
            $review = $this->userReviewService->create($request->validated());

            return $this->success(
                new UserReviewResource($review),
                'Review submitted successfully.',
                201
            );
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['product_id', 'user_id', 'status']);
        $perPage = (int) $request->get('per_page', 15);

        $reviews = $this->userReviewService->getAllForUsers($filters, $perPage);

        return $this->success([
            'reviews' => ReviewResource::collection($reviews),
            'pagination' => [
                'total' => $reviews->total(),
                'per_page' => $reviews->perPage(),
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
            ],
        ], 'Reviews retrieved successfully.');
    }
}
