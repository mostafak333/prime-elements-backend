<?php

namespace App\Http\Controllers\AdminApi\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\UpdateUserStatusRequest;
use App\Http\Resources\Admin\AdminUserResource;
use App\Models\User;
use App\Services\ReviewService;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    protected UserService $userService;

    protected ReviewService $reviewService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'name', 'email', 'phone', 'status']);
        $perPage = (int) $request->get('per_page', 15);

        $users = $this->userService->getAll($filters, $perPage);

        return $this->success([
            'users' => AdminUserResource::collection($users),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
        ], 'Users retrieved successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->find($id);

        return $this->success(
            new AdminUserResource($user),
            'User retrieved successfully.'
        );
    }

    public function updateStatus(UpdateUserStatusRequest $request, $userId): JsonResponse
    {
        $user = User::find($userId);

        if (! $user) {
            return $this->error('User not found.', 404);
        }

        $updatedUser = $this->userService->updateStatus($user, $request->input('status'));

        return $this->success(
            new AdminUserResource($updatedUser),
            'User status updated successfully.'
        );
    }
}
