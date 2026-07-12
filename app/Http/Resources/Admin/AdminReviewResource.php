<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product?->name_en,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name,
            'user_email' => $this->user?->email,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'status' => $this->status ? 'active' : 'hidden',
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
