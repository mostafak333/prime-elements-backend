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

        return $this->success(null, 'messages.auth.password_set');
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $this->authService->sendForgotPasswordEmail($request->validated());

        return $this->success(null, 'messages.auth.password_reset_link_sent');
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $this->authService->resetPassword($request->validated());

        return $this->success(null, 'messages.auth.password_reset');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->authService->changePassword($request->validated());

        return $this->success(null, 'messages.auth.password_changed');
    }

    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $admin = Admin::where('email', $credentials['email'])->first();

        if ($admin && ! $admin->is_active) {
            return $this->error('messages.auth.account_inactive', 403);
        }

        if (! $token = auth()->guard('api-admin')->attempt($credentials)) {
            return $this->error('messages.auth.credentials_invalid', 401);
        }

        $admin = auth()->guard('api-admin')->user();

        return $this->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'admin' => new AdminResource($admin),
        ], 'messages.auth.admin_login_successful', 200);
    }

    public function logout()
    {
        auth()->guard('api-admin')->logout();

        return $this->success(null, 'messages.auth.admin_logout_successful', 200);
    }
}
