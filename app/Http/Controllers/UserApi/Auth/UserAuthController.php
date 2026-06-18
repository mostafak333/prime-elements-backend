<?php

namespace App\Http\Controllers\UserApi\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\UserLoginRequest;
use App\Http\Requests\User\Auth\UserRegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserAuthController extends Controller
{
    /**
     * Customer Registration Endpoint
     */
    public function register(UserRegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Automatically assign the default Customer role via Spatie
        $user->assignRole('Customer');

        // Log the new user in directly and issue a JWT token
        $token = auth()->guard('api-user')->login($user);

        return response()->json([
            'message' => 'Registration successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ]
        ], 201);
    }

    /**
     * Customer Login Endpoint
     */
    public function login(UserLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // Attempt login via Tymon JWT using the customer guard
        if (! $token = auth()->guard('api-user')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        $user = auth()->guard('api-user')->user();

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ]
        ], 200);
    }

    /**
     * Customer Logout Endpoint
     */
    public function logout()
    {
        // Invalidate the stateless token
        auth()->guard('api-user')->logout();

        return response()->json([
            'message' => 'User logged out successfully'
        ], 200);
    }
}
