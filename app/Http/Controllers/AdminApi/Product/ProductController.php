<?php

namespace App\Http\Controllers\AdminApi\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);

        $products = $this->productService->getAll($perPage);

        return $this->success([
            'products' => ProductResource::collection($products),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ]
        ], 'Products retrieved successfully.');
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return $this->success(
            new ProductResource($product),
            'Product created successfully.',
            201
        );
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['images', 'detail']);

        return $this->success(
            new ProductResource($product),
            'Product retrieved successfully.'
        );
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->productService->update($product, $request->validated());

        return $this->success(
            new ProductResource($product),
            'Product updated successfully.'
        );
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product);

        return $this->success(
            null,
            'Product deleted successfully.'
        );
    }
}
