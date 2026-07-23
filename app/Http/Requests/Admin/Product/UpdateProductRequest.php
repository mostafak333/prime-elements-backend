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
            'short_description_en' => ['nullable', 'string'],
            'short_description_ar' => ['nullable', 'string'],
            'price'             => ['sometimes', 'required', 'numeric', 'min:0'],
            'discount'          => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'stock'             => ['sometimes', 'required', 'integer', 'min:0'],
            'status'            => ['sometimes', 'required', 'boolean'],
            'is_new_arrival'    => ['sometimes', 'required', 'boolean'],
            'is_best_seller'    => ['sometimes', 'required', 'boolean'],
            'is_e_copy'         => ['sometimes', 'required', 'boolean'],
            'publisher'         => ['sometimes', 'required', 'string', 'max:255'],

            // Images
            'images'   => ['nullable', 'array'],
            'images.*' => ['string'],

            // Product Detail
            'detail' => ['nullable', 'array'],

            'detail.description_en'    => ['nullable', 'string'],
            'detail.description_ar'    => ['nullable', 'string'],
            'detail.title_en'       => ['nullable', 'string', 'max:255'],
            'detail.title_ar'           => ['nullable', 'string', 'max:255'],
            'detail.author'           => ['nullable', 'string', 'max:255'],
            'detail.publisher'        => ['nullable', 'string', 'max:255'],
            'detail.language'         => ['nullable', 'string', 'max:50'],
            'detail.pages'            => ['nullable', 'integer', 'min:1'],
            'detail.isbn'             => ['nullable', 'string', 'max:50'],
            'detail.publication_date' => ['nullable', 'date'],
        ];
    }
}
