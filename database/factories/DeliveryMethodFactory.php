<?php

namespace Database\Factories;

use App\Models\DeliveryMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryMethodFactory extends Factory
{
    protected $model = DeliveryMethod::class;

    public function definition(): array
    {
        return [
            'name_en' => 'Standard',
            'name_ar' => 'عادي',
            'description_en' => 'Standard delivery',
            'description_ar' => 'توصيل عادي',
            'status' => true,
            'price' => 0,
        ];
    }
}
