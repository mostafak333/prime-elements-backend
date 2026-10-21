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
            'messages.admin_role.retrieved'
        );
    }

    public function sync(Request $request, Admin $admin): JsonResponse
    {
        $validated = $request->validate([
            'roles' => ['required'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where(fn ($q) => $q->where('guard_name', 'api-admin'))],
        ]);

        $roles = is_array($validated['roles']) ? $validated['roles'] : [$validated['roles']];

        $admin->syncRoles($roles);

        return $this->success(
            RoleResource::collection($admin->fresh()->roles),
            'messages.admin_role.updated'
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
                'role' => [__('messages.admin_role.role_not_exists')],
            ]);
        }

        if (! $admin->hasRole($role)) {
            throw ValidationException::withMessages([
                'role' => [__('messages.admin_role.role_not_assigned')],
            ]);
        }

        $admin->removeRole($roleModel);

        return $this->success(
            RoleResource::collection($admin->fresh()->roles),
            'messages.admin_role.role_removed'
        );
    }
}
