<?php

namespace App\Http\Controllers\UserApi\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\SocialLoginRequest;
use App\Http\Resources\User\UserResource;
use App\Services\UserSocialAuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserSocialAuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private UserSocialAuthService $socialAuthService
    ) {}

    public function login(string $provider, SocialLoginRequest $request): JsonResponse
    {
        $result = $this->socialAuthService->login($provider, $request->input('token'));

        return $this->success([
            'message' => 'Login successful',
            'access_token' => $result['token'],
            'token_type' => 'Bearer',
            'user' => new UserResource($result['user']),
        ]);
    }
}
