<?php

namespace App\Http\Requests\Admin\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:admins,email'],
            'phone'     => ['required', 'string', 'max:20', 'unique:admins,phone'],
            'avatar'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_super'  => ['sometimes', 'boolean'],
            'roles' => ['required', 'string', Rule::exists('roles', 'name')],
        ];
    }
}
