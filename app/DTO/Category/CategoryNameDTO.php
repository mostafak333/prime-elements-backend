<?php

namespace App\DTO\Category;

use App\Models\Category;



class CategoryNameDTO
{
    public static function fromModel(Category $category): array
    {
        return [
            'name' => $category->name,
        ];
    }

    public static function collection($categories): array
    {
        return $categories
            ->map(fn($category) => self::fromModel($category))
            ->toArray();
    }
}
