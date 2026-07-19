<?php

namespace App\Http\Controllers\UserApi\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\ChangePasswordRequest;
use App\Http\Requests\User\Auth\ForgotPasswordRequest;
use App\Http\Requests\User\Auth\ResetPasswordRequest;
use App\Http\Requests\User\Auth\UserLoginRequest;
use App\Http\Requests\User\Auth\UserRegisterRequest;
use App\Http\Resources\User\UserResource;
use App\Services\UserAuthService;
use App\Traits\ApiResponse;

class UserAuthController extends Controller
{
    use ApiResponse;
    public function __construct(
        private UserAuthService $userAuthService
    ) {}

    public function register(UserRegisterRequest $request)
    {
        $user = $this->userAuthService->register($request->validated());

        return $this->success([
            'message' => 'Registration successful. Please verify your email.',
            'user'    => new UserResource($user),
        ], 201);
    }

    public function login(UserLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth()->guard('api-user')->attempt($credentials)) {

            return $this->error('The provided credentials do not match our records.', 401);
        }

        $user = auth()->guard('api-user')->user();

        if ($user->status === 'blocked') {
            auth()->guard('api-user')->logout();
            return $this->error('Your account has been blocked. Please contact support.', 403);
        }

        $check =  $this->userAuthService->checkEmailVerification($user);
        if ($check) {
            return $this->error('email not verified, please verify your email before logging in.', 401);
        }

        return $this->success([
            'message'      => 'Login successful',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => new UserResource($user),
        ], 200);
    }

    public function logout()
    {
        auth()->guard('api-user')->logout();

        return $this->success([
            'message' => 'User logged out successfully',
        ], 200);
    }

    public function verifyEmail(string $token)
    {
        $this->userAuthService->verifyEmail($token);

        return $this->success([
            'message' => 'Email verified successfully',
        ], 200);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $this->userAuthService->sendForgotPasswordEmail($request->validated());

        return $this->success([
            'message' => 'If the email exists, a password reset link has been sent',
        ], 200);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $this->userAuthService->resetPassword($request->validated());

        return $this->success([
            'message' => 'Password reset successfully',
        ], 200);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->userAuthService->changePassword($request->validated());

        return $this->success([
            'message' => 'Password changed successfully',
        ], 200);
    }
}
