<?php

namespace Tests\Feature\Admin;

use App\Models\AddressDetail;
use App\Models\Admin;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Title;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductCategoryDeleteTest extends TestCase
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
            'discount_percentage' => 0,
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
    // CATEGORY DELETION GUARDS
    // =========================================================================

    public function test_admin_cannot_delete_category_that_has_products(): void
    {
        $this->createProduct();

        $response = $this->deleteJson(
            "/api/admin/categories/{$this->category->id}",
            [],
            $this->authHeaders()
        );

        $response->assertUnprocessable();
        $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
    }

    public function test_admin_can_delete_category_without_products(): void
    {
        $response = $this->deleteJson(
            "/api/admin/categories/{$this->category->id}",
            [],
            $this->authHeaders()
        );

        $response->assertOk();
        $this->assertSoftDeleted('categories', ['id' => $this->category->id]);
    }

    // =========================================================================
    // PRODUCT DELETION GUARDS
    // =========================================================================

    public function test_admin_cannot_delete_product_referenced_by_orders(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();

        $address = AddressDetail::create([
            'user_id' => $user->id,
            'full_name' => 'Test User',
            'phone' => '01111111111',
            'address_line1' => 'Cairo',
            'city' => 'Cairo',
            'postal_code' => '12345',
            'country' => 'EG',
        ]);

        $paymentMethod = PaymentMethod::create(['name_en' => 'Cash', 'name_ar' => 'كاش']);
        $deliveryMethod = DeliveryMethod::create(['name_en' => 'Home', 'name_ar' => 'منزل']);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-'.uniqid(),
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
            'user_full_name' => 'Test User',
            'email' => $user->email,
            'phone_to_number' => '01111111111',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100,
            'discount' => 0,
        ]);

        $response = $this->deleteJson(
            "/api/admin/products/{$product->id}",
            [],
            $this->authHeaders()
        );

        $response->assertUnprocessable();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'deleted_at' => null]);
    }

    public function test_deleting_product_removes_it_from_carts_and_wishlists(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->deleteJson(
            "/api/admin/products/{$product->id}",
            [],
            $this->authHeaders()
        );

        $response->assertOk();

        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('wishlists', ['product_id' => $product->id]);

        $userToken = auth()->guard('api-user')->login($user);
        $cart = $this->getJson('/api/cart', $this->userHeaders($userToken));

        $cart->assertOk();
        $this->assertCount(0, $cart->json('data.items'));
        $this->assertSame(0, $cart->json('data.summary.total_items'));
    }

    // =========================================================================
    // DEACTIVATION CLEANUP
    // =========================================================================

    public function test_deactivating_product_removes_it_from_carts_and_wishlists(): void
    {
        $product = $this->createProduct();
        $user = $this->createUser();

        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->putJson(
            "/api/admin/products/{$product->id}",
            ['status' => false],
            $this->authHeaders()
        );

        $response->assertOk();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 0]);
        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('wishlists', ['product_id' => $product->id]);
    }

    public function test_cannot_add_inactive_product_to_cart_or_wishlist(): void
    {
        $product = $this->createProduct();
        $product->update(['status' => false]);

        $user = $this->createUser();
        $token = auth()->guard('api-user')->login($user);

        $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ], $this->userHeaders($token))->assertUnprocessable();

        $this->postJson('/api/wishlist', [
            'product_id' => $product->id,
        ], $this->userHeaders($token))->assertUnprocessable();
    }

    public function test_cart_retrieval_cleans_up_inactive_and_deleted_products(): void
    {
        $activeProduct = $this->createProduct(['name_en' => 'Active']);
        $deletedProduct = $this->createProduct(['name_en' => 'Gone']);
        $inactiveProduct = $this->createProduct(['name_en' => 'Inactive']);

        $deletedProduct->delete();
        $inactiveProduct->update(['status' => false]);

        $user = $this->createUser();
        $token = auth()->guard('api-user')->login($user);

        CartItem::create(['user_id' => $user->id, 'product_id' => $activeProduct->id, 'quantity' => 1]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $deletedProduct->id, 'quantity' => 1]);
        CartItem::create(['user_id' => $user->id, 'product_id' => $inactiveProduct->id, 'quantity' => 1]);

        $response = $this->getJson('/api/cart', $this->userHeaders($token));

        $response->assertOk();

        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertSame($activeProduct->id, $items[0]['product']['id']);
        $this->assertSame(1, $response->json('data.summary.total_items'));
        $this->assertSame(100.0, (float) $response->json('data.summary.subtotal'));

        $this->assertDatabaseMissing('cart_items', ['product_id' => $deletedProduct->id]);
        $this->assertDatabaseMissing('cart_items', ['product_id' => $inactiveProduct->id]);
    }
}
