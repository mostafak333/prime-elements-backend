<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id'  => ['nullable', 'integer', 'exists:categories,id'],
            'title_id'   => ['nullable', 'integer', 'exists:titles,id'],
            'image_id'   => ['nullable', 'integer', 'exists:images,id'],
            'name_en'    => ['sometimes', 'required', 'string', 'max:255'],
            'name_ar'    => ['sometimes', 'required', 'string', 'max:255'],
            'status'     => ['sometimes', 'required', 'boolean'],
        ];
    }
}
