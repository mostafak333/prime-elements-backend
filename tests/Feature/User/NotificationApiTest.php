<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Notifications\OrderStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_notification_text_is_localized_by_accept_language(): void
    {
        $this->createOrderNotification($this->user);

        $english = $this->getJson('/api/notifications', $this->authHeaders('en'));
        $english->assertOk()
            ->assertJsonPath('data.notifications.0.title', 'Order status updated')
            ->assertJsonPath('data.notifications.0.message', 'Order ORD-2026-001 is now Confirmed.')
            ->assertJsonPath('data.notifications.0.data.order.reference', 'ORD-2026-001');

        $arabic = $this->getJson('/api/notifications', $this->authHeaders('ar'));
        $arabic->assertOk()
            ->assertJsonPath('data.notifications.0.title', 'تم تحديث حالة الطلب')
            ->assertJsonPath('data.notifications.0.message', 'حالة الطلب ORD-2026-001 الآن تم التأكيد.');
    }

    public function test_notification_list_is_paginated_and_contains_unread_count(): void
    {
        $this->createOrderNotification($this->user, 'ORD-2026-001');
        $this->createOrderNotification($this->user, 'ORD-2026-002');

        $list = $this->getJson('/api/notifications?per_page=1&page=1', $this->authHeaders());
        $list->assertOk()
            ->assertJsonPath('data.pagination.total', 2)
            ->assertJsonPath('data.pagination.per_page', 1)
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.last_page', 2);

        $this->getJson('/api/notifications/unread-count', $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.unread_count', 2);
    }

    public function test_customer_can_mark_one_notification_as_read(): void
    {
        $notification = $this->createOrderNotification($this->user);

        $this->patchJson('/api/notifications/'.$notification->id.'/read', [], $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.is_read', true)
            ->assertJsonPath('data.read_at', fn ($value) => is_string($value));

        $this->assertDatabaseCount('notifications', 1);
        $this->getJson('/api/notifications/unread-count', $this->authHeaders())
            ->assertJsonPath('data.unread_count', 0);
    }

    public function test_customer_can_mark_all_notifications_as_read(): void
    {
        $this->createOrderNotification($this->user, 'ORD-2026-001');
        $this->createOrderNotification($this->user, 'ORD-2026-002');

        $this->patchJson('/api/notifications/read-all', [], $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.updated_count', 2);

        $this->getJson('/api/notifications/unread-count', $this->authHeaders())
            ->assertJsonPath('data.unread_count', 0);
    }

    public function test_customer_cannot_mark_another_users_notification_as_read(): void
    {
        $otherUser = User::factory()->create(['status' => 'active']);
        $notification = $this->createOrderNotification($otherUser);

        $this->patchJson('/api/notifications/'.$notification->id.'/read', [], $this->authHeaders())
            ->assertNotFound();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }

    public function test_customer_notification_endpoints_require_authentication(): void
    {
        $this->getJson('/api/notifications')->assertUnauthorized();
        $this->getJson('/api/notifications/unread-count')->assertUnauthorized();
    }

    public function test_private_channel_authorizes_only_the_matching_customer(): void
    {
        $pusherConnection = config('broadcasting.connections.pusher');
        $pusherConnection['key'] = 'test-key';
        $pusherConnection['secret'] = 'test-secret';
        $pusherConnection['app_id'] = 'test-app';
        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher' => $pusherConnection,
        ]);
        require base_path('routes/channels.php');

        $ownChannelResponse = $this->postJson('/api/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-users.'.$this->user->id,
        ], $this->authHeaders());
        $this->assertSame(200, $ownChannelResponse->status(), $ownChannelResponse->getContent());
        $ownChannelResponse->assertJsonStructure(['auth']);

        $otherUser = User::factory()->create(['status' => 'active']);
        $this->postJson('/api/broadcasting/auth', [
            'socket_id' => '1234.5678',
            'channel_name' => 'private-users.'.$otherUser->id,
        ], $this->authHeaders())
            ->assertForbidden();
    }

    private function createOrderNotification(User $user, string $reference = 'ORD-2026-001')
    {
        $notification = new OrderStatusNotification(42, $reference, 'confirmed');
        $user->notifyNow($notification, ['database']);

        return $user->notifications()->latest()->firstOrFail();
    }

    private function authHeaders(string $locale = 'en'): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
            'Accept-Language' => $locale,
        ];
    }
}
