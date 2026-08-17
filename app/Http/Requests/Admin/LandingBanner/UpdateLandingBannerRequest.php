<?php

namespace App\Http\Requests\Admin\LandingBanner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLandingBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_en' => ['sometimes', 'required', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'button_enabled' => ['sometimes', 'boolean'],
            'button_name_en' => ['nullable', 'required_if:button_enabled,true', 'string', 'max:255'],
            'button_name_ar' => ['nullable', 'string', 'max:255'],
            'button_link' => ['nullable', 'required_if:button_enabled,true', 'string', 'max:255'],
            'status' => ['sometimes', 'boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
