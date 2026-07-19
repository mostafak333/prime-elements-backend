<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['pending', 'confirmed', 'out_for_delivery', 'delivered', 'cancelled'])],
        ];
    }
}
