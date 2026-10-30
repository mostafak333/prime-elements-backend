<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'token' => $this->token,
            'share_url' => $this->share_url,
            'status' => $this->status,
            'items' => BundleItemResource::collection($this->whenLoaded('items')),
            'total_items' => $this->relationLoaded('items')
                ? $this->items->sum('quantity')
                : 0,
        ];
    }
}
