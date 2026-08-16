<?php

namespace App\Services;

use App\Models\EmailSubscription;
use App\Models\User;

class EmailSubscriptionService
{
    public function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public function subscribe(string $email, ?User $user = null): EmailSubscription
    {
        $email = $this->normalizeEmail($email);

        $subscription = EmailSubscription::where('email', $email)->first();

        if ($subscription) {
            $updates = [
                'is_active' => true,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ];

            if ($user && ! $subscription->user_id) {
                $updates['user_id'] = $user->id;
            }

            $subscription->update($updates);

            return $subscription->fresh();
        }

        return EmailSubscription::create([
            'email' => $email,
            'user_id' => $user?->id,
            'is_active' => true,
            'subscribed_at' => now(),
        ]);
    }

    public function unsubscribe(string $email): void
    {
        $email = $this->normalizeEmail($email);

        $subscription = EmailSubscription::where('email', $email)->first();

        if ($subscription) {
            $subscription->update([
                'is_active' => false,
                'unsubscribed_at' => now(),
            ]);
        }
    }

    public function isSubscribed(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return EmailSubscription::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    public function linkGuestSubscription(User $user): void
    {
        $email = $this->normalizeEmail($user->email);

        EmailSubscription::where('email', $email)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);
    }
}
