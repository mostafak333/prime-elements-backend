<?php

namespace App\Http\Controllers\AdminApi\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\AdminLoginRequest;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    /**
     * Admin Login Endpoint
     */
    public function login(AdminLoginRequest $request)
    {
        // The incoming data is already automatically validated here!
        $credentials = $request->only('email', 'password');

        // Attempt authentication via Tymon JWT guard
        if (! $token = auth()->guard('api-admin')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        // Get the authenticated admin record
        $admin = auth()->guard('api-admin')->user();

        return response()->json([
            'message' => 'Admin login successful',
            'access_token' => $token, // Clean stateless JWT string
            'token_type' => 'Bearer',
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'roles' => $admin->getRoleNames(), // Spatie helper
            ]
        ], 200);
    }

    /**
     * Admin Logout Endpoint
     */
    public function logout()
    {
        // Invalidate the JWT token completely state-side
        auth()->guard('api-admin')->logout();

        return response()->json([
            'message' => 'Admin logged out successfully'
        ], 200);
    }
}
