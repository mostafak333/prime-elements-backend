<?php

namespace Tests\Feature\User;

use App\Models\EmailSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->token = auth()->guard('api-user')->login($this->user);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];
    }

    private function guestHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'User',
            'email' => $email,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
    }

    // =========================================================================
    // GUEST SUBSCRIBE
    // =========================================================================

    public function test_guest_can_subscribe(): void
    {
        $response = $this->postJson('/api/subscribe', [
            'email' => 'guest@example.com',
        ], $this->guestHeaders());

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Subscribed successfully.',
            ]);

        $this->assertDatabaseHas('email_subscriptions', [
            'email' => 'guest@example.com',
            'is_active' => true,
            'user_id' => null,
        ]);
    }

    // =========================================================================
    // AUTHENTICATED USER SUBSCRIBE
    // =========================================================================

    public function test_authenticated_user_can_subscribe(): void
    {
        $response = $this->postJson('/api/subscribe', [
            'email' => $this->user->email,
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Subscribed successfully.',
            ]);

        $this->assertDatabaseHas('email_subscriptions', [
            'email' => $this->user->email,
            'user_id' => $this->user->id,
            'is_active' => true,
        ]);
    }

    // =========================================================================
    // VALIDATION
    // =========================================================================

    public function test_invalid_email_is_rejected(): void
    {
        $response = $this->postJson('/api/subscribe', [
            'email' => 'not-an-email',
        ], $this->guestHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_missing_email_is_rejected(): void
    {
        $response = $this->postJson('/api/subscribe', [], $this->guestHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    // =========================================================================
    // DUPLICATE PREVENTION
    // =========================================================================

    public function test_duplicate_email_does_not_create_multiple_records(): void
    {
        $this->postJson('/api/subscribe', [
            'email' => 'test@example.com',
        ], $this->guestHeaders());

        $this->postJson('/api/subscribe', [
            'email' => 'test@example.com',
        ], $this->guestHeaders());

        $this->assertDatabaseCount('email_subscriptions', 1);
    }

    // =========================================================================
    // EMAIL NORMALIZATION
    // =========================================================================

    public function test_email_normalization_prevents_duplicate_subscriptions(): void
    {
        $this->postJson('/api/subscribe', [
            'email' => 'User@Example.com',
        ], $this->guestHeaders());

        $this->postJson('/api/subscribe', [
            'email' => 'user@example.com',
        ], $this->guestHeaders());

        $this->assertDatabaseCount('email_subscriptions', 1);

        $subscription = EmailSubscription::where('email', 'user@example.com')->first();
        $this->assertNotNull($subscription);
        $this->assertEquals('user@example.com', $subscription->email);
    }

    // =========================================================================
    // GUEST UNSUBSCRIBE
    // =========================================================================

    public function test_guest_can_unsubscribe(): void
    {
        $this->postJson('/api/subscribe', [
            'email' => 'guest@example.com',
        ], $this->guestHeaders());

        $response = $this->postJson('/api/unsubscribe', [
            'email' => 'guest@example.com',
        ], $this->guestHeaders());

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Subscription preference updated.',
            ]);

        $this->assertDatabaseHas('email_subscriptions', [
            'email' => 'guest@example.com',
            'is_active' => false,
        ]);
    }

    // =========================================================================
    // AUTHENTICATED USER UNSUBSCRIBE
    // =========================================================================

    public function test_authenticated_user_can_unsubscribe(): void
    {
        $this->postJson('/api/subscribe', [
            'email' => $this->user->email,
        ], $this->authHeaders());

        $response = $this->postJson('/api/unsubscribe', [
            'email' => $this->user->email,
        ], $this->authHeaders());

        $response->assertOk();

        $this->assertDatabaseHas('email_subscriptions', [
            'email' => $this->user->email,
            'is_active' => false,
        ]);
    }

    // =========================================================================
    // UNSUBSCRIBE DOES NOT DELETE
    // =========================================================================

    public function test_unsubscribe_does_not_delete_record(): void
    {
        $this->postJson('/api/subscribe', [
            'email' => 'guest@example.com',
        ], $this->guestHeaders());

        $this->postJson('/api/unsubscribe', [
            'email' => 'guest@example.com',
        ], $this->guestHeaders());

        $this->assertDatabaseCount('email_subscriptions', 1);
        $this->assertNotNull(EmailSubscription::where('email', 'guest@example.com')->first()->unsubscribed_at);
    }

    // =========================================================================
    // RE-SUBSCRIBE AFTER UNSUBSCRIBE
    // =========================================================================

    public function test_guest_can_subscribe_again_after_unsubscribing(): void
    {
        $this->postJson('/api/subscribe', [
            'email' => 'guest@example.com',
        ], $this->guestHeaders());

        $this->postJson('/api/unsubscribe', [
            'email' => 'guest@example.com',
        ], $this->guestHeaders());

        $this->assertDatabaseCount('email_subscriptions', 1);

        $response = $this->postJson('/api/subscribe', [
            'email' => 'guest@example.com',
        ], $this->guestHeaders());

        $response->assertOk();

        $this->assertDatabaseCount('email_subscriptions', 1);
        $this->assertDatabaseHas('email_subscriptions', [
            'email' => 'guest@example.com',
            'is_active' => true,
        ]);
    }

    // =========================================================================
    // IS_SUBSCRIBED IN USER RESOURCE
    // =========================================================================

    public function test_authenticated_user_is_subscribed_true_when_active(): void
    {
        $this->postJson('/api/subscribe', [
            'email' => $this->user->email,
        ], $this->authHeaders());

        $response = $this->getJson('/api/profile', $this->authHeaders());

        $response->assertOk();
        $this->assertTrue($response->json('data.user.is_subscribed'));
    }

    public function test_authenticated_user_is_subscribed_false_when_inactive(): void
    {
        $this->postJson('/api/subscribe', [
            'email' => $this->user->email,
        ], $this->authHeaders());

        $this->postJson('/api/unsubscribe', [
            'email' => $this->user->email,
        ], $this->authHeaders());

        $response = $this->getJson('/api/profile', $this->authHeaders());

        $response->assertOk();
        $this->assertFalse($response->json('data.user.is_subscribed'));
    }

    public function test_authenticated_user_is_subscribed_false_when_no_subscription(): void
    {
        $response = $this->getJson('/api/profile', $this->authHeaders());

        $response->assertOk();
        $this->assertFalse($response->json('data.user.is_subscribed'));
    }

    // =========================================================================
    // GUEST→USER LINKING
    // =========================================================================

    public function test_guest_subscription_can_be_associated_with_user_later(): void
    {
        EmailSubscription::create([
            'email' => 'link@test.com',
            'user_id' => null,
            'is_active' => true,
            'subscribed_at' => now(),
        ]);

        $newUser = $this->createUser('link@test.com');
        $newToken = auth()->guard('api-user')->login($newUser);

        $this->postJson('/api/subscribe', [
            'email' => 'link@test.com',
        ], [
            'Authorization' => 'Bearer '.$newToken,
            'Accept' => 'application/json',
        ]);

        $subscription = EmailSubscription::where('email', 'link@test.com')->first();
        $this->assertNotNull($subscription->user_id);
        $this->assertEquals($newUser->id, $subscription->user_id);
    }

    // =========================================================================
    // SECURITY: NO ARBITRARY USER_ID MANIPULATION
    // =========================================================================

    public function test_subscription_cannot_be_manipulated_using_arbitrary_user_id(): void
    {
        $otherUser = $this->createUser('other@test.com');
        $otherToken = auth()->guard('api-user')->login($otherUser);

        $this->postJson('/api/subscribe', [
            'email' => 'attacker@example.com',
        ], $this->authHeaders());

        $this->postJson('/api/subscribe', [
            'email' => 'attacker@example.com',
        ], [
            'Authorization' => 'Bearer '.$otherToken,
            'Accept' => 'application/json',
        ]);

        $subscription = EmailSubscription::where('email', 'attacker@example.com')->first();

        $this->assertNotNull($subscription);
        $this->assertEquals($this->user->id, $subscription->user_id);
    }

    // =========================================================================
    // SECURITY: UNSUBSCRIBE DOES NOT LEAK INFO
    // =========================================================================

    public function test_unsubscribe_response_does_not_leak_subscription_information(): void
    {
        $response = $this->postJson('/api/unsubscribe', [
            'email' => 'nonexistent@example.com',
        ], $this->guestHeaders());

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Subscription preference updated.',
            ]);

        $response->assertJsonMissing([
            'email' => 'nonexistent@example.com',
        ]);
    }
}
