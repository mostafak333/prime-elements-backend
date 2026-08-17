<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Mail\UserPasswordResetMail;
use App\Mail\UserVerificationMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserAuthService
{
    public function __construct(
        private EmailSubscriptionService $subscriptionService
    ) {}

    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $token = Str::random(64);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'email_verification_token' => $token,
                'email_verification_token_expires_at' => now()->addHours(48),
            ]);

            $user->assignRole('Customer');

            $this->subscriptionService->linkGuestSubscription($user);

            SendEmailJob::dispatch(
                $user->email,
                new UserVerificationMail($user, $token, 2880)
            );

            return $user;
        });
    }

    public function verifyEmail(string $token): void
    {
        $user = User::where('email_verification_token', $token)
            ->where('email_verification_token_expires_at', '>', now())
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'token' => ['The verification token is invalid or has expired.'],
            ]);
        }

        $user->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'email_verification_token_expires_at' => null,
        ]);
    }

    public function sendForgotPasswordEmail(array $data): void
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return;
        }

        $token = Str::random(64);

        $user->update([
            'password_reset_token' => $token,
            'password_reset_token_expires_at' => now()->addHour(),
        ]);

        SendEmailJob::dispatch(
            $user->email,
            new UserPasswordResetMail($user, $token, 60)
        );
    }

    public function resetPassword(array $data): void
    {
        $user = User::where('password_reset_token', $data['token'])
            ->where('password_reset_token_expires_at', '>', now())
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'token' => ['The password reset token is invalid or has expired.'],
            ]);
        }

        DB::transaction(function () use ($user, $data) {
            $user->update([
                'password' => Hash::make($data['password']),
                'password_reset_token' => null,
                'password_reset_token_expires_at' => null,
            ]);
        });
    }

    public function changePassword(array $data): void
    {
        $user = auth()->guard('api-user')->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);
    }

    public function checkEmailVerification(User $user): bool
    {
        return ! $user->email_verified_at;
    }
}
