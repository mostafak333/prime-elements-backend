<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\EbookAccessCode;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class BundleTest extends TestCase
{
    use RefreshDatabase;

    private function adminHeaders(): array
    {
        $admin = Admin::factory()->create();

        return ['Authorization' => 'Bearer '.JWTAuth::fromUser($admin)];
    }

    private function userHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer '.JWTAuth::fromUser($user)];
    }

    private function product(array $attributes = []): Product
    {
        return Product::factory()->create(array_merge([
            'category_id' => Category::factory()->create()->id,
            'status' => true,
            'stock' => 10,
            'is_e_copy' => false,
        ], $attributes));
    }

    public function test_admin_can_create_list_update_and_delete_a_bundle(): void
    {
        $firstProduct = $this->product();
        $secondProduct = $this->product();

        $response = $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/bundles', [
                'name_en' => 'Back to school',
                'name_ar' => 'العودة إلى المدرسة',
                'description_en' => 'A ready-made school bundle',
                'description_ar' => 'حزمة مدرسية جاهزة',
                'status' => true,
                'items' => [
                    ['product_id' => $firstProduct->id, 'quantity' => 2],
                    ['product_id' => $secondProduct->id, 'quantity' => 3],
                ],
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name_en', 'Back to school')
            ->assertJsonPath('data.items_count', 2)
            ->assertJsonStructure([
                'data' => ['id', 'token', 'share_url', 'status', 'items'],
            ]);

        $bundleId = $response->json('data.id');
        $originalToken = $response->json('data.token');

        $this->assertNotEmpty($originalToken);
        $this->assertStringContainsString($originalToken, $response->json('data.share_url'));
        $this->assertDatabaseHas('bundles', [
            'id' => $bundleId,
            'token' => $originalToken,
        ]);
        $this->assertDatabaseHas('bundle_items', [
            'bundle_id' => $bundleId,
            'product_id' => $firstProduct->id,
            'quantity' => 2,
        ]);

        $this->withHeaders($this->adminHeaders())
            ->getJson('/api/admin/bundles')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.bundles')
            ->assertJsonPath('data.pagination.total', 1);

        $updateResponse = $this->withHeaders($this->adminHeaders())
            ->putJson("/api/admin/bundles/{$bundleId}", [
                'name_en' => 'Updated bundle',
                'items' => [
                    ['product_id' => $firstProduct->id, 'quantity' => 5],
                ],
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.name_en', 'Updated bundle')
            ->assertJsonPath('data.items_count', 1);

        // Editing keeps the same public link.
        $this->assertSame($originalToken, $updateResponse->json('data.token'));
        $this->assertDatabaseHas('bundle_items', [
            'bundle_id' => $bundleId,
            'product_id' => $firstProduct->id,
            'quantity' => 5,
        ]);
        $this->assertDatabaseMissing('bundle_items', [
            'bundle_id' => $bundleId,
            'product_id' => $secondProduct->id,
            'deleted_at' => null,
        ]);

        $this->withHeaders($this->adminHeaders())
            ->putJson("/api/admin/bundles/{$bundleId}/status")
            ->assertOk()
            ->assertJsonPath('data.status', false);

        $this->withHeaders($this->adminHeaders())
            ->deleteJson("/api/admin/bundles/{$bundleId}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('bundles', ['id' => $bundleId]);
    }

    public function test_admin_bundle_validation_rejects_duplicate_products_and_unknown_products(): void
    {
        $product = $this->product();

        $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/bundles', [
                'name_en' => 'Duplicate bundle',
                'name_ar' => 'حزمة مكررة',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);

        $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/bundles', [
                'name_en' => 'Unknown product bundle',
                'name_ar' => 'حزمة منتج غير معروف',
                'items' => [
                    ['product_id' => 99999, 'quantity' => 1],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('items.0.product_id');
    }

    public function test_admin_cannot_create_a_bundle_when_physical_stock_is_insufficient(): void
    {
        $product = $this->product(['stock' => 2]);

        $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/bundles', [
                'name_en' => 'Too many copies',
                'name_ar' => 'نسخ كثيرة',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 3],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('items.0.quantity');

        $this->assertDatabaseCount('bundles', 0);
        $this->assertDatabaseCount('bundle_items', 0);
    }

    public function test_admin_cannot_create_a_bundle_when_ebook_access_codes_are_insufficient(): void
    {
        $ebook = $this->product([
            'is_e_copy' => true,
            'stock' => 0,
            'ebook_url' => 'https://example.com/book.pdf',
        ]);
        EbookAccessCode::create([
            'product_id' => $ebook->id,
            'code' => 'ONLY-ONE-CODE',
        ]);

        $this->withHeaders($this->adminHeaders())
            ->postJson('/api/admin/bundles', [
                'name_en' => 'Too many ebooks',
                'name_ar' => 'كتب إلكترونية كثيرة',
                'items' => [
                    ['product_id' => $ebook->id, 'quantity' => 2],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('items.0.quantity');

        $this->assertDatabaseCount('bundles', 0);
        $this->assertDatabaseCount('bundle_items', 0);
    }

    public function test_admin_cannot_update_a_bundle_beyond_current_stock(): void
    {
        $product = $this->product(['stock' => 5]);
        $bundle = Bundle::factory()->create(['status' => true]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->withHeaders($this->adminHeaders())
            ->putJson("/api/admin/bundles/{$bundle->id}", [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 6],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('items.0.quantity');

        $this->assertDatabaseHas('bundle_items', [
            'bundle_id' => $bundle->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'deleted_at' => null,
        ]);
    }

    public function test_public_preview_returns_an_active_bundle_and_hides_inactive_ones(): void
    {
        $product = $this->product();
        $bundle = Bundle::factory()->create(['status' => true]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->getJson("/api/bundles/{$bundle->token}")
            ->assertOk()
            ->assertJsonPath('data.token', $bundle->token)
            ->assertJsonPath('data.total_items', 2)
            ->assertJsonPath('data.items.0.product.id', $product->id);

        $bundle->update(['status' => false]);

        $this->getJson("/api/bundles/{$bundle->token}")
            ->assertNotFound()
            ->assertJsonPath('success', false);

        $this->getJson('/api/bundles/not-a-real-token')
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_customer_can_add_all_bundle_products_to_the_cart_with_quantity_merging(): void
    {
        $user = User::factory()->create();
        $firstProduct = $this->product(['stock' => 10]);
        $secondProduct = $this->product(['stock' => 10]);

        $bundle = Bundle::factory()->create(['status' => true]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $firstProduct->id,
            'quantity' => 2,
        ]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $secondProduct->id,
            'quantity' => 3,
        ]);

        // The customer already has one copy of the first product.
        CartItem::create([
            'user_id' => $user->id,
            'product_id' => $firstProduct->id,
            'quantity' => 1,
        ]);

        $response = $this->withHeaders($this->userHeaders($user))
            ->postJson("/api/bundles/{$bundle->token}/add-to-cart");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.total_items', 6)
            ->assertJsonCount(2, 'data.items')
            ->assertJsonPath('data.redirect_url', rtrim(config('app.frontend_url'), '/').'/cart');

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $firstProduct->id,
            'quantity' => 3,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $secondProduct->id,
            'quantity' => 3,
        ]);
        $this->assertSame(2, CartItem::where('user_id', $user->id)->count());

        // Opening a bundle link never reserves or decrements stock.
        $this->assertSame(10, $firstProduct->fresh()->stock);
        $this->assertSame(10, $secondProduct->fresh()->stock);
    }

    public function test_inactive_bundle_cannot_be_added_to_the_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $bundle = Bundle::factory()->create(['status' => false]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->withHeaders($this->userHeaders($user))
            ->postJson("/api/bundles/{$bundle->token}/add-to-cart")
            ->assertStatus(422)
            ->assertJsonValidationErrors('bundle');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_bundle_is_not_partially_added_when_a_product_is_out_of_stock(): void
    {
        $user = User::factory()->create();
        $availableProduct = $this->product(['stock' => 10]);
        $outOfStockProduct = $this->product(['stock' => 0]);

        $bundle = Bundle::factory()->create(['status' => true]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $availableProduct->id,
            'quantity' => 1,
        ]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $outOfStockProduct->id,
            'quantity' => 1,
        ]);

        $this->withHeaders($this->userHeaders($user))
            ->postJson("/api/bundles/{$bundle->token}/add-to-cart")
            ->assertStatus(422)
            ->assertJsonValidationErrors('bundle');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_bundle_is_not_partially_added_when_an_ebook_has_no_access_codes(): void
    {
        $user = User::factory()->create();
        $printedProduct = $this->product(['stock' => 10]);
        $ebook = $this->product([
            'is_e_copy' => true,
            'stock' => 0,
            'ebook_url' => 'https://example.com/book.pdf',
        ]);

        $bundle = Bundle::factory()->create(['status' => true]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $printedProduct->id,
            'quantity' => 1,
        ]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $ebook->id,
            'quantity' => 1,
        ]);

        $this->withHeaders($this->userHeaders($user))
            ->postJson("/api/bundles/{$bundle->token}/add-to-cart")
            ->assertStatus(422)
            ->assertJsonValidationErrors('bundle');

        $this->assertDatabaseCount('cart_items', 0);

        // With an available code the same bundle succeeds.
        EbookAccessCode::create([
            'product_id' => $ebook->id,
            'code' => 'BUNDLE-CODE-1',
        ]);

        $this->withHeaders($this->userHeaders($user))
            ->postJson("/api/bundles/{$bundle->token}/add-to-cart")
            ->assertOk();

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $ebook->id,
            'quantity' => 1,
        ]);
    }

    public function test_bundle_with_an_inactive_product_is_rejected_atomically(): void
    {
        $user = User::factory()->create();
        $activeProduct = $this->product(['stock' => 10]);
        $inactiveProduct = $this->product(['stock' => 10, 'status' => false]);

        $bundle = Bundle::factory()->create(['status' => true]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $activeProduct->id,
            'quantity' => 1,
        ]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $inactiveProduct->id,
            'quantity' => 1,
        ]);

        $this->withHeaders($this->userHeaders($user))
            ->postJson("/api/bundles/{$bundle->token}/add-to-cart")
            ->assertStatus(422)
            ->assertJsonValidationErrors('bundle');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_bundle_cannot_be_added_twice_beyond_stock_without_partial_writes(): void
    {
        $user = User::factory()->create();
        $firstProduct = $this->product(['stock' => 2]);
        $secondProduct = $this->product(['stock' => 2]);

        $bundle = Bundle::factory()->create(['status' => true]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $firstProduct->id,
            'quantity' => 1,
        ]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $secondProduct->id,
            'quantity' => 1,
        ]);

        $headers = $this->userHeaders($user);

        $this->withHeaders($headers)
            ->postJson("/api/bundles/{$bundle->token}/add-to-cart")
            ->assertOk();

        // The next two additions exceed available stock for both products.
        $this->withHeaders($headers)
            ->postJson("/api/bundles/{$bundle->token}/add-to-cart")
            ->assertOk();

        $this->withHeaders($headers)
            ->postJson("/api/bundles/{$bundle->token}/add-to-cart")
            ->assertStatus(422)
            ->assertJsonValidationErrors('bundle');

        $this->assertSame(2, CartItem::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $firstProduct->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $secondProduct->id,
            'quantity' => 2,
        ]);
    }

    public function test_add_to_cart_requires_an_authenticated_customer(): void
    {
        $product = $this->product();
        $bundle = Bundle::factory()->create(['status' => true]);
        BundleItem::factory()->create([
            'bundle_id' => $bundle->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->postJson("/api/bundles/{$bundle->token}/add-to-cart")
            ->assertUnauthorized();

        $this->assertDatabaseCount('cart_items', 0);
    }
}
