<?php

namespace App\Http\Requests\Admin\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_en' => 'required|string|max:255|unique:delivery_methods,name_en',
            'name_ar' => 'required|string|max:255|unique:delivery_methods,name_ar',
        ];
    }
}
