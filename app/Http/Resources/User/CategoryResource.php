<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'parent_id' => $this->parent_id,
            'title_id'  => $this->title_id,
            'image_id'  => $this->image_id,
            'name_en'   => $this->name_en,
            'name_ar'   => $this->name_ar,
        ];
    }
}