<?php

namespace App\Http\Controllers\UserApi\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CategoryService $categoryService
    ) {}

    public function index(): JsonResponse
    {
        return $this->success([
            'categories' => CategoryResource::collection(
                $this->categoryService->getAllForUser()
            ),
        ]);
    }

    public function subCategories(Category $category): JsonResponse
    {
        return $this->success([
            'sub_categories' => CategoryResource::collection(
                $this->categoryService->getSubCategories($category)
            ),
        ]);
    }
}