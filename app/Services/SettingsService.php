<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService
{
    public function getSettings(): ?Setting
    {
        return Setting::first() ?? new Setting();
    }

    public function updateSettings(array $data): Setting
    {
        $settings = Setting::firstOrCreate([]);
        $adminId = auth()->guard('api-admin')->id() ?? null;
        $data['updated_by'] = $adminId;
        $settings->update($data);

        return $settings;
    }
}
