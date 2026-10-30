<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'name_en' => 'Cash',
            'name_ar' => 'نقدي',
            'description_en' => 'Cash on delivery',
            'description_ar' => 'الدفع عند الاستلام',
            'status' => true,
        ];
    }
}
