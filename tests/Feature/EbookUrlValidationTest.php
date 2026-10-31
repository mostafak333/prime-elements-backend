<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class EbookUrlValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ebook_requires_url(): void
    {
        $admin = Admin::factory()->create(['password' => Hash::make('password123')]);
        $token = JWTAuth::fromUser($admin);
        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/products', [
                'category_id' => $category->id,
                'name_en' => 'Book',
                'name_ar' => 'كتاب',
                'short_description_en' => 'desc',
                'short_description_ar' => 'وصف',
                'price' => 10,
                'stock' => 5,
                'status' => true,
                'is_new_arrival' => false,
                'is_best_seller' => false,
                'is_e_copy' => true,
                'publisher' => 'Pub',
            ]);

        $response->assertStatus(422);
    }

    public function test_physical_book_does_not_require_ebook_url(): void
    {
        $admin = Admin::factory()->create(['password' => Hash::make('password123')]);
        $token = JWTAuth::fromUser($admin);
        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/products', [
                'category_id' => $category->id,
                'name_en' => 'Book',
                'name_ar' => 'كتاب',
                'short_description_en' => 'desc',
                'short_description_ar' => 'وصف',
                'price' => 10,
                'stock' => 5,
                'status' => true,
                'is_new_arrival' => false,
                'is_best_seller' => false,
                'is_e_copy' => false,
                'publisher' => 'Pub',
            ]);

        $response->assertStatus(201);
    }
}
