<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\LandingBanner;
use App\Models\Product;
use App\Models\Title;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'is_super' => true,
            'is_active' => true,
        ]);

        $this->token = auth()->guard('api-admin')->login($this->admin);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];
    }

    // =========================================================================
    // ADMIN AVATAR
    // =========================================================================

    public function test_admin_can_upload_avatar_on_create(): void
    {
        $role = Admin::withoutTimestamps(fn () => Role::create(['name' => 'Admin', 'guard_name' => 'api-admin']));

        $avatar = UploadedFile::fake()->image('avatar.jpg', 200, 200)->size(100);

        $response = $this->postJson('/api/admin/admins', [
            'name' => 'New Admin',
            'email' => 'new@test.com',
            'phone' => '+1234567890',
            'is_super' => false,
            'roles' => 'Admin',
            'avatar' => $avatar,
        ], $this->authHeaders());

        $response->assertCreated();

        $admin = Admin::where('email', 'new@test.com')->first();
        $this->assertNotNull($admin->avatar);
        $this->assertStringStartsWith('admins/', $admin->avatar);
        Storage::disk('public')->assertExists($admin->avatar);
    }

    public function test_admin_can_replace_avatar_on_update(): void
    {
        $oldAvatar = UploadedFile::fake()->image('old.jpg', 200, 200)->size(100);
        $oldPath = $oldAvatar->store('admins', 'public');

        $this->admin->update(['avatar' => $oldPath]);

        $newAvatar = UploadedFile::fake()->image('new.jpg', 200, 200)->size(100);

        $response = $this->putJson("/api/admin/admins/{$this->admin->id}", [
            'avatar' => $newAvatar,
        ], $this->authHeaders());

        $response->assertOk();

        $this->admin->refresh();
        $this->assertStringStartsWith('admins/', $this->admin->avatar);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($this->admin->avatar);
    }

    // =========================================================================
    // PRODUCT IMAGES
    // =========================================================================

    public function test_admin_can_upload_product_images(): void
    {
        $title = Title::create(['name_en' => 'Test', 'name_ar' => 'test']);
        $category = Category::create([
            'title_id' => $title->id,
            'name_en' => 'Test',
            'name_ar' => 'test',
            'slug' => 'test',
            'status' => true,
            'is_filter' => false,
        ]);

        $images = [
            UploadedFile::fake()->image('prod1.jpg', 800, 600)->size(200),
            UploadedFile::fake()->image('prod2.jpg', 800, 600)->size(200),
        ];

        $response = $this->postJson('/api/admin/products', [
            'category_id' => $category->id,
            'name_en' => 'Test Product',
            'name_ar' => 'منتج تجريبي',
            'short_description_en' => 'Short desc',
            'short_description_ar' => 'وصف قصير',
            'price' => 29.99,
            'stock' => 10,
            'status' => true,
            'is_new_arrival' => false,
            'is_best_seller' => false,
            'is_e_copy' => false,
            'publisher' => 'Test Publisher',
            'images' => $images,
        ], $this->authHeaders());

        $response->assertCreated();

        $product = Product::where('name_en', 'Test Product')->first();
        $this->assertCount(2, $product->images);

        foreach ($product->images as $productImage) {
            $this->assertStringStartsWith('products/', $productImage->image_path);
            Storage::disk('public')->assertExists($productImage->image_path);
        }
    }

    public function test_admin_can_replace_product_images(): void
    {
        $title = Title::create(['name_en' => 'Test', 'name_ar' => 'test']);
        $category = Category::create([
            'title_id' => $title->id,
            'name_en' => 'Test',
            'name_ar' => 'test',
            'slug' => 'test',
            'status' => true,
            'is_filter' => false,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name_en' => 'Product',
            'name_ar' => 'منتج',
            'price' => 10,
            'discount' => 0,
            'stock' => 5,
            'status' => true,
            'is_new_arrival' => false,
            'is_best_seller' => false,
            'is_e_copy' => false,
            'publisher' => 'Pub',
        ]);

        $oldImage = UploadedFile::fake()->image('old.jpg', 800, 600)->size(200);
        $oldPath = $oldImage->store('products', 'public');
        $product->images()->create(['image_path' => $oldPath]);

        $newImages = [
            UploadedFile::fake()->image('new1.jpg', 800, 600)->size(200),
        ];

        $response = $this->putJson("/api/admin/products/{$product->id}", [
            'images' => $newImages,
        ], $this->authHeaders());

        $response->assertOk();

        $product->refresh()->load('images');
        $this->assertCount(1, $product->images);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_product_images_deleted_with_product(): void
    {
        $title = Title::create(['name_en' => 'Test', 'name_ar' => 'test']);
        $category = Category::create([
            'title_id' => $title->id,
            'name_en' => 'Test',
            'name_ar' => 'test',
            'slug' => 'test',
            'status' => true,
            'is_filter' => false,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name_en' => 'Product',
            'name_ar' => 'منتج',
            'price' => 10,
            'discount' => 0,
            'stock' => 5,
            'status' => true,
            'is_new_arrival' => false,
            'is_best_seller' => false,
            'is_e_copy' => false,
            'publisher' => 'Pub',
        ]);

        $image = UploadedFile::fake()->image('prod.jpg', 800, 600)->size(200);
        $path = $image->store('products', 'public');
        $product->images()->create(['image_path' => $path]);

        $response = $this->deleteJson("/api/admin/products/{$product->id}", [], $this->authHeaders());

        $response->assertOk();
        Storage::disk('public')->assertMissing($path);
    }

    // =========================================================================
    // CATEGORY IMAGE
    // =========================================================================

    public function test_admin_can_upload_category_image(): void
    {
        $title = Title::create(['name_en' => 'Test', 'name_ar' => 'test']);
        $image = UploadedFile::fake()->image('category.jpg', 400, 400)->size(100);

        $response = $this->postJson('/api/admin/categories', [
            'title_id' => $title->id,
            'name_en' => 'New Category',
            'name_ar' => 'فئة جديدة',
            'slug' => 'new-category',
            'status' => true,
            'is_filter' => false,
            'image' => $image,
        ], $this->authHeaders());

        $response->assertCreated();

        $category = Category::where('slug', 'new-category')->first();
        $this->assertNotNull($category->image);
        $this->assertStringStartsWith('categories/', $category->image);
        Storage::disk('public')->assertExists($category->image);
    }

    public function test_admin_can_replace_category_image(): void
    {
        $title = Title::create(['name_en' => 'Test', 'name_ar' => 'test']);
        $oldImage = UploadedFile::fake()->image('old.jpg', 400, 400)->size(100);
        $oldPath = $oldImage->store('categories', 'public');

        $category = Category::create([
            'title_id' => $title->id,
            'name_en' => 'Cat',
            'name_ar' => 'cat',
            'slug' => 'cat',
            'image' => $oldPath,
            'status' => true,
            'is_filter' => false,
        ]);

        $newImage = UploadedFile::fake()->image('new.jpg', 400, 400)->size(100);

        $response = $this->putJson("/api/admin/categories/{$category->id}", [
            'title_id' => $title->id,
            'is_filter' => false,
            'image' => $newImage,
        ], $this->authHeaders());

        $response->assertOk();

        $category->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($category->image);
    }

    public function test_category_image_deleted_with_category(): void
    {
        $title = Title::create(['name_en' => 'Test', 'name_ar' => 'test']);
        $image = UploadedFile::fake()->image('cat.jpg', 400, 400)->size(100);
        $path = $image->store('categories', 'public');

        $category = Category::create([
            'title_id' => $title->id,
            'name_en' => 'Cat',
            'name_ar' => 'cat',
            'slug' => 'cat',
            'image' => $path,
            'status' => true,
            'is_filter' => false,
        ]);

        $response = $this->deleteJson("/api/admin/categories/{$category->id}", [], $this->authHeaders());

        $response->assertOk();
        Storage::disk('public')->assertMissing($path);
    }

    // =========================================================================
    // BANNER IMAGE
    // =========================================================================

    public function test_admin_can_upload_banner_image(): void
    {
        $image = UploadedFile::fake()->image('banner.jpg', 1200, 600)->size(300);

        $response = $this->postJson('/api/admin/landing-banners', [
            'title_en' => 'Sale Banner',
            'title_ar' => 'بانر تخفيضات',
            'image' => $image,
            'status' => true,
            'sort_order' => 0,
        ], $this->authHeaders());

        $response->assertCreated();

        $banner = LandingBanner::where('title_en', 'Sale Banner')->first();
        $this->assertNotNull($banner->image);
        $this->assertStringStartsWith('banners/', $banner->image);
        Storage::disk('public')->assertExists($banner->image);
    }

    public function test_admin_can_replace_banner_image(): void
    {
        $oldImage = UploadedFile::fake()->image('old.jpg', 1200, 600)->size(300);
        $oldPath = $oldImage->store('banners', 'public');

        $banner = LandingBanner::create([
            'title_en' => 'Banner',
            'title_ar' => 'بانر',
            'image' => $oldPath,
            'status' => true,
            'sort_order' => 0,
        ]);

        $newImage = UploadedFile::fake()->image('new.jpg', 1200, 600)->size(300);

        $response = $this->putJson("/api/admin/landing-banners/{$banner->id}", [
            'image' => $newImage,
        ], $this->authHeaders());

        $response->assertOk();

        $banner->refresh();
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($banner->image);
    }

    // =========================================================================
    // VALIDATION
    // =========================================================================

    public function test_invalid_image_type_rejected(): void
    {
        $file = UploadedFile::fake()->create('document.exe', 100, 'application/x-msdownload');

        $response = $this->postJson('/api/admin/landing-banners', [
            'title_en' => 'Test',
            'image' => $file,
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('image');
    }

    public function test_oversized_image_rejected(): void
    {
        $file = UploadedFile::fake()->image('large.jpg', 200, 200)->size(6000);

        $response = $this->postJson('/api/admin/landing-banners', [
            'title_en' => 'Test',
            'image' => $file,
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('image');
    }

    public function test_product_image_validation_rejects_invalid_type(): void
    {
        $title = Title::create(['name_en' => 'Test', 'name_ar' => 'test']);
        $category = Category::create([
            'title_id' => $title->id,
            'name_en' => 'Test',
            'name_ar' => 'test',
            'slug' => 'test',
            'status' => true,
            'is_filter' => false,
        ]);

        $file = UploadedFile::fake()->create('script.php', 100, 'application/x-php');

        $response = $this->postJson('/api/admin/products', [
            'category_id' => $category->id,
            'name_en' => 'Product',
            'name_ar' => 'منتج',
            'short_description_en' => 'Desc',
            'short_description_ar' => 'وصف',
            'price' => 10,
            'stock' => 5,
            'status' => true,
            'is_new_arrival' => false,
            'is_best_seller' => false,
            'is_e_copy' => false,
            'publisher' => 'Pub',
            'images' => [$file],
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('images.0');
    }

    // =========================================================================
    // URL GENERATION
    // =========================================================================

    public function test_product_image_url_generated_in_resource(): void
    {
        $title = Title::create(['name_en' => 'Test', 'name_ar' => 'test']);
        $category = Category::create([
            'title_id' => $title->id,
            'name_en' => 'Test',
            'name_ar' => 'test',
            'slug' => 'test',
            'status' => true,
            'is_filter' => false,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name_en' => 'Product',
            'name_ar' => 'منتج',
            'price' => 10,
            'discount' => 0,
            'stock' => 5,
            'status' => true,
            'is_new_arrival' => false,
            'is_best_seller' => false,
            'is_e_copy' => false,
            'publisher' => 'Pub',
        ]);

        $image = UploadedFile::fake()->image('prod.jpg', 800, 600)->size(200);
        $path = $image->store('products', 'public');
        $product->images()->create(['image_path' => $path]);

        $response = $this->getJson("/api/admin/products/{$product->id}", $this->authHeaders());

        $response->assertOk();

        $imageData = $response->json('data.images.0');
        $this->assertArrayHasKey('image_url', $imageData);
        $this->assertStringContainsString('/storage/products/', $imageData['image_url']);
    }
}
