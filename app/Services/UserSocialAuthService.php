<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class UserSocialAuthService
{
    protected array $supportedProviders = ['google', 'facebook'];

    public function __construct(
        private EmailSubscriptionService $subscriptionService
    ) {}

    public function login(string $provider, string $token): array
    {
        if (! in_array($provider, $this->supportedProviders)) {
            throw ValidationException::withMessages([
                'provider' => ["Provider '{$provider}' is not supported."],
            ]);
        }

        $socialUser = Socialite::driver($provider)->userFromToken($token);

        $socialId = $socialUser->getId();
        $email = $socialUser->getEmail();
        $name = $socialUser->getName();
        $avatar = $socialUser->getAvatar();

        if (! $email) {
            throw ValidationException::withMessages([
                'email' => ['Unable to retrieve email from the social provider.'],
            ]);
        }

        return DB::transaction(function () use ($provider, $socialId, $email, $name, $avatar) {
            $user = User::where('social_provider', $provider)
                ->where('social_id', $socialId)
                ->first();

            if (! $user) {
                $user = User::where('email', $email)->first();

                if ($user) {
                    $user->update([
                        'social_provider' => $provider,
                        'social_id' => $socialId,
                        'avatar' => $avatar ?: $user->avatar,
                    ]);
                } else {
                    $user = User::create([
                        'name' => $name ?: explode('@', $email)[0],
                        'email' => $email,
                        'avatar' => $avatar,
                        'social_provider' => $provider,
                        'social_id' => $socialId,
                        'email_verified_at' => now(),
                    ]);

                    $user->assignRole('Customer');
                }

                $this->subscriptionService->linkGuestSubscription($user);
            }

            if ($user->status === 'blocked') {
                throw ValidationException::withMessages([
                    'email' => ['Your account has been blocked. Please contact support.'],
                ]);
            }

            $token = auth()->guard('api-user')->login($user);

            return [
                'user' => $user->fresh(),
                'token' => $token,
            ];
        });
    }
}
