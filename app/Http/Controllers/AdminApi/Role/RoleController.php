<?php

namespace App\Http\Controllers\AdminApi\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\CreateRoleRequest;
use App\Http\Requests\Admin\Role\UpdateRoleRequest;
use App\Http\Resources\Admin\RoleResource;
use App\Models\Admin;
use App\Services\RoleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    use ApiResponse;

    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $roles = $this->roleService->getAll($perPage);

        return $this->success([
            'roles' => RoleResource::collection($roles),
            'pagination' => [
                'total' => $roles->total(),
                'per_page' => $roles->perPage(),
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
            ],
        ], 'Roles retrieved successfully.');
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        $role = $this->roleService->create($request->validated());

        return $this->success(
            new RoleResource($role),
            'Role created successfully.',
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->roleService->find($id);

        return $this->success(
            new RoleResource($role),
            'Role retrieved successfully.'
        );
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $updatedRole = $this->roleService->update($role, $request->validated());

        return $this->success(
            new RoleResource($updatedRole),
            'Role updated successfully.'
        );
    }

    public function destroy(Role $role): JsonResponse
    {
        try {
            $this->roleService->delete($role);

            return $this->success(null, 'Role deleted successfully.');
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
