<?php

namespace Tests\Feature\Admin;

use App\Events\UserNotificationCreated;
use App\Models\AddressDetail;
use App\Models\Admin;
use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderStatusNotificationTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;

    private User $otherUser;

    private Order $order;

    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = Admin::factory()->create([
            'password' => Hash::make('password123'),
        ]);
        $this->adminToken = JWTAuth::fromUser($admin);

        $this->user = User::factory()->create(['status' => 'active']);
        $this->otherUser = User::factory()->create(['status' => 'active']);
        $address = AddressDetail::create([
            'user_id' => $this->user->id,
            'full_name' => 'Notification Customer',
            'phone' => '01111111111',
            'address_line1' => 'Cairo',
            'city' => 'Cairo',
            'postal_code' => '12345',
            'country' => 'EG',
        ]);
        $paymentMethod = PaymentMethod::create([
            'name_en' => 'Cash',
            'name_ar' => 'نقدي',
        ]);
        $deliveryMethod = DeliveryMethod::create([
            'name_en' => 'Home',
            'name_ar' => 'منزلي',
        ]);

        $this->order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-NOTIFY-1',
            'address_details_id' => $address->id,
            'payment_method_id' => $paymentMethod->id,
            'delivery_method_id' => $deliveryMethod->id,
            'subtotal' => 100,
            'shipping' => 0,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'user_full_name' => 'Notification Customer',
            'email' => $this->user->email,
            'phone_to_number' => '01111111111',
        ]);
    }

    public function test_admin_status_change_notifies_the_order_owner_and_queues_private_event(): void
    {
        Queue::fake();
        config(['queue.default' => 'database']);

        $this->updateStatus('confirmed')->assertOk();

        $notification = $this->user->notifications()->firstOrFail();
        $this->assertSame('order_status_changed', $notification->data['type']);
        $this->assertSame($this->order->id, $notification->data['order']['id']);
        $this->assertSame('ORD-NOTIFY-1', $notification->data['order']['reference']);
        $this->assertSame('confirmed', $notification->data['order']['status']);
        $this->assertSame(0, $this->otherUser->notifications()->count());

        Queue::assertPushed(BroadcastEvent::class, function (BroadcastEvent $job): bool {
            $event = $job->event;
            $channels = $event->broadcastOn();

            return $event instanceof UserNotificationCreated
                && $event->broadcastAs() === 'notification.created'
                && $channels[0] instanceof PrivateChannel
                && $channels[0]->name === 'private-users.'.$this->user->id
                && $event->broadcastWith()['data']['order']['reference'] === 'ORD-NOTIFY-1';
        });
    }

    public function test_repeated_same_status_does_not_create_duplicate_notifications(): void
    {
        Queue::fake();
        $this->updateStatus('confirmed')->assertOk();
        $this->updateStatus('confirmed')->assertUnprocessable();

        $this->assertSame(1, $this->user->notifications()->count());
        Queue::assertPushed(BroadcastEvent::class, 1);
    }

    public function test_notification_and_broadcast_are_created_only_after_transaction_commit(): void
    {
        Queue::fake();
        DB::beginTransaction();

        $this->updateStatus('confirmed')->assertOk();

        $this->assertSame('confirmed', $this->order->fresh()->status);
        $this->assertSame(0, $this->user->notifications()->count());
        Queue::assertNothingPushed();

        DB::commit();

        $this->assertSame(1, $this->user->notifications()->count());
        Queue::assertPushed(BroadcastEvent::class, 1);
    }

    public function test_rolled_back_status_update_creates_no_notification_or_broadcast(): void
    {
        Queue::fake();
        DB::beginTransaction();

        $this->updateStatus('confirmed')->assertOk();
        DB::rollBack();

        $this->assertSame('pending', $this->order->fresh()->status);
        $this->assertSame(0, $this->user->notifications()->count());
        Queue::assertNothingPushed();
    }

    private function updateStatus(string $status)
    {
        return $this->putJson(
            '/api/admin/orders/'.$this->order->id.'/status',
            ['status' => $status],
            [
                'Authorization' => 'Bearer '.$this->adminToken,
                'Accept' => 'application/json',
            ]
        );
    }
}
