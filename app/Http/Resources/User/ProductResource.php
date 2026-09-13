<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'short_description_en' => $this->short_description_en,
            'short_description_ar' => $this->short_description_ar,
            'title_en' => $this->title_en,
            'title_ar' => $this->title_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'price' => $this->price,
            'price_after_discount' => number_format((float) $this->price - (float) $this->discount, 2, '.', ''),
            'discount_percentage' => number_format((float) ($this->has_offer ? ($this->discount_percentage ?? 0) : 0), 2, '.', ''),
            'discount_amount' => $this->discount,
            'has_offer' => $this->has_offer,
            'discount_start_at' => $this->discount_start_at?->toDateTimeString(),
            'discount_end_at' => $this->discount_end_at?->toDateTimeString(),
            'stock' => $this->stock,
            'is_new_arrival' => $this->is_new_arrival,
            'is_best_seller' => $this->is_best_seller,
            'is_e_copy' => $this->is_e_copy,
            'publisher' => $this->publisher,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'detail' => new ProductDetailResource($this->whenLoaded('detail')),
        ];
    }
}
