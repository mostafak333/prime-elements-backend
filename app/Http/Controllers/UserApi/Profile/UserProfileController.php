<?php

namespace App\Http\Controllers\UserApi\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\UpdateProfileRequest;
use App\Http\Resources\User\UserResource;
use App\Services\UserProfileService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserProfileController extends Controller
{
    use ApiResponse;

    public function __construct(
        private UserProfileService $userProfileService
    ) {}

    public function show(): JsonResponse
    {
        $user = $this->userProfileService->getProfile();

        return $this->success([
            'user' => new UserResource($user),
        ], 'Profile retrieved successfully.');
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->userProfileService->updateProfile($request->validated());

        return $this->success([
            'message' => 'Profile updated successfully',
            'user'    => new UserResource($user),
        ], 'Profile updated successfully.');
    }
}
