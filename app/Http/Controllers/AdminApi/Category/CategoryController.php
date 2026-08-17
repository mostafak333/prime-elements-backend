<?php

namespace App\Http\Controllers\AdminApi\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\FilterCategoryRequest;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    use ApiResponse;

    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    // In your CategoryController.php
    public function index(FilterCategoryRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = $request->get('per_page', 15);

        $categories = $this->categoryService->getAll($filters, $perPage);

        $responseData = [
            'categories' => CategoryResource::collection($categories),
            'pagination' => [
                'total' => $categories->total(),
                'per_page' => $categories->perPage(),
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
            ],
        ];

        return $this->success($responseData, 'Categories retrieved successfully.', 200);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return $this->success(
            new CategoryResource($category),
            'Category created successfully.',
            201
        );
    }

    public function show(Category $category): JsonResponse
    {
        return $this->success(
            new CategoryResource($category),
            'Category retrieved successfully.',
            200
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $updatedCategory = $this->categoryService->update($category, $request->validated());

        return $this->success(
            new CategoryResource($updatedCategory),
            'Category updated successfully.',
            200
        );
    }

    public function destroy(Category $category): JsonResponse
    {
        try {

            $this->categoryService->delete($category);

            return $this->success(
                null,
                'Category deleted successfully.',
                200
            );
        } catch (ValidationException $e) {
            return $this->error(
                'Cannot delete a category that has child categories',
                422
            );
        }
    }
}
