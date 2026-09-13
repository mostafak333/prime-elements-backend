<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\User\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'discount_amount' => $this->discount,
            'discount_percentage' => (float) $this->price > 0
                ? round(((float) $this->discount / (float) $this->price) * 100, 2)
                : 0,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
