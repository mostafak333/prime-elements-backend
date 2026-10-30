<?php

namespace App\Http\Controllers\UserApi\Bundle;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\BundleResource;
use App\Http\Resources\User\CartResource;
use App\Services\BundleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class BundleController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BundleService $bundleService
    ) {}

    public function show(string $token): JsonResponse
    {
        $bundle = $this->bundleService->findByToken($token);

        if (! $bundle || ! $bundle->status) {
            return $this->error('messages.bundle.not_found', 404);
        }

        return $this->success(
            new BundleResource($bundle),
            'messages.bundle.retrieved'
        );
    }

    public function addToCart(string $token): JsonResponse
    {
        $result = $this->bundleService->addToCart($token);

        return $this->success([
            'bundle' => new BundleResource($result['bundle']),
            'items' => CartResource::collection($result['items']),
            'summary' => $result['summary'],
            'redirect_url' => rtrim((string) config('app.frontend_url'), '/').'/cart',
        ], 'messages.bundle.added_to_cart');
    }
}
