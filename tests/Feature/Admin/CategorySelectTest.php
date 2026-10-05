<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Title;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategorySelectTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'is_super' => true,
            'is_active' => true,
        ]);

        $this->token = auth()->guard('api-admin')->login($admin);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];
    }

    private function createCategory(string $name, array $overrides = []): Category
    {
        $title = Title::first() ?? Title::create(['name_en' => 'Test', 'name_ar' => 'test']);

        return Category::create(array_merge([
            'title_id' => $title->id,
            'name_en' => $name,
            'name_ar' => $name.' ar',
            'slug' => str($name)->slug()->append(uniqid())->value(),
            'status' => true,
            'is_filter' => false,
        ], $overrides));
    }

    public function test_category_select_returns_all_categories_without_pagination(): void
    {
        $this->createCategory('Alpha');
        $this->createCategory('Beta', ['parent_id' => null]);

        $response = $this->getJson('/api/admin/categories/select', $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(2, 'data.categories')
            ->assertJsonStructure([
                'data' => [
                    'categories' => [['id', 'name_en', 'name_ar']],
                ],
            ]);

        $this->assertArrayNotHasKey('pagination', $response->json('data'));
    }

    public function test_category_select_excludes_soft_deleted_and_supports_filters(): void
    {
        $this->createCategory('Alpha');
        $filterCat = $this->createCategory('Beta', ['is_filter' => true]);
        $inactive = $this->createCategory('Gamma', ['status' => false]);
        $deleted = $this->createCategory('Delta');
        $deleted->delete();

        $response = $this->getJson('/api/admin/categories/select?is_filter=1', $this->authHeaders());

        $response->assertOk()->assertJsonCount(1, 'data.categories');
        $this->assertSame($filterCat->id, $response->json('data.categories.0.id'));

        $all = $this->getJson('/api/admin/categories/select', $this->authHeaders());
        $ids = collect($all->json('data.categories'))->pluck('id')->all();

        $this->assertNotContains($deleted->id, $ids);
        $this->assertContains($inactive->id, $ids);
        $this->assertContains($filterCat->id, $ids);
    }
}
