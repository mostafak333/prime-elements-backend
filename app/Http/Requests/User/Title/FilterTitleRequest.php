<?php

namespace App\Http\Requests\User\Title;

use Illuminate\Foundation\Http\FormRequest;

class FilterTitleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => [
                'nullable',
                'integer',
                'exists:titles,id',
            ],
        ];
    }
}
