<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'title_id' => \App\Models\Title::factory(),
            'name_en' => $this->faker->word(),
            'name_ar' => $this->faker->word(),
            'description_en' => $this->faker->sentence(),
            'description_ar' => $this->faker->sentence(),
            'image' => null,
            'parent_id' => null,
        ];
    }
}
