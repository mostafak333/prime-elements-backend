<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $category = $this->route('category');
        $this->merge([
            'category_id' => $category?->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'parent_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
                'different:category_id', // 👈 Built-in Laravel validation
            ],
            'title_id' => ['required', 'integer', 'exists:titles,id'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'name_en' => ['sometimes', 'required', 'string', 'max:255'],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($this->category_id),
            ],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'boolean'],
            'is_filter' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.different' => __('validation.custom.parent_id.different'),
            'slug.unique' => __('validation.custom.slug.unique'),
            'parent_id.exists' => __('validation.custom.parent_id.exists'),
            'title_id.required' => __('validation.custom.title_id.required'),
            'title_id.exists' => __('validation.custom.title_id.exists'),
        ];
    }
}
