<?php

namespace App\Http\Requests\Admin\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $adminId = $this->route('admin');

        return [
            'name'      => ['sometimes', 'string', 'max:255'],
            'email'     => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($adminId),
            ],
            'phone'     => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::unique('admins', 'phone')->ignore($adminId),
            ],
            'avatar'    => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_super'  => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'roles' => ['sometimes', 'string', Rule::exists('roles', 'name')],

        ];
    }
}
