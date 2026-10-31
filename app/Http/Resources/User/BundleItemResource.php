<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'product' => $this->whenLoaded(
                'product',
                fn () => $this->product ? new ProductResource($this->product) : null
            ),
        ];
    }
}
