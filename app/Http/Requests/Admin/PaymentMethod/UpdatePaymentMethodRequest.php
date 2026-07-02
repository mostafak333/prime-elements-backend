<?php

namespace App\Http\Requests\Admin\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $paymentMethod = $this->route('paymentMethod');

        return [
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_methods', 'name_en')
                    ->ignore($paymentMethod->id),
            ],

            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_methods', 'name_ar')
                    ->ignore($paymentMethod->id),
            ],
        ];
    }
}