<?php

namespace App\Http\Controllers\AdminApi\Category;


use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use App\Support\ApiResponse;

class AdminCategoryController extends Controller
{
    public function __construct(
        protected CategoryService $service
    ) {}

    public function index()
    {
        return ApiResponse::success(
            $this->service->list()
        );
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->service->create($request->validated());

        return ApiResponse::created(
            $category,
            __('messages.category_created')
        );
    }

    public function show(Category $category)
    {
        return ApiResponse::success($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $updated = $this->service->update($category, $request->validated());

        return ApiResponse::success(
            $updated,
            __('messages.category_updated')
        );
    }

    public function destroy(Category $category)
    {
        $this->service->delete($category);

        return ApiResponse::success(
            null,
            __('messages.category_deleted')
        );
    }
}
