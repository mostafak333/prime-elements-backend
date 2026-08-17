<?php

namespace App\Http\Controllers\AdminApi\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\RoleResource;
use App\Models\Admin;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    use ApiResponse;

    public function show(Admin $admin): JsonResponse
    {
        return $this->success(
            RoleResource::collection($admin->roles),
            'Admin roles retrieved successfully.'
        );
    }

    public function sync(Request $request, Admin $admin): JsonResponse
    {
        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', Rule::exists('roles', 'name')->where(fn ($q) => $q->where('guard_name', 'api-admin'))],
        ]);

        $admin->syncRoles($validated['roles']);

        return $this->success(
            RoleResource::collection($admin->fresh()->roles),
            'Admin roles updated successfully.'
        );
    }

    public function destroy(Admin $admin, string $role): JsonResponse
    {
        $roleModel = app(Role::class)
            ->where('name', $role)
            ->where('guard_name', 'api-admin')
            ->first();

        if (! $roleModel) {
            throw ValidationException::withMessages([
                'role' => ['The specified role does not exist.'],
            ]);
        }

        if (! $admin->hasRole($role)) {
            throw ValidationException::withMessages([
                'role' => ['The admin does not have this role.'],
            ]);
        }

        $admin->removeRole($roleModel);

        return $this->success(
            RoleResource::collection($admin->fresh()->roles),
            'Role removed from admin successfully.'
        );
    }
}
