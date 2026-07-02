<?php

namespace App\Http\Requests\Admin\DeliveryMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeliveryMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $deliveryMethod = $this->route('deliveryMethod');

        return [
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('delivery_methods', 'name_en')
                    ->ignore($deliveryMethod->id),
            ],
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('delivery_methods', 'name_ar')
                    ->ignore($deliveryMethod->id),
            ],
        ];
    }
}