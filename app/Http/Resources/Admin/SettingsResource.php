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
            'terms_conditions_en' => $this->terms_conditions_en,
            'terms_conditions_ar' => $this->terms_conditions_ar,
            'privacy_policy_en' => $this->privacy_policy_en,
            'privacy_policy_ar' => $this->privacy_policy_ar,
            'return_exchange_policy_en' => $this->return_exchange_policy_en,
            'return_exchange_policy_ar' => $this->return_exchange_policy_ar,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updatedBy->name ?? null,
        ];
    }
}
