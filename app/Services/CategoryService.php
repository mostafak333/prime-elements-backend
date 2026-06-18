<?php

namespace App\Services;

use App\Models\Category;
use App\DTO\Category\CategoryNameDTO;

class CategoryService
{
    public function listActiveNames(): array
    {
        $categories = Category::active()->get();

        return CategoryNameDTO::collection($categories);
    }

    public function listAll(): array
    {
        return CategoryNameDTO::collection(
            Category::latest()->get()
        );
    }

    public function find(int $id): ?array
    {
        $category = Category::find($id);

        if (! $category) {
            return null;
        }

        return CategoryNameDTO::fromModel($category);
    }
}
