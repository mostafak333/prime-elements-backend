<?php

namespace Database\Factories;

use App\Models\Title;
use Illuminate\Database\Eloquent\Factories\Factory;

class TitleFactory extends Factory
{
    protected $model = Title::class;

    public function definition(): array
    {
        return [
            'name_en' => $this->faker->unique()->word(),
            'name_ar' => $this->faker->unique()->word(),
        ];
    }
}
