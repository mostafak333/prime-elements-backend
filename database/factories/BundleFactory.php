<?php

namespace Database\Factories;

use App\Models\Bundle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BundleFactory extends Factory
{
    protected $model = Bundle::class;

    public function definition(): array
    {
        return [
            'name_en' => $this->faker->words(3, true),
            'name_ar' => $this->faker->words(3, true),
            'description_en' => $this->faker->sentence(),
            'description_ar' => $this->faker->sentence(),
            'token' => Str::random(64),
            'status' => true,
        ];
    }
}
