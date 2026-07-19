<?php

namespace App\Http\Controllers\UserApi\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Cart\StoreCartRequest;
use App\Http\Requests\User\Cart\UpdateCartRequest;
use App\Http\Resources\User\CartResource;
use App\Models\CartItem;
use App\Services\CartService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CartService $cartService
    ) {}

    public function index(): JsonResponse
    {
        $cartItems = $this->cartService->getUserCart();
        $summary = $this->cartService->getCartSummary($cartItems);

        return $this->success([
            'items'   => CartResource::collection($cartItems),
            'summary' => $summary,
        ], 'Cart retrieved successfully.');
    }

    public function store(StoreCartRequest $request): JsonResponse
    {
        $cartItem = $this->cartService->add($request->validated());

        return $this->success(
            new CartResource($cartItem),
            'Product added to cart successfully.',
            201
        );
    }

    public function update(UpdateCartRequest $request, CartItem $cartItem): JsonResponse
    {
        $updated = $this->cartService->updateQuantity($cartItem, $request->input('quantity'));

        if ($updated === null) {
            return $this->success(null, 'Cart item removed successfully.');
        }

        return $this->success(
            new CartResource($updated),
            'Cart item updated successfully.'
        );
    }

    public function destroy(CartItem $cartItem): JsonResponse
    {
        $this->cartService->remove($cartItem);

        return $this->success(null, 'Cart item removed successfully.');
    }

    public function clear(): JsonResponse
    {
        $this->cartService->clear();

        return $this->success(null, 'Cart cleared successfully.');
    }
}
