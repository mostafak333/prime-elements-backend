<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminLandingBannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title_en'       => $this->title_en,
            'title_ar'       => $this->title_ar,
            'description_en' => $this->description_en,
            'description_ar' => $this->description_ar,
            'image'          => $this->image,
            'button_enabled' => $this->button_enabled,
            'button_name_en' => $this->button_name_en,
            'button_name_ar' => $this->button_name_ar,
            'button_link'    => $this->button_link,
            'status'         => $this->status,
            'sort_order'     => $this->sort_order,
            'created_at'     => $this->created_at?->toDateTimeString(),
            'updated_at'     => $this->updated_at?->toDateTimeString(),
        ];
    }
}
