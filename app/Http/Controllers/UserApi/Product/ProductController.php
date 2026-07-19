<?php

namespace App\Http\Controllers\UserApi\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\User\Product\ProductFilterRequest;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ProductService $productService
    ) {}

    public function index(ProductFilterRequest $request): JsonResponse
    {
        $products = $this->productService->getAllForUser($request->validated());

        return $this->success([
            'products' => ProductResource::collection($products),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['images', 'detail']);

        return $this->success(
            new ProductResource($product)
        );
    }

    public function filterOptions(): JsonResponse
    {
        return $this->success(
            $this->productService->getFilterOptions()
        );
    }
}
