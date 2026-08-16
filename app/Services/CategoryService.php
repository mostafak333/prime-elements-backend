<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class CategoryService
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    public function getAll(array $filters = [], int $perPage = 15)
    {
        return Category::with('children.children.children')
            ->whereNull('parent_id')
            ->filter($filters)
            ->paginate($perPage);
    }

    public function create(array $data): Category
    {
        $adminId = auth()->guard('api-admin')->id() ?? null;
        $data['created_by'] = $adminId;
        $data['updated_by'] = $adminId;

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $this->mediaService->store($data['image'], 'categories');
        }

        return Category::query()->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $adminId = auth()->guard('api-admin')->id() ?? null;
        $data['updated_by'] = $adminId;

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $this->mediaService->replace(
                $data['image'],
                $category->image,
                'categories'
            );
        }

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

        $this->mediaService->delete($category->image);
        $category->delete();
    }

    public function getAllForUser()
    {
        return Category::with('children.children.children')
            ->whereNull('parent_id')
            ->where('status', true)
            ->orderBy('name_en')
            ->get();
    }
}
