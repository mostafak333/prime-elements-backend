<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name_en' => $this->faker->word(),
            'name_ar' => $this->faker->word(),
            'short_description_en' => $this->faker->sentence(),
            'short_description_ar' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'discount_percentage' => 0,
            'discount_start_at' => null,
            'discount_end_at' => null,
            'stock' => $this->faker->numberBetween(0, 50),
            'status' => true,
            'is_new_arrival' => false,
            'is_best_seller' => false,
            'is_e_copy' => false,
            'ebook_url' => null,
            'publisher' => $this->faker->company(),
        ];
    }
}
