<?php

namespace App\Http\Controllers\UserApi\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ProductResource;
use App\Services\ProductService;
use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Requests\User\Product\ProductFilterRequest;


class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ProductService $productService
    ) {}

    public function index(ProductFilterRequest $request): JsonResponse
    {
        return $this->success([
            'products' => ProductResource::collection(
                $this->productService->getAllForUser($request->validated())
            ),
        ]);
    }
}
