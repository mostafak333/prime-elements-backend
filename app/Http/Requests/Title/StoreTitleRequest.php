<?php

namespace App\Http\Requests\Title;

use Illuminate\Foundation\Http\FormRequest;

class StoreTitleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
        ];
    }
}
