<?php

namespace App\Http\Controllers\AdminApi\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Permission\CreatePermissionRequest;
use App\Http\Resources\Admin\PermissionResource;
use App\Services\PermissionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    use ApiResponse;

    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 50);
        $permissions = $this->permissionService->getAll($perPage);

        return $this->success([
            'permissions' => PermissionResource::collection($permissions),
            'pagination' => [
                'total' => $permissions->total(),
                'per_page' => $permissions->perPage(),
                'current_page' => $permissions->currentPage(),
                'last_page' => $permissions->lastPage(),
            ],
        ], 'Permissions retrieved successfully.');
    }

    public function store(CreatePermissionRequest $request): JsonResponse
    {
        $permission = $this->permissionService->create($request->validated());

        return $this->success(
            new PermissionResource($permission),
            'Permission created successfully.',
            201
        );
    }

    public function destroy(Permission $permission): JsonResponse
    {
        try {
            $this->permissionService->delete($permission);

            return $this->success(null, 'Permission deleted successfully.');
        } catch (ValidationException $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
