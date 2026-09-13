<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BlockedUserTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifiedUser(): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }

    private function authHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];
    }

    public function test_active_user_can_access_protected_endpoint(): void
    {
        $user = $this->createVerifiedUser();
        $token = auth()->guard('api-user')->login($user);

        $this->getJson('/api/profile', $this->authHeaders($token))
            ->assertOk();
    }

    public function test_blocked_user_cannot_access_protected_endpoint(): void
    {
        $user = $this->createVerifiedUser();
        $token = auth()->guard('api-user')->login($user);

        $user->update(['status' => 'blocked']);

        $this->getJson('/api/profile', $this->authHeaders($token))
            ->assertForbidden();
    }

    public function test_blocked_user_cannot_place_order(): void
    {
        $user = $this->createVerifiedUser();
        $token = auth()->guard('api-user')->login($user);

        $user->update(['status' => 'blocked']);

        $this->postJson('/api/orders', [], $this->authHeaders($token))
            ->assertForbidden();
    }

    public function test_updating_user_status_ends_existing_session(): void
    {
        $user = $this->createVerifiedUser();
        $token = auth()->guard('api-user')->login($user);

        app(UserService::class)->updateStatus($user, 'blocked');

        $this->getJson('/api/profile', $this->authHeaders($token))
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'token_version' => 1,
        ]);
    }

    public function test_blocked_user_cannot_login(): void
    {
        $user = $this->createVerifiedUser();

        app(UserService::class)->updateStatus($user, 'blocked');

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertForbidden();
    }

    public function test_token_version_change_ends_existing_session(): void
    {
        $user = $this->createVerifiedUser();
        $token = auth()->guard('api-user')->login($user);

        $user->increment('token_version');

        $this->getJson('/api/profile', $this->authHeaders($token))
            ->assertUnauthorized();
    }
}
