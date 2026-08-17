<?php

namespace App\Http\Controllers\AdminApi\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Admin\CreateAdminRequest;
use App\Http\Requests\Admin\Admin\ListAdminsRequest;
use App\Http\Requests\Admin\Admin\UpdateAdminRequest;
use App\Http\Requests\Admin\Admin\UpdateAdminStatusRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Models\Admin;
use App\Services\AdminService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AdminService $adminService
    ) {}

    public function createAdmin(CreateAdminRequest $request)
    {
        $authenticatedAdmin = auth()->user();
        if ($authenticatedAdmin->is_super === false) {
            return $this->error('Only super admins can create new admin users.', 403);
        }
        $admin = $this->adminService->createAdminInvitation($request->validated());

        return $this->success([
            'message' => 'Admin invitation sent successfully',
            'admin' => new AdminResource($admin),
        ], 'Admin invitation sent successfully', 201);
    }

    public function index(ListAdminsRequest $request): JsonResponse
    {
        $filters = $request->filters();
        $perPage = $request->perPage();

        $users = $this->adminService->getAll($filters, $perPage);

        return $this->success([
            'users' => AdminResource::collection($users),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
        ], 'Users retrieved successfully.');
    }

    public function show(string $id): JsonResponse
    {
        $admin = Admin::find($id);

        if (! $admin) {
            return $this->error('Admin not found.', 404);
        }

        return $this->success(
            new AdminResource($admin),
            'Admin retrieved successfully.'
        );
    }

    public function update(UpdateAdminRequest $request, Admin $admin): JsonResponse
    {

        $authenticatedAdmin = auth()->user();

        // Check if user is super admin
        if (! $authenticatedAdmin || ! $authenticatedAdmin->is_super) {
            return $this->error('Only super admins can update admin users.', 403);
        }

        // Prevent super admin from demoting themselves
        if ($authenticatedAdmin->id === $admin->id) {
            if ($request->has('is_super') && $request->is_super === false) {
                return $this->error('You cannot remove your own super admin privileges.', 403);
            }

            if ($request->has('is_active') && $request->is_active === false) {
                return $this->error('You cannot deactivate your own account.', 403);
            }
        }

        try {
            $updatedAdmin = $this->adminService->update($admin, $request->validated());

            return $this->success(
                new AdminResource($updatedAdmin),
                'Admin updated successfully.'
            );
        } catch (\Exception $e) {
            return $this->error('Failed to update admin: ' . $e->getMessage(), 500);
        }
    }

    public function updateStatus(UpdateAdminStatusRequest $request, int $id): JsonResponse
    {
        $admin = Admin::find($id);

        if (! $admin) {
            return $this->error('Admin not found.', 404);
        }

        $updatedAdmin = $this->adminService->updateStatus($admin, $request->input('status'));

        return $this->success(
            new AdminResource($updatedAdmin),
            'Admin status updated successfully.'
        );
    }
}
