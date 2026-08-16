<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::firstOrCreate(
            [],
            [
                'delivery_fee' => 50,
                'vat_percentage' => 14,
                'vat_enabled' => true,
                'updated_by' => 1,
            ]
        );
    }
}
