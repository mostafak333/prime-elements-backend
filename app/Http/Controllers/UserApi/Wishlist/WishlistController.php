<?php

namespace App\Http\Controllers\UserApi\Wishlist;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Wishlist\StoreWishlistRequest;
use App\Http\Resources\User\WishlistResource;
use App\Models\Wishlist;
use App\Services\WishlistService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    use ApiResponse;

    public function __construct(
        private WishlistService $wishlistService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);

        $wishlistItems = $this->wishlistService->getUserWishlist($perPage);

        return $this->success([
            'wishlist' => WishlistResource::collection($wishlistItems),
            'pagination' => [
                'current_page' => $wishlistItems->currentPage(),
                'last_page'    => $wishlistItems->lastPage(),
                'per_page'     => $wishlistItems->perPage(),
                'total'        => $wishlistItems->total(),
            ],
        ], 'Wishlist retrieved successfully.');
    }

    public function store(StoreWishlistRequest $request): JsonResponse
    {
        $wishlistItem = $this->wishlistService->add($request->validated());

        return $this->success(
            new WishlistResource($wishlistItem->load('product.images', 'product.detail')),
            'Product added to wishlist successfully.',
            201
        );
    }

    public function destroy(Wishlist $wishlist): JsonResponse
    {
        $this->wishlistService->delete($wishlist);

        return $this->success(
            null,
            'Product removed from wishlist successfully.'
        );
    }
}
