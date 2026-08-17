<?php

namespace App\Http\Resources\Admin;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'avatar_url' => app(MediaService::class)->getUrl($this->avatar),
            'is_super' => $this->is_super,
            'is_active' => $this->is_active,
            'roles' => $this->getRoleNames(),
        ];
    }
}
