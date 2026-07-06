<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'image_id'   => $this->image_id,
            'name_en'    => $this->name_en,
            'name_ar'    => $this->name_ar,
            'is_filter'  => $this->is_filter,
            'children'   => SimpleCategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
