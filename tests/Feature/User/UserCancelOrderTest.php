<?php

namespace Tests\Feature\User;

use App\Models\AddressDetail;
use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCancelOrderTest extends TestCase
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

    private function createOrder(string $status = 'pending', ?User $owner = null): Order
    {
        $address = AddressDetail::create([
            'user_id' => $this->user->id,
            'full_name' => 'Test User',
            'phone' => '01111111111',
            'address_line1' => 'Cairo',
            'city' => 'Cairo',
            'postal_code' => '12345',
            'country' => 'EG',
        ]);

        $paymentMethod = PaymentMethod::create(['name_en' => 'Cash', 'name_ar' => 'كاش']);
        $deliveryMethod = DeliveryMethod::create(['name_en' => 'Home', 'name_ar' => 'منزل']);

        return Order::create([
            'user_id' => ($owner ?? $this->user)->id,
            'order_number' => 'ORD-'.uniqid(),
            'address_details_id' => $address->id,
            'payment_method_id' => $paymentMethod->id,
            'delivery_method_id' => $deliveryMethod->id,
            'subtotal' => 100,
            'shipping' => 0,
            'discount' => 0,
            'tax' => 0,
            'total' => 100,
            'status' => $status,
            'payment_status' => 'unpaid',
            'user_full_name' => 'Test User',
            'email' => $this->user->email,
            'phone_to_number' => '01111111111',
        ]);
    }

    public function test_user_can_cancel_pending_order(): void
    {
        $order = $this->createOrder('pending');

        $this->patchJson("/api/orders/{$order->id}/cancel", [], $this->authHeaders())
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled')
            ->assertJsonPath('data.can_cancel', false);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }

    public function test_user_cannot_cancel_non_pending_order(): void
    {
        $order = $this->createOrder('confirmed');

        $this->patchJson("/api/orders/{$order->id}/cancel", [], $this->authHeaders())
            ->assertUnprocessable();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'confirmed']);
    }

    public function test_user_cannot_cancel_already_cancelled_order(): void
    {
        $order = $this->createOrder('cancelled');

        $this->patchJson("/api/orders/{$order->id}/cancel", [], $this->authHeaders())
            ->assertUnprocessable();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }

    public function test_user_cannot_cancel_another_users_order(): void
    {
        $other = User::create([
            'name' => 'Other',
            'email' => 'other@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $order = $this->createOrder('pending', $other);

        $this->patchJson("/api/orders/{$order->id}/cancel", [], $this->authHeaders())
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Order not found.');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
    }

    public function test_blocked_user_cannot_cancel_order(): void
    {
        $order = $this->createOrder('pending');

        $this->user->update(['status' => 'blocked']);

        $this->patchJson("/api/orders/{$order->id}/cancel", [], $this->authHeaders())
            ->assertForbidden();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
    }

    public function test_guest_cannot_cancel_order(): void
    {
        $order = $this->createOrder('pending');

        auth()->guard('api-user')->logout();

        $this->patchJson("/api/orders/{$order->id}/cancel", [], ['Accept' => 'application/json'])
            ->assertUnauthorized();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
    }

    public function test_user_orders_list_exposes_can_cancel_flag(): void
    {
        $pending = $this->createOrder('pending');
        $delivered = $this->createOrder('delivered');

        $response = $this->getJson('/api/orders', $this->authHeaders())->assertOk();

        $byId = collect($response->json('data.orders'))->keyBy('id');

        $this->assertTrue($byId[$pending->id]['can_cancel']);
        $this->assertFalse($byId[$delivered->id]['can_cancel']);
    }
}
