<?php

namespace Database\Seeders;

use App\Models\Title;
use Illuminate\Database\Seeder;

class TitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $titles = [
            [
                'name_en' => 'School Books',
                'name_ar' => 'الكتب المدرسية',
            ],
            [
                'name_en' => 'University Books',
                'name_ar' => 'الكتب الجامعية',
            ],
            [
                'name_en' => 'Academic Books',
                'name_ar' => 'الكتب الأكاديمية',
            ],
            [
                'name_en' => 'Fiction Books',
                'name_ar' => 'الكتب الخيالية',
            ],
        ];

        foreach ($titles as $title) {
            Title::create([
                'name_en' => $title['name_en'],
                'name_ar' => $title['name_ar'],
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }
    }
}
