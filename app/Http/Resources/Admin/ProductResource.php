<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Admin\ProductDetailResource;
use App\Http\Resources\Admin\ProductImageResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'category_id'       => $this->category_id,
            'name_en'           => $this->name_en,
            'name_ar'           => $this->name_ar,
            'short_description' => $this->short_description,
            'price'             => $this->price,
            'discount'          => $this->discount,
            'stock'             => $this->stock,
            'status'            => $this->status,
            'is_new_arrival'    => $this->is_new_arrival,
            'is_best_seller'    => $this->is_best_seller,
            'is_e_copy'         => $this->is_e_copy,
            'publisher'         => $this->publisher,
            'created_by'        => $this->createdBy->name ?? null,
            'updated_by'        => $this->updatedBy->name ?? null,
            'created_at'        => $this->created_at?->toDateTimeString(),
            'updated_at'        => $this->updated_at?->toDateTimeString(),
            'deleted_at'        => $this->deleted_at?->toDateTimeString(),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'detail' => new ProductDetailResource($this->whenLoaded('detail')),
        ];
    }
}
