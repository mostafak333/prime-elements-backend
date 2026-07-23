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
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'title_en' => $this->title_en,
            'title_ar' => $this->title_ar,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'language' => $this->language,
            'pages' => $this->pages,
            'isbn' => $this->isbn,
            'publication_date' => $this->publication_date,
        ];
    }
}
