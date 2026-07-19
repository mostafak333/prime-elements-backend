<?php

namespace App\Http\Controllers\AdminApi\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\AdminLoginRequest;
use App\Http\Requests\Admin\Auth\ChangePasswordRequest;
use App\Http\Requests\Admin\Auth\CreateAdminRequest;
use App\Http\Requests\Admin\Auth\ForgotPasswordRequest;
use App\Http\Requests\Admin\Auth\ResetPasswordRequest;
use App\Http\Requests\Admin\Auth\SetPasswordRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Models\Admin;
use App\Services\AuthService;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function createAdmin(CreateAdminRequest $request)
    {
        $admin = $this->authService->createAdminInvitation($request->validated());

        return response()->json([
            'message' => 'Admin invitation sent successfully',
            'admin'   => new AdminResource($admin),
        ], 201);
    }

    public function setPassword(SetPasswordRequest $request)
    {
        $this->authService->setPassword($request->validated());

        return response()->json([
            'message' => 'Password set successfully',
        ], 200);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $this->authService->sendForgotPasswordEmail($request->validated());

        return response()->json([
            'message' => 'If the email exists, a password reset link has been sent',
        ], 200);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $this->authService->resetPassword($request->validated());

        return response()->json([
            'message' => 'Password reset successfully',
        ], 200);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $this->authService->changePassword($request->validated());

        return response()->json([
            'message' => 'Password changed successfully',
        ], 200);
    }

    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $admin = Admin::where('email', $credentials['email'])->first();

        if ($admin && ! $admin->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive. Please set your password via the invitation link.'],
            ]);
        }

        if (! $token = auth()->guard('api-admin')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        $admin = auth()->guard('api-admin')->user();

        return response()->json([
            'message'      => 'Admin login successful',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'admin'        => new AdminResource($admin),
        ], 200);
    }

    public function logout()
    {
        auth()->guard('api-admin')->logout();

        return response()->json([
            'message' => 'Admin logged out successfully',
        ], 200);
    }
}
