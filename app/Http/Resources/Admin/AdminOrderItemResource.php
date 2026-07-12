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
            'discount' => $this->discount,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
