<?php

namespace Tests\Feature\User;

use App\Models\Admin;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Title;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DiscountWindowTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private string $token;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'is_super' => true,
            'is_active' => true,
        ]);

        $this->token = auth()->guard('api-admin')->login($this->admin);

        $this->category = $this->createCategory();
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];
    }

    private function createCategory(): Category
    {
        $title = Title::create(['name_en' => 'Test', 'name_ar' => 'test']);

        return Category::create([
            'title_id' => $title->id,
            'name_en' => 'Category',
            'name_ar' => 'تصنيف',
            'slug' => 'category-'.uniqid(),
            'status' => true,
            'is_filter' => false,
        ]);
    }

    private function createProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $this->category->id,
            'name_en' => 'Product',
            'name_ar' => 'منتج',
            'price' => 100,
            'discount_percentage' => 10,
            'stock' => 5,
            'status' => true,
            'is_new_arrival' => false,
            'is_best_seller' => false,
            'is_e_copy' => false,
            'publisher' => 'Pub',
        ], $overrides));
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => Hash::make('password'),
        ]);
    }

    private function userHeaders(string $token): array
    {
        return [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];
    }

    // =========================================================================
    // EFFECTIVE DISCOUNT WINDOW
    // =========================================================================

    public function test_discount_within_window_is_returned(): void
    {
        $product = $this->createProduct([
            'name_en' => 'Active Offer',
            'discount_start_at' => now()->subDay(),
            'discount_end_at' => now()->addDay(),
        ]);

        $response = $this->getJson('/api/products');

        $response->assertOk();

        $data = collect($response->json('data.products'))->firstWhere('id', $product->id);

        $this->assertNotNull($data);
        $this->assertSame('10.00', $data['discount_percentage']);
        $this->assertSame('10.00', $data['discount_amount']);
        $this->assertSame('90.00', $data['price_after_discount']);
        $this->assertTrue($data['has_offer']);
        $this->assertNotNull($data['discount_start_at']);
        $this->assertNotNull($data['discount_end_at']);
    }

    public function test_expired_discount_is_zeroed_in_db_when_listed(): void
    {
        $product = $this->createProduct([
            'name_en' => 'Expired Offer',
            'discount_percentage' => 50,
            'discount_start_at' => now()->subDays(2),
            'discount_end_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/products');

        $response->assertOk();

        $data = collect($response->json('data.products'))->firstWhere('id', $product->id);

        $this->assertNotNull($data);
        $this->assertSame('0.00', $data['discount_percentage']);
        $this->assertSame('0.00', $data['discount_amount']);
        $this->assertSame('100.00', $data['price_after_discount']);
        $this->assertFalse($data['has_offer']);

        $this->assertEquals(0, $product->fresh()->getRawOriginal('discount_percentage'));
    }

    public function test_future_discount_is_not_active_yet(): void
    {
        $product = $this->createProduct([
            'name_en' => 'Upcoming Offer',
            'discount_percentage' => 30,
            'discount_start_at' => now()->addDay(),
            'discount_end_at' => now()->addDays(2),
        ]);

        $response = $this->getJson('/api/products');

        $response->assertOk();

        $data = collect($response->json('data.products'))->firstWhere('id', $product->id);

        $this->assertNotNull($data);
        $this->assertSame('0.00', $data['discount_percentage']);
        $this->assertSame('100.00', $data['price_after_discount']);
        $this->assertFalse($data['has_offer']);
    }

    public function test_has_offer_true_within_window_with_absolute_times(): void
    {
        $product = $this->createProduct([
            'name_en' => 'Timed Offer',
            'discount_percentage' => 5,
            'discount_start_at' => '2026-09-13 00:00:00',
            'discount_end_at' => '2026-09-13 21:30:00',
        ]);

        $this->travelTo('2026-09-13 12:00:00');

        $response = $this->getJson('/api/products');

        $response->assertOk();

        $data = collect($response->json('data.products'))->firstWhere('id', $product->id);

        $this->assertTrue($data['has_offer']);
        $this->assertSame('5.00', $data['discount_percentage']);
        $this->assertSame('5.00', $data['discount_amount']);
    }

    public function test_has_offer_false_before_start_but_values_preserved(): void
    {
        $product = $this->createProduct([
            'name_en' => 'Timed Offer',
            'discount_percentage' => 5,
            'discount_start_at' => '2026-09-13 00:00:00',
            'discount_end_at' => '2026-09-13 21:30:00',
        ]);

        $this->travelTo('2026-09-12 23:59:59');

        $response = $this->getJson('/api/products');

        $response->assertOk();

        $data = collect($response->json('data.products'))->firstWhere('id', $product->id);

        $this->assertFalse($data['has_offer']);
        $this->assertSame('0.00', $data['discount_percentage']);
        $this->assertSame('0.00', $data['discount_amount']);
        $this->assertSame('100.00', $data['price_after_discount']);

        $fresh = $product->fresh();
        $this->assertEquals(5, $fresh->getRawOriginal('discount_percentage'));
        $this->assertSame('2026-09-13 00:00:00', $fresh->discount_start_at?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-13 21:30:00', $fresh->discount_end_at?->format('Y-m-d H:i:s'));
    }

    public function test_has_offer_false_after_end_zeroes_percentage_but_keeps_window(): void
    {
        $product = $this->createProduct([
            'name_en' => 'Timed Offer',
            'discount_percentage' => 5,
            'discount_start_at' => '2026-09-13 00:00:00',
            'discount_end_at' => '2026-09-13 21:30:00',
        ]);

        $this->travelTo('2026-09-13 21:30:01');

        $response = $this->getJson('/api/products');

        $response->assertOk();

        $data = collect($response->json('data.products'))->firstWhere('id', $product->id);

        $this->assertFalse($data['has_offer']);
        $this->assertSame('0.00', $data['discount_percentage']);
        $this->assertSame('0.00', $data['discount_amount']);
        $this->assertSame('100.00', $data['price_after_discount']);

        $fresh = $product->fresh();
        $this->assertEquals(0, $fresh->getRawOriginal('discount_percentage'));
        $this->assertSame('2026-09-13 00:00:00', $fresh->discount_start_at?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-13 21:30:00', $fresh->discount_end_at?->format('Y-m-d H:i:s'));
    }

    // =========================================================================
    // HAS_OFFER FILTER
    // =========================================================================

    public function test_has_offer_filter_returns_only_active_offers(): void
    {
        $active = $this->createProduct([
            'name_en' => 'Active',
            'discount_percentage' => 20,
            'discount_end_at' => now()->addDays(3),
        ]);

        $expired = $this->createProduct([
            'name_en' => 'Expired',
            'discount_percentage' => 20,
            'discount_end_at' => now()->subDay(),
        ]);

        $noDiscount = $this->createProduct([
            'name_en' => 'No Discount',
            'discount_percentage' => 0,
        ]);

        $response = $this->getJson('/api/products?filter=has_offer');

        $response->assertOk();

        $ids = collect($response->json('data.products'))->pluck('id')->all();

        $this->assertContains($active->id, $ids);
        $this->assertNotContains($expired->id, $ids);
        $this->assertNotContains($noDiscount->id, $ids);
    }

    public function test_invalid_filter_value_is_rejected(): void
    {
        $response = $this->getJson('/api/products?filter=invalid');

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('filter');
    }

    // =========================================================================
    // ADMIN SET/UPDATE DISCOUNT FIELDS
    // =========================================================================

    public function test_admin_can_set_discount_fields_when_creating_product(): void
    {
        $category = $this->createCategory();

        $response = $this->postJson('/api/admin/products', [
            'category_id' => $category->id,
            'name_en' => 'New Product',
            'name_ar' => 'منتج جديد',
            'short_description_en' => 'Short',
            'short_description_ar' => 'قصير',
            'price' => 200,
            'discount_percentage' => 25,
            'discount_start_at' => '2026-09-01 00:00:00',
            'discount_end_at' => '2026-10-01 00:00:00',
            'stock' => 10,
            'status' => true,
            'is_new_arrival' => false,
            'is_best_seller' => false,
            'is_e_copy' => false,
            'publisher' => 'Pub',
        ], $this->authHeaders());

        $response->assertCreated();

        $product = Product::where('name_en', 'New Product')->first();

        $this->assertNotNull($product->discount_start_at);
        $this->assertNotNull($product->discount_end_at);
        $this->assertEquals(25, $product->getRawOriginal('discount_percentage'));
    }

    public function test_admin_update_does_not_wipe_discount_fields(): void
    {
        $product = $this->createProduct([
            'name_en' => 'Expired Offer',
            'discount_percentage' => 30,
            'discount_end_at' => now()->subDay(),
        ]);

        $response = $this->putJson("/api/admin/products/{$product->id}", [
            'name_en' => 'Renamed',
        ], $this->authHeaders());

        $response->assertOk();

        $product->refresh();

        $this->assertSame('Renamed', $product->name_en);
        $this->assertEquals(30, $product->getRawOriginal('discount_percentage'));
    }

    public function test_admin_rejects_discount_percentage_over_100(): void
    {
        $category = $this->createCategory();

        $response = $this->postJson('/api/admin/products', [
            'category_id' => $category->id,
            'name_en' => 'Product',
            'name_ar' => 'منتج',
            'short_description_en' => 'Short',
            'short_description_ar' => 'قصير',
            'price' => 100,
            'discount_percentage' => 101,
            'stock' => 1,
            'status' => true,
            'is_new_arrival' => false,
            'is_best_seller' => false,
            'is_e_copy' => false,
            'publisher' => 'Pub',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('discount_percentage');
    }

    // =========================================================================
    // CART / ORDER PRICING
    // =========================================================================

    public function test_cart_pricing_ignores_expired_discount(): void
    {
        $product = $this->createProduct([
            'name_en' => 'Expired',
            'price' => 100,
            'discount_percentage' => 20,
            'discount_end_at' => now()->subDay(),
        ]);

        $user = $this->createUser();
        $token = auth()->guard('api-user')->login($user);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->getJson('/api/cart', $this->userHeaders($token));

        $response->assertOk();

        $this->assertSame(100.0, (float) $response->json('data.summary.subtotal'));
        $this->assertSame(0.0, (float) $response->json('data.summary.discount'));
    }

    public function test_cart_discount_is_percentage_based(): void
    {
        $product = $this->createProduct([
            'name_en' => 'On Offer',
            'price' => 100,
            'discount_percentage' => 10,
            'discount_end_at' => now()->addDay(),
        ]);

        $user = $this->createUser();
        $token = auth()->guard('api-user')->login($user);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->getJson('/api/cart', $this->userHeaders($token));

        $response->assertOk();

        $this->assertSame(200.0, (float) $response->json('data.summary.subtotal'));
        $this->assertSame(20.0, (float) $response->json('data.summary.discount'));
        $this->assertSame(20.0, (float) $response->json('data.summary.discount_amount'));
        $this->assertSame(10.0, (float) $response->json('data.summary.discount_percentage'));
    }

    public function test_order_discount_is_percentage_based(): void
    {
        Setting::firstOrCreate([], [
            'delivery_fee' => 50,
            'vat_percentage' => 14,
            'vat_enabled' => true,
        ]);

        $product = $this->createProduct([
            'price' => 100,
            'discount_percentage' => 10,
            'discount_end_at' => now()->addDay(),
        ]);

        $paymentMethod = PaymentMethod::create(['name_en' => 'Cash', 'name_ar' => 'كاش']);
        $deliveryMethod = DeliveryMethod::create(['name_en' => 'Home', 'name_ar' => 'منزل']);

        $user = $this->createUser();
        $token = auth()->guard('api-user')->login($user);

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->postJson('/api/orders', [
            'address' => [
                'full_name' => 'Test User',
                'phone' => '01111111111',
                'address_line1' => 'Cairo',
                'city' => 'Cairo',
                'postal_code' => '12345',
                'country' => 'EG',
            ],
            'payment_method_id' => $paymentMethod->id,
            'delivery_method_id' => $deliveryMethod->id,
            'terms_and_condition_agreed' => true,
            'privacy_policy_agreed' => true,
        ], $this->userHeaders($token));

        $response->assertCreated();

        $this->assertSame(20.0, (float) $response->json('data.discount_amount'));
        $this->assertSame(10.0, (float) $response->json('data.discount_percentage'));

        $order = Order::where('user_id', $user->id)->first();
        $orderItem = $order->orderItems()->first();
        $this->assertSame(10.0, (float) $orderItem->discount);
        $this->assertSame(10.0, (float) $response->json('data.items.0.discount_percentage'));
    }

    public function test_expire_command_zeroes_expired_discount_in_db(): void
    {
        $this->travelTo('2026-09-13 12:00:00');

        $product = $this->createProduct([
            'discount_percentage' => 10,
            'discount_start_at' => now()->subDays(2),
            'discount_end_at' => now()->subDay(),
        ]);

        $this->artisan('discounts:expire')->assertExitCode(0);

        $this->assertEquals(0, $product->fresh()->getRawOriginal('discount_percentage'));
    }

    public function test_expire_command_preserves_active_discount(): void
    {
        $this->travelTo('2026-09-13 12:00:00');

        $product = $this->createProduct([
            'discount_percentage' => 10,
            'discount_start_at' => now()->subDay(),
            'discount_end_at' => now()->addDay(),
        ]);

        $this->artisan('discounts:expire')->assertExitCode(0);

        $this->assertEquals(10, $product->fresh()->getRawOriginal('discount_percentage'));
    }

    public function test_expire_command_preserves_upcoming_scheduled_discount(): void
    {
        $this->travelTo('2026-09-13 12:00:00');

        $product = $this->createProduct([
            'discount_percentage' => 10,
            'discount_start_at' => now()->addDay(),
            'discount_end_at' => now()->addDays(2),
        ]);

        $this->artisan('discounts:expire')->assertExitCode(0);

        $this->assertEquals(10, $product->fresh()->getRawOriginal('discount_percentage'));
    }

    public function test_expire_command_is_safe_when_discount_already_zero(): void
    {
        $this->travelTo('2026-09-13 12:00:00');

        $product = $this->createProduct([
            'discount_percentage' => 0,
            'discount_start_at' => now()->subDays(2),
            'discount_end_at' => now()->subDay(),
        ]);

        $this->artisan('discounts:expire')->assertExitCode(0);

        $this->assertEquals(0, $product->fresh()->getRawOriginal('discount_percentage'));
    }

    public function test_admin_product_list_zeroes_expired_percentage_in_db_and_response(): void
    {
        $product = $this->createProduct([
            'name_en' => 'Expired For Admin',
            'discount_percentage' => 5,
            'discount_start_at' => '2026-09-13 00:00:00',
            'discount_end_at' => '2026-09-13 21:30:00',
        ]);

        $this->travelTo('2026-09-13 21:30:01');

        $response = $this->getJson('/api/admin/products', $this->authHeaders());

        $response->assertOk();

        $data = collect($response->json('data.products'))->firstWhere('id', $product->id);

        $this->assertNotNull($data);
        $this->assertSame('0.00', $data['discount_percentage']);
        $this->assertSame('0.00', $data['discount_amount']);
        $this->assertFalse($data['has_offer']);
        $this->assertEquals(0, $product->fresh()->getRawOriginal('discount_percentage'));
    }
}
