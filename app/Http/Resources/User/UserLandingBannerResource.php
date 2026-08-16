<?php

namespace App\Http\Resources\User;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLandingBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        $title = $locale === 'ar' ? $this->title_ar : $this->title_en;
        $description = $locale === 'ar' ? $this->description_ar : $this->description_en;
        $buttonName = $locale === 'ar' ? $this->button_name_ar : $this->button_name_en;

        return [
            'id' => $this->id,
            'title' => $title,
            'description' => $description,
            'image_url' => app(MediaService::class)->getUrl($this->image),
            'button' => [
                'enabled' => $this->button_enabled,
                'name' => $buttonName,
                'link' => $this->button_link,
            ],
        ];
    }
}
