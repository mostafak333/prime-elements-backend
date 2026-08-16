<?php

namespace App\Http\Controllers\AdminApi\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\AdminLoginRequest;
use App\Http\Requests\Admin\Auth\ChangePasswordRequest;
use App\Http\Requests\Admin\Auth\ForgotPasswordRequest;
use App\Http\Requests\Admin\Auth\ResetPasswordRequest;
use App\Http\Requests\Admin\Auth\SetPasswordRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Models\Admin;
use App\Services\AuthService;
use App\Traits\ApiResponse;

class AdminAuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AuthService $authService
    ) {}

    public function setPassword(SetPasswordRequest $request)
    {
        $this->authService->setPassword($request->validated());

        return $this->success(null, 'Password set successfully');
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $this->authService->sendForgotPasswordEmail($request->validated());

        return $this->success(null, 'If the email exists, a password reset link has been sent');
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $this->authService->resetPassword($request->validated());

        return $this->success(null, 'Password reset successfully');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->authService->changePassword($request->validated());

        return $this->success(null, 'Password changed successfully');
    }

    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $admin = Admin::where('email', $credentials['email'])->first();

        if ($admin && ! $admin->is_active) {
            return $this->error('Your account is inactive. Please contact the administrator.', 403);
        }

        if (! $token = auth()->guard('api-admin')->attempt($credentials)) {
            return $this->error('The provided credentials do not match our records.', 401);
        }

        $admin = auth()->guard('api-admin')->user();

        return $this->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'admin' => new AdminResource($admin),
        ], 'Admin login successful', 200);
    }

    public function logout()
    {
        auth()->guard('api-admin')->logout();

        return $this->success(null, 'Admin logged out successfully', 200);
    }
}
