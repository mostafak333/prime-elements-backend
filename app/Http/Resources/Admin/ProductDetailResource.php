<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'book_title' => $this->book_title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'language' => $this->language,
            'pages' => $this->pages,
            'isbn' => $this->isbn,
            'format' => $this->format,
            'publication_date' => $this->publication_date,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'display_name' => $this->display_name,
        ];
    }
}
