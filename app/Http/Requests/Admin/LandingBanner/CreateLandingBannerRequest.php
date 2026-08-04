<?php

namespace App\Http\Requests\Admin\LandingBanner;

use Illuminate\Foundation\Http\FormRequest;

class CreateLandingBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_en'       => ['required', 'string', 'max:255'],
            'title_ar'       => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'image'          => ['nullable', 'string'],
            'button_enabled' => ['boolean'],
            'button_name_en' => ['nullable', 'required_if:button_enabled,true', 'string', 'max:255'],
            'button_name_ar' => ['nullable', 'string', 'max:255'],
            'button_link'    => ['nullable', 'required_if:button_enabled,true', 'string', 'max:255'],
            'status'         => ['boolean'],
            'sort_order'     => ['integer', 'min:0'],
        ];
    }
}
