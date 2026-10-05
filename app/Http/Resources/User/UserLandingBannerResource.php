<?php

namespace App\Http\Resources\User;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLandingBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title_en' => $this->title_en,
            'title_ar' => $this->title_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'image_url' => app(MediaService::class)->getUrl($this->image),
            'button' => [
                'enabled' => $this->button_enabled,
                'name_en' => $this->button_name_en,
                'name_ar' => $this->button_name_ar,
                'link' => $this->button_link,
            ],
        ];
    }
}
