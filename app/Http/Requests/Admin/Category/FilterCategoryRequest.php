<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;

class FilterCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id'  => ['integer', 'exists:categories,id'],
            'title_id'   => ['integer', 'exists:titles,id'],
            'name_en'    => ['string', 'max:255'],
            'name_ar'    => ['string', 'max:255'],
            'status'     => ['boolean'],
            'is_filter'     => ['boolean'],
        ];
    }
}
