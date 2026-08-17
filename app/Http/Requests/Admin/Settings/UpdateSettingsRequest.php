<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_fee' => [
                'sometimes',
                'numeric',
                'min:0',
            ],

            'vat_percentage' => [
                'sometimes',
                'numeric',
                'min:0',
                'max:100',
            ],

            'vat_enabled' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
