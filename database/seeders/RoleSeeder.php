<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'api-admin']);
        $manager    = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'api-admin']);
        $staff      = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'api-admin']);

        $customer   = Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'api-user']);
        $vip        = Role::firstOrCreate(['name' => 'VIPCustomer', 'guard_name' => 'api-user']);

        // Assign permissions
        $manager->givePermissionTo([
            'view_users','edit_users',
            'view_categories','create_categories','edit_categories',
            'view_items','create_items','edit_items',
            'view_orders','update_order_status','assign_shipping','track_shipments',
            'view_reviews','delete_reviews',
        ]);

        $staff->givePermissionTo([
            'view_items','view_orders','update_order_status','track_shipments',
        ]);

        $customer->givePermissionTo([
            'browse_store','manage_cart','manage_wishlist','place_orders','write_reviews'
        ]);

        $vip->givePermissionTo([
            'browse_store','manage_cart','manage_wishlist','place_orders','write_reviews',
            'view_vip_discounts','access_vip_products','get_priority_support'
        ]);
    }
}
