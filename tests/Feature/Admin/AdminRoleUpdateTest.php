<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminRoleUpdateTest extends TestCase
{
    use RefreshDatabase;

    private Admin $superAdmin;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Manager', 'guard_name' => 'api-admin']);
        Role::create(['name' => 'SuperAdmin', 'guard_name' => 'api-admin']);
        Role::create(['name' => 'Staff', 'guard_name' => 'api-admin']);

        $this->superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => Hash::make('password'),
            'is_super' => true,
            'is_active' => true,
        ]);

        $this->token = auth()->guard('api-admin')->login($this->superAdmin);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];
    }

    private function createManagerAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Store Manager',
            'email' => 'manager@test.com',
            'password' => Hash::make('password'),
            'is_super' => false,
            'is_active' => true,
        ]);

        $admin->assignRole('Manager');

        return $admin;
    }

    // =========================================================================
    // REQUEST VALIDATION
    // =========================================================================

    public function test_roles_accepts_single_string(): void
    {
        $target = $this->createManagerAdmin();

        $response = $this->putJson("/api/admin/admins/{$target->id}", [
            'roles' => 'SuperAdmin',
        ], $this->authHeaders());

        $response->assertOk();

        $target->refresh();
        $this->assertTrue($target->hasRole('SuperAdmin'));
        $this->assertFalse($target->hasRole('Manager'));
    }

    public function test_roles_accepts_array(): void
    {
        $target = $this->createManagerAdmin();

        $response = $this->putJson("/api/admin/admins/{$target->id}", [
            'roles' => ['SuperAdmin'],
        ], $this->authHeaders());

        $response->assertOk();

        $this->assertTrue($target->fresh()->hasRole('SuperAdmin'));
    }

    public function test_roles_accepts_multiple_roles(): void
    {
        $target = $this->createManagerAdmin();

        $response = $this->putJson("/api/admin/admins/{$target->id}", [
            'roles' => ['SuperAdmin', 'Staff'],
        ], $this->authHeaders());

        $response->assertOk();

        $this->assertTrue($target->fresh()->hasAllRoles(['SuperAdmin', 'Staff']));
        $this->assertFalse($target->fresh()->hasRole('Manager'));
    }

    public function test_invalid_role_name_is_rejected(): void
    {
        $target = $this->createManagerAdmin();

        $response = $this->putJson("/api/admin/admins/{$target->id}", [
            'roles' => 'NonExistentRole',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('roles');
    }

    // =========================================================================
    // AUTHORIZATION
    // =========================================================================

    public function test_non_super_admin_cannot_update_roles(): void
    {
        $nonSuper = Admin::create([
            'name' => 'Regular Admin',
            'email' => 'regular@test.com',
            'password' => Hash::make('password'),
            'is_super' => false,
            'is_active' => true,
        ]);

        $nonSuperToken = auth()->guard('api-admin')->login($nonSuper);

        $target = $this->createManagerAdmin();

        $response = $this->putJson("/api/admin/admins/{$target->id}", [
            'roles' => 'SuperAdmin',
        ], [
            'Authorization' => 'Bearer '.$nonSuperToken,
            'Accept' => 'application/json',
        ]);

        $response->assertForbidden();

        $this->assertTrue($target->fresh()->hasRole('Manager'));
    }

    // =========================================================================
    // DEDICATED ROLES SYNC ENDPOINT
    // =========================================================================

    public function test_roles_sync_endpoint_accepts_single_string(): void
    {
        $target = $this->createManagerAdmin();

        $response = $this->putJson("/api/admin/admins/{$target->id}/roles", [
            'roles' => 'SuperAdmin',
        ], $this->authHeaders());

        $response->assertOk();

        $this->assertTrue($target->fresh()->hasRole('SuperAdmin'));
        $this->assertFalse($target->fresh()->hasRole('Manager'));
    }

    public function test_roles_sync_endpoint_accepts_array(): void
    {
        $target = $this->createManagerAdmin();

        $response = $this->putJson("/api/admin/admins/{$target->id}/roles", [
            'roles' => ['SuperAdmin'],
        ], $this->authHeaders());

        $response->assertOk();

        $this->assertTrue($target->fresh()->hasRole('SuperAdmin'));
    }
}
