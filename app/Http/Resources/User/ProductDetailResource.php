<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'description'      => $this->description,
            'book_title'       => $this->book_title,
            'author'           => $this->author,
            'publisher'        => $this->publisher,
            'language'         => $this->language,
            'pages'            => $this->pages,
            'isbn'             => $this->isbn,
            'publication_date' => $this->publication_date?->format('Y-m-d'),
        ];
    }
}
