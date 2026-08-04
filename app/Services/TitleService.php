<?php

namespace App\Services;

use App\Models\Title;

class TitleService
{
    // In TitleService.php
    public function getNavigationTree()
    {

        return Title::with(['categories' => function ($query) {
            $query->whereNull('parent_id')->with('children.children.children');
        }])->get();
    }

    public function getAllForUser(array $filters)
    {
        $query = $this->getNavigationTree();

        if (isset($filters['id'])) {
            $query = $query->where('id', $filters['id']);
        }

        return $query;
    }

    public function create(array $data): Title
    {
        $adminId = auth()->guard('api-admin')->id();
        $data['created_by'] = $adminId;
        $data['updated_by'] = $adminId;
        return Title::create($data);
    }

    public function update(Title $title, array $data): Title
    {
        $adminId = auth()->guard('api-admin')->id();
        $data['updated_by'] = $adminId;
        $title->update($data);

        return $title->refresh();
    }

    public function find(int $id): ?array
    {
        $title = Title::find($id);

        if (! $title) {
            return null;
        }

        return $title;
    }

    public function delete(Title $title): void
    {
        if ($title->categories()->exists()) {
            throw new \Exception("Cannot delete title because it is linked to categories.");
        }

        $title->delete();
    }
}
