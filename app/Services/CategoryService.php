<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()->paginate($perPage);
    }

    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category->refresh();
    }

    public function find(int $id): ?Category
    {
        return Category::query()->find($id);
    }

    public function delete(Category $category): void
    {
        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'category' => ['Cannot delete a category that has child categories.'],
            ]);
        }

        $category->delete();
    }
}
