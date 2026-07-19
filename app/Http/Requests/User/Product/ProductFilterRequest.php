<?php

namespace App\Http\Requests\User\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categories' => 'sometimes|array',
            'categories.*' => 'integer|exists:categories,id',
            'formats' => 'sometimes|array',
            'formats.*' => 'in:printed,ebook,both',
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|min:0|gte:min_price',
            'availability' => 'sometimes|in:in_stock,out_of_stock',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_by' => 'sometimes|in:price_asc,price_desc,name_asc,name_desc,newest,best_seller',
            'search' => 'sometimes|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'categories.*.exists' => 'One or more selected categories are invalid.',
            'formats.*.in' => 'Invalid format selected. Must be printed, ebook, or both.',
            'min_price.min' => 'Minimum price must be at least 0.',
            'max_price.min' => 'Maximum price must be at least 0.',
            'max_price.gte' => 'Maximum price must be greater than or equal to minimum price.',
            'availability.in' => 'Invalid availability option.',
        ];
    }
}
