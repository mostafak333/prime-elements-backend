<?php

namespace Tests\Feature;

use App\Models\AddressDetail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\DeliveryMethod;
use App\Models\EbookAccessCode;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class EbookOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_checkout_without_enough_codes(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => Hash::make('password123'), 'status' => 'active']);
        $token = JWTAuth::fromUser($user);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_e_copy' => true,
            'ebook_url' => 'https://example.com/book.pdf',
            'price' => 50,
            'stock' => 0,
        ]);
        EbookAccessCode::create(['product_id' => $product->id, 'code' => 'C1']);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);
        $payment = PaymentMethod::factory()->create(['status' => true]);
        $delivery = DeliveryMethod::factory()->create(['status' => true]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', [
                'address' => [
                    'full_name' => 'Test',
                    'phone' => '123',
                    'address_line1' => 'Addr',
                    'city' => 'Cairo',
                    'postal_code' => '123',
                    'country' => 'EG',
                ],
                'payment_method_id' => $payment->id,
                'delivery_method_id' => $delivery->id,
                'terms_and_condition_agreed' => true,
                'privacy_policy_agreed' => true,
            ]);

        $response->assertStatus(422);
    }

    public function test_pending_order_can_be_cancelled_and_codes_released(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => Hash::make('password123'), 'status' => 'active']);
        $token = JWTAuth::fromUser($user);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_e_copy' => true,
            'ebook_url' => 'https://example.com/book.pdf',
            'price' => 50,
            'stock' => 0,
        ]);
        EbookAccessCode::create(['product_id' => $product->id, 'code' => 'C1']);
        EbookAccessCode::create(['product_id' => $product->id, 'code' => 'C2']);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);
        $payment = PaymentMethod::factory()->create(['status' => true]);
        $delivery = DeliveryMethod::factory()->create(['status' => true]);

        $orderRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', [
                'address' => [
                    'full_name' => 'Test',
                    'phone' => '123',
                    'address_line1' => 'Addr',
                    'city' => 'Cairo',
                    'postal_code' => '123',
                    'country' => 'EG',
                ],
                'payment_method_id' => $payment->id,
                'delivery_method_id' => $delivery->id,
                'terms_and_condition_agreed' => true,
                'privacy_policy_agreed' => true,
            ]);

        $orderRes->assertStatus(201);
        $order = Order::first();
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(1, EbookAccessCode::whereNotNull('used_at')->count());

        $cancel = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/orders/{$order->id}/cancel");

        $cancel->assertStatus(200);
        $this->assertEquals(0, EbookAccessCode::whereNotNull('used_at')->count());
    }

    public function test_cannot_cancel_after_confirmation(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => Hash::make('password123'), 'status' => 'active']);
        $token = JWTAuth::fromUser($user);
        $admin = \App\Models\Admin::factory()->create(['password' => Hash::make('password123')]);
        $adminToken = JWTAuth::fromUser($admin);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_e_copy' => true,
            'ebook_url' => 'https://example.com/book.pdf',
            'price' => 50,
            'stock' => 0,
        ]);
        EbookAccessCode::create(['product_id' => $product->id, 'code' => 'C1']);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);
        $payment = PaymentMethod::factory()->create(['status' => true]);
        $delivery = DeliveryMethod::factory()->create(['status' => true]);

        $orderRes = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', [
                'address' => [
                    'full_name' => 'Test',
                    'phone' => '123',
                    'address_line1' => 'Addr',
                    'city' => 'Cairo',
                    'postal_code' => '123',
                    'country' => 'EG',
                ],
                'payment_method_id' => $payment->id,
                'delivery_method_id' => $delivery->id,
                'terms_and_condition_agreed' => true,
                'privacy_policy_agreed' => true,
            ]);

        $order = Order::first();
        $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->putJson("/api/admin/orders/{$order->id}/status", ['status' => 'confirmed']);

        $cancel = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/orders/{$order->id}/cancel");

        $cancel->assertStatus(422);
    }

    public function test_email_sent_on_confirmation_with_url_and_codes(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => Hash::make('password123'), 'status' => 'active']);
        $token = JWTAuth::fromUser($user);
        $admin = \App\Models\Admin::factory()->create(['password' => Hash::make('password123')]);
        $adminToken = JWTAuth::fromUser($admin);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_e_copy' => true,
            'ebook_url' => 'https://example.com/book.pdf',
            'price' => 50,
            'stock' => 0,
        ]);
        EbookAccessCode::create(['product_id' => $product->id, 'code' => 'CODE-ABC']);
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);
        $payment = PaymentMethod::factory()->create(['status' => true]);
        $delivery = DeliveryMethod::factory()->create(['status' => true]);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/orders', [
                'address' => [
                    'full_name' => 'Test',
                    'phone' => '123',
                    'address_line1' => 'Addr',
                    'city' => 'Cairo',
                    'postal_code' => '123',
                    'country' => 'EG',
                ],
                'payment_method_id' => $payment->id,
                'delivery_method_id' => $delivery->id,
                'terms_and_condition_agreed' => true,
                'privacy_policy_agreed' => true,
            ]);

        $order = Order::first();
        $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->putJson("/api/admin/orders/{$order->id}/status", ['status' => 'confirmed']);

        Mail::assertQueued(\App\Mail\EbookDeliveryMail::class);
    }
}
