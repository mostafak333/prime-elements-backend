<?php

namespace App\Http\Requests\Admin\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($roleId)->where(fn ($q) => $q->where('guard_name', 'api-admin'))],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['required', 'string', Rule::exists('permissions', 'name')->where(fn ($q) => $q->where('guard_name', 'api-admin'))],
        ];
    }
}
