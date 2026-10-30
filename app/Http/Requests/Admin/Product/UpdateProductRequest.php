<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'name_en' => ['sometimes', 'required', 'string', 'max:255'],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'short_description_en' => ['nullable', 'string'],
            'short_description_ar' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'discount_percentage' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'discount_start_at' => ['sometimes', 'nullable', 'date'],
            'discount_end_at' => ['sometimes', 'nullable', 'date'],
            'stock' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'boolean'],
            'is_new_arrival' => ['sometimes', 'required', 'boolean'],
            'is_best_seller' => ['sometimes', 'required', 'boolean'],
            'is_e_copy' => ['sometimes', 'required', 'boolean'],
            'ebook_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'publisher' => ['sometimes', 'required', 'string', 'max:255'],

            // Images
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            // Product Detail
            'detail' => ['nullable', 'array'],

            'detail.description_en' => ['nullable', 'string'],
            'detail.description_ar' => ['nullable', 'string'],
            'detail.title_en' => ['nullable', 'string', 'max:255'],
            'detail.title_ar' => ['nullable', 'string', 'max:255'],
            'detail.author' => ['nullable', 'string', 'max:255'],
            'detail.publisher' => ['nullable', 'string', 'max:255'],
            'detail.language' => ['nullable', 'string', 'max:50'],
            'detail.pages' => ['nullable', 'integer', 'min:1'],
            'detail.isbn' => ['nullable', 'string', 'max:50'],
            'detail.publication_date' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $product = $this->route('product');
            $isEbook = $this->has('is_e_copy') ? $this->boolean('is_e_copy') : (bool) optional($product)->is_e_copy;
            if ($isEbook) {
                $ebookUrl = $this->has('ebook_url') ? $this->input('ebook_url') : optional($product)->ebook_url;
                if (empty($ebookUrl)) {
                    $validator->errors()->add('ebook_url', __('validation.custom.ebook_url.required'));
                }
                if ($this->has('stock') && $this->input('stock') !== null && (int)$this->input('stock') != 0) {
                    $validator->errors()->add('stock', __('messages.ebook.cannot_set_stock_for_ebook'));
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'ebook_url.required' => __('validation.custom.ebook_url.required'),
            'ebook_url.url' => __('validation.custom.ebook_url.url'),
        ];
    }
}
