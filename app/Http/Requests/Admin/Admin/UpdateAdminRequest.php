<?php

namespace App\Http\Requests\Admin\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->convertBooleanInput('is_super');
        $this->convertBooleanInput('is_active');
    }

    private function convertBooleanInput(string $key): void
    {
        if ($this->has($key)) {
            $this->merge([
                $key => filter_var($this->input($key), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function rules(): array
    {

        $adminId = $this->route('admin');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('admins', 'email')->ignore($adminId),
            ],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                Rule::unique('admins', 'phone')->ignore($adminId),
            ],
            'avatar' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_super' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'roles' => ['sometimes', 'nullable', function ($attribute, $value, $fail) {
                $roleNames = is_array($value) ? $value : [$value];

                foreach ($roleNames as $roleName) {
                    $exists = Role::where('name', $roleName)
                        ->where('guard_name', 'api-admin')
                        ->exists();

                    if (! $exists) {
                        $fail("The role \"{$roleName}\" does not exist.");
                    }
                }
            }],

        ];
    }
}
