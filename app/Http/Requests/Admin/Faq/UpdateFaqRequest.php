<?php

namespace App\Http\Requests\Admin\Faq;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_en' => ['sometimes', 'required', 'string', 'max:1000'],
            'question_ar' => ['sometimes', 'required', 'string', 'max:1000'],
            'answer_en' => ['sometimes', 'required', 'string'],
            'answer_ar' => ['sometimes', 'required', 'string'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
