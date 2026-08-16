<?php

namespace App\Http\Requests\User\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address.full_name' => ['required', 'string', 'max:255'],
            'address.phone' => ['required', 'string', 'max:50'],
            'address.address_line1' => ['required', 'string', 'max:255'],
            'address.address_line2' => ['nullable', 'string', 'max:255'],
            'address.city' => ['required', 'string', 'max:255'],
            'address.state' => ['nullable', 'string', 'max:255'],
            'address.postal_code' => ['required', 'string', 'max:20'],
            'address.country' => ['required', 'string', 'max:255'],

            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'delivery_method_id' => ['required', 'integer', 'exists:delivery_methods,id'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms_and_condition_agreed' => ['required', 'boolean', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'terms_and_condition_agreed.accepted' => 'You must agree to the terms and conditions.',
            'address.full_name.required' => 'Full name is required.',
            'address.phone.required' => 'Phone number is required.',
        ];
    }
}
