<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
