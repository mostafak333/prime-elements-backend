<?php

namespace App\Http\Requests\Admin\Ebook;

use Illuminate\Foundation\Http\FormRequest;

class ImportEbookAccessCodesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'extensions:xlsx,csv', 'max:20480'],
        ];
    }
}
