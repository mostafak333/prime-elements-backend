<?php

namespace App\Services;

use App\Models\Title;

class TitleService
{
    public function getAll()
    {
        return Title::latest()->paginate(15);
    }

    public function getAllForUser()
    {
        return Title::orderBy('name_en')->get();
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
