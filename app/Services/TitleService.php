<?php

namespace App\Services;

use App\Models\Title;

class TitleService
{
    public function getAll()
    {
        return Title::latest()->paginate(15);
    }

    public function create(array $data): Title
    {
        return Title::create($data);
    }

    public function update(Title $title, array $data): Title
    {
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
        $title->delete();
    }
}