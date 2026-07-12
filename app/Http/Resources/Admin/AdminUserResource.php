<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at?->toDateTimeString(),
            'roles' => $this->getRoleNames(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
