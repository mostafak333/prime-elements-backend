<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\EbookAccessCode;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class EbookAccessCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_and_list_access_codes_for_ebook(): void
    {
        $admin = Admin::factory()->create(['password' => Hash::make('password123')]);
        $token = JWTAuth::fromUser($admin);
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_e_copy' => true,
            'ebook_url' => 'https://example.com/book.pdf',
            'stock' => 0,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/admin/products/{$product->id}/access-codes", [
                'codes' => ['CODE-1', 'CODE-2'],
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('ebook_access_codes', 2);

        $list = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/admin/products/{$product->id}/access-codes");

        $list->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_duplicate_codes_not_allowed(): void
    {
        $admin = Admin::factory()->create(['password' => Hash::make('password123')]);
        $token = JWTAuth::fromUser($admin);
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_e_copy' => true,
            'ebook_url' => 'https://example.com/book.pdf',
        ]);

        EbookAccessCode::create([
            'product_id' => $product->id,
            'code' => 'DUP-1',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/admin/products/{$product->id}/access-codes", [
                'codes' => ['DUP-1'],
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }
}
