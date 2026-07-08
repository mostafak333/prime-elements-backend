<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

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
