<?php

namespace App\Http\Requests\Admin\Ebook;

use Illuminate\Foundation\Http\FormRequest;

class StoreEbookAccessCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codes' => ['required', 'array', 'min:1'],
            'codes.*' => self::codeRules(),
        ];
    }

    public static function codeRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    public function messages(): array
    {
        return [
            'codes.required' => __('validation.custom.codes.required'),
            'codes.array' => __('validation.custom.codes.array'),
            'codes.*.required' => __('validation.custom.codes.code_required'),
        ];
    }
}
