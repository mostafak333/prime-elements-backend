<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'delivery_fee' => $this->delivery_fee,
            'vat_percentage' => $this->vat_percentage,
            'vat_enabled' => $this->vat_enabled,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updatedBy->name ?? null,
        ];
    }
}
