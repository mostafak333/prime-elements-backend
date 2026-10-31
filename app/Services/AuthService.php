<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Mail\AdminPasswordResetMail;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function setPassword(array $data): void
    {
        $admin = Admin::where('invitation_token', $data['token'])
            ->where('invitation_token_expires_at', '>', now())
            ->first();

        if (! $admin) {
            throw ValidationException::withMessages([
                'token' => [__('messages.auth.invitation_token_invalid')],
            ]);
        }

        DB::transaction(function () use ($admin, $data) {
            $admin->update([
                'password' => Hash::make($data['password']),
                'invitation_token' => null,
                'invitation_token_expires_at' => null,
                'is_active' => true,
            ]);
        });
    }

    public function sendForgotPasswordEmail(array $data): void
    {
        $admin = Admin::where('email', $data['email'])->first();

        if (! $admin) {
            return;
        }

        $token = Str::random(64);

        $admin->update([
            'password_reset_token' => $token,
            'password_reset_token_expires_at' => now()->addHour(),
        ]);

        SendEmailJob::dispatch(
            $admin->email,
            new AdminPasswordResetMail($admin, $token, 60)
        );
    }

    public function resetPassword(array $data): void
    {
        $admin = Admin::where('password_reset_token', $data['token'])
            ->where('password_reset_token_expires_at', '>', now())
            ->first();

        if (! $admin) {
            throw ValidationException::withMessages([
                'token' => [__('messages.auth.password_reset_token_invalid')],
            ]);
        }

        DB::transaction(function () use ($admin, $data) {
            $admin->update([
                'password' => Hash::make($data['password']),
                'password_reset_token' => null,
                'password_reset_token_expires_at' => null,
            ]);
        });
    }

    public function changePassword(array $data): void
    {
        $admin = auth()->guard('api-admin')->user();

        if (! Hash::check($data['current_password'], $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('messages.auth.current_password_incorrect')],
            ]);
        }

        $admin->update([
            'password' => Hash::make($data['password']),
        ]);
    }
}
