<?php

namespace App\Http\Requests\Admin\Review;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:active,hidden'],
        ];
    }
}
