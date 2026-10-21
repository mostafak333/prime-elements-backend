<?php

namespace App\Http\Requests\User\Product;

use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Foundation\Http\FormRequest;

#[QueryParameter(
    'search',
    description: 'Searches across product name (EN/AR), short description (EN/AR), author, and title (EN/AR).',
    type: 'string',
    required: false
)]
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
            'page' => 'sometimes|integer|min:1',
            'filter' => 'sometimes|in:new_arrival,best_seller,has_offer',
        ];
    }

    public function messages(): array
    {
        return [
            'categories.*.exists' => __('validation.custom.categories.exists'),
            'formats.*.in' => __('validation.custom.formats.in'),
            'min_price.min' => __('validation.custom.min_price.min'),
            'max_price.min' => __('validation.custom.max_price.min'),
            'max_price.gte' => __('validation.custom.max_price.gte'),
            'availability.in' => __('validation.custom.availability.in'),
        ];
    }
}
