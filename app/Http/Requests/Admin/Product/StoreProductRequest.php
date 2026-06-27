<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // --------------------
            // Product Core
            // --------------------
            'category_id'       => ['required', 'integer', 'exists:categories,id'],
            'name_en'           => ['required', 'string', 'max:255'],
            'name_ar'           => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'price'             => ['required', 'numeric', 'min:0'],
            'discount'          => ['nullable', 'numeric', 'min:0'],
            'stock'             => ['required', 'integer', 'min:0'],
            'status'            => ['required', 'boolean'],
            'is_new_arrival'    => ['required', 'boolean'],
            'is_best_seller'    => ['required', 'boolean'],
            'is_e_copy'         => ['required', 'boolean'],
            'publisher'         => ['nullable', 'string', 'max:255'],

            // --------------------
            // Images
            // --------------------
            'images'   => ['nullable', 'array'],
            'images.*' => ['string'],

            // --------------------
            // Product Detail (ONE record)
            // --------------------
            'detail' => ['nullable', 'array'],

            'detail.name_en'          => ['required_with:detail', 'string', 'max:255'],
            'detail.name_ar'          => ['required_with:detail', 'string', 'max:255'],
            'detail.description'      => ['nullable', 'string'],
            'detail.author'           => ['nullable', 'string', 'max:255'],
            'detail.publisher'        => ['nullable', 'string', 'max:255'],
            'detail.language'         => ['nullable', 'string', 'max:50'],
            'detail.pages'            => ['nullable', 'integer', 'min:1'],
            'detail.isbn'             => ['nullable', 'string', 'max:50'],
            'detail.format'           => ['nullable', 'string', 'max:50'],
            'detail.publication_date' => ['nullable', 'date'],
            'detail.is_active'        => ['nullable', 'boolean'],

        ];
    }
}
