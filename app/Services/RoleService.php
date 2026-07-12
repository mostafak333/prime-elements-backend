<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public function getAll(int $perPage = 15)
    {
        return Role::where('guard_name', 'api-admin')
            ->with('permissions')
            ->paginate($perPage);
    }

    public function create(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'api-admin',
        ]);

        if (!empty($data['permissions'])) {
            $permissions = Permission::whereIn('name', $data['permissions'])
                ->where('guard_name', 'api-admin')
                ->get();
            $role->syncPermissions($permissions);
        }

        return $role->load('permissions');
    }

    public function find(int $id): Role
    {
        return Role::where('guard_name', 'api-admin')
            ->with('permissions')
            ->findOrFail($id);
    }

    public function update(Role $role, array $data): Role
    {
        if ($role->name === 'SuperAdmin') {
            throw ValidationException::withMessages([
                'role' => ['The SuperAdmin role cannot be modified.'],
            ]);
        }

        if (isset($data['name'])) {
            $role->update(['name' => $data['name']]);
        }

        if (isset($data['permissions'])) {
            $permissions = Permission::whereIn('name', $data['permissions'])
                ->where('guard_name', 'api-admin')
                ->get();
            $role->syncPermissions($permissions);
        }

        return $role->fresh()->load('permissions');
    }

    public function delete(Role $role): void
    {
        if ($role->name === 'SuperAdmin') {
            throw ValidationException::withMessages([
                'role' => ['The SuperAdmin role cannot be deleted.'],
            ]);
        }

        $role->delete();
    }
}
