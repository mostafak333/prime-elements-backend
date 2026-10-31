<?php

namespace App\Http\Requests\Admin\Bundle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBundleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_en' => ['sometimes', 'required', 'string', 'max:255'],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'description_en' => ['sometimes', 'nullable', 'string'],
            'description_ar' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'boolean'],
            'items' => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
