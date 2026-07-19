<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Illuminate\Validation\ValidationException;

class PermissionService
{
    public function getAll(int $perPage = 50)
    {
        return Permission::where('guard_name', 'api-admin')
            ->paginate($perPage);
    }

    public function create(array $data): Permission
    {
        return Permission::create([
            'name' => $data['name'],
            'guard_name' => 'api-admin',
        ]);
    }

    public function delete(Permission $permission): void
    {
        if ($permission->roles()->count() > 0) {
            throw ValidationException::withMessages([
                'permission' => ['Cannot delete a permission that is assigned to roles.'],
            ]);
        }

        $permission->delete();
    }
}
