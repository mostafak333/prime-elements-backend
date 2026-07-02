<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'       => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'name_en'           => ['sometimes', 'required', 'string', 'max:255'],
            'name_ar'           => ['sometimes', 'required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'price'             => ['sometimes', 'required', 'numeric', 'min:0'],
            'discount'          => ['nullable', 'numeric', 'min:0'],
            'stock'             => ['sometimes', 'required', 'integer', 'min:0'],
            'status'            => ['sometimes', 'required', 'boolean'],
            'is_new_arrival'    => ['sometimes', 'required', 'boolean'],
            'is_best_seller'    => ['sometimes', 'required', 'boolean'],
            'is_e_copy'         => ['sometimes', 'required', 'boolean'],
            'publisher'         => ['nullable', 'string', 'max:255'],

            // images
            'images'   => ['nullable', 'array'],
            'images.*' => ['string'],

            // detail (replace whole object)
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
