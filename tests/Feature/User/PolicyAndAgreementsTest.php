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
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PolicyAndAgreementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Customer', 'guard_name' => 'api-user']);
    }

    private function createUser(): User
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

    private function createOrderableStore(): array
    {
        Setting::firstOrCreate([], [
            'delivery_fee' => 50,
            'vat_percentage' => 14,
            'vat_enabled' => true,
        ]);

        $paymentMethod = PaymentMethod::create(['name_en' => 'Cash', 'name_ar' => 'كاش']);
        $deliveryMethod = DeliveryMethod::create(['name_en' => 'Home', 'name_ar' => 'منزل']);

        $title = Title::create(['name_en' => 'Title', 'name_ar' => 'عنوان']);
        $category = Category::create([
            'title_id' => $title->id,
            'name_en' => 'Category',
            'name_ar' => 'تصنيف',
            'slug' => 'category',
            'status' => true,
            'is_filter' => false,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name_en' => 'Product',
            'name_ar' => 'منتج',
            'price' => 100,
            'discount_percentage' => 0,
            'stock' => 10,
            'status' => true,
            'is_new_arrival' => false,
            'is_best_seller' => false,
            'is_e_copy' => false,
            'publisher' => 'Pub',
        ]);

        return compact('paymentMethod', 'deliveryMethod', 'product');
    }

    private function orderPayload(PaymentMethod $paymentMethod, DeliveryMethod $deliveryMethod, array $overrides = []): array
    {
        return array_merge([
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
        ], $overrides);
    }

    // =========================================================================
    // POLICIES ENDPOINT
    // =========================================================================

    public function test_policies_endpoint_returns_policy_contents(): void
    {
        Setting::firstOrCreate([], [
            'terms_conditions_en' => 'Terms and conditions content (EN).',
            'terms_conditions_ar' => 'الشروط والأحكام (AR).',
            'privacy_policy_en' => 'Privacy policy content (EN).',
            'privacy_policy_ar' => 'سياسة الخصوصية (AR).',
            'return_exchange_policy_en' => 'Return policy content (EN).',
            'return_exchange_policy_ar' => 'سياسة الاسترجاع (AR).',
        ]);

        $response = $this->getJson('/api/policies');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'terms_conditions_en' => 'Terms and conditions content (EN).',
                    'terms_conditions_ar' => 'الشروط والأحكام (AR).',
                    'privacy_policy_en' => 'Privacy policy content (EN).',
                    'privacy_policy_ar' => 'سياسة الخصوصية (AR).',
                    'return_exchange_policy_en' => 'Return policy content (EN).',
                    'return_exchange_policy_ar' => 'سياسة الاسترجاع (AR).',
                ],
            ]);
    }

    public function test_policies_endpoint_returns_nulls_when_not_configured(): void
    {
        $response = $this->getJson('/api/policies');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'terms_conditions_en' => null,
                    'terms_conditions_ar' => null,
                    'privacy_policy_en' => null,
                    'privacy_policy_ar' => null,
                    'return_exchange_policy_en' => null,
                    'return_exchange_policy_ar' => null,
                ],
            ]);
    }

    // =========================================================================
    // ADMIN CAN MANAGE POLICY SETTINGS
    // =========================================================================

    public function test_admin_can_update_policy_settings(): void
    {
        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'is_super' => true,
            'is_active' => true,
        ]);

        $token = auth()->guard('api-admin')->login($admin);

        $response = $this->putJson('/api/admin/settings', [
            'terms_conditions_en' => 'New terms.',
            'terms_conditions_ar' => 'شروط جديدة.',
            'privacy_policy_en' => 'New privacy.',
            'privacy_policy_ar' => 'خصوصية جديدة.',
            'return_exchange_policy_en' => 'New return policy.',
            'return_exchange_policy_ar' => 'سياسة استرجاع جديدة.',
        ], $this->authHeaders($token));

        $response->assertOk();

        $setting = Setting::first();
        $this->assertSame('New terms.', $setting->terms_conditions_en);
        $this->assertSame('شروط جديدة.', $setting->terms_conditions_ar);
        $this->assertSame('New privacy.', $setting->privacy_policy_en);
        $this->assertSame('خصوصية جديدة.', $setting->privacy_policy_ar);
        $this->assertSame('New return policy.', $setting->return_exchange_policy_en);
        $this->assertSame('سياسة استرجاع جديدة.', $setting->return_exchange_policy_ar);
    }

    public function test_long_styled_policy_content_is_saved_and_retrieved_unchanged(): void
    {
        $paragraph = '<h2>Section</h2><p>This <strong>styled</strong> paragraph contains exactly the words needed to exceed three thousand words when repeated many times. It includes <em>emphasized text</em>, <a href="#">links</a>, list items, and other formatting that the store admin may want to keep intact when arranging terms and conditions, privacy policy, or return and exchange policy content.</p><ul><li>Bullet one</li><li>Bullet two</li></ul>';
        $longHtml = implode("\n", array_fill(0, 120, $paragraph));

        $admin = Admin::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'is_super' => true,
            'is_active' => true,
        ]);

        $token = auth()->guard('api-admin')->login($admin);

        $response = $this->putJson('/api/admin/settings', [
            'terms_conditions_en' => $longHtml,
            'terms_conditions_ar' => $longHtml,
        ], $this->authHeaders($token));

        $response->assertOk();

        $setting = Setting::first();
        $this->assertGreaterThan(3000, str_word_count($setting->terms_conditions_en));
        $this->assertSame($longHtml, $setting->terms_conditions_en);
        $this->assertSame($longHtml, $setting->terms_conditions_ar);

        $public = $this->getJson('/api/policies');
        $public->assertOk();
        $this->assertSame($longHtml, $public->json('data.terms_conditions_en'));
        $this->assertSame($longHtml, $public->json('data.terms_conditions_ar'));
    }

    // =========================================================================
    // REGISTRATION AGREEMENTS
    // =========================================================================

    public function test_register_requires_terms_and_privacy_agreement(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'new@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['terms_and_conditions_agreed', 'privacy_policy_agreed']);
    }

    public function test_register_without_agreements_does_not_create_user(): void
    {
        $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'new@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseMissing('users', ['email' => 'new@test.com']);
    }

    public function test_register_stores_agreement_flags(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'new@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms_and_conditions_agreed' => true,
            'privacy_policy_agreed' => true,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'new@test.com',
            'terms_and_conditions_agreed' => true,
            'privacy_policy_agreed' => true,
        ]);
    }

    // =========================================================================
    // ORDER AGREEMENTS
    // =========================================================================

    public function test_order_requires_privacy_policy_agreement(): void
    {
        $user = $this->createUser();
        $token = auth()->guard('api-user')->login($user);

        [$paymentMethod, $deliveryMethod, $product] = array_values($this->createOrderableStore());

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $payload = $this->orderPayload($paymentMethod, $deliveryMethod, [
            'privacy_policy_agreed' => false,
        ]);

        $response = $this->postJson('/api/orders', $payload, $this->authHeaders($token));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('privacy_policy_agreed');
    }

    public function test_order_stores_agreement_flags(): void
    {
        $user = $this->createUser();
        $token = auth()->guard('api-user')->login($user);

        [$paymentMethod, $deliveryMethod, $product] = array_values($this->createOrderableStore());

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->postJson('/api/orders', $this->orderPayload($paymentMethod, $deliveryMethod), [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'terms_and_condition_agreed' => true,
            'privacy_policy_agreed' => true,
        ]);

        $this->assertSame(1, Order::where('user_id', $user->id)->count());
    }
}
