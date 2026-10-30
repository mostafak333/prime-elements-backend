<?php

namespace Database\Factories;

use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class BundleItemFactory extends Factory
{
    protected $model = BundleItem::class;

    public function definition(): array
    {
        return [
            'bundle_id' => Bundle::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 5),
        ];
    }
}
