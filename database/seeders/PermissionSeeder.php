<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ---------------- ADMIN ----------------
        $adminPermissions = [
            'view_admins','create_admins','edit_admins','delete_admins','active_admins','inactive_admins',

            'view_admin_roles','create_admin_roles','edit_admin_roles','delete_admin_roles','assign_admin_roles',
            'view_user_roles','create_user_roles','edit_user_roles','delete_user_roles','assign_user_roles',

            'view_users','edit_users','ban_users','unban_users',

            'view_settings','create_settings','edit_settings','delete_settings',

            'view_categories','create_categories','edit_categories','delete_categories','active_categories','inactive_categories',

            'view_sub_categories','create_sub_categories','edit_sub_categories','delete_sub_categories','active_sub_categories','inactive_sub_categories',

            'view_items','create_items','edit_items','delete_items','active_items','inactive_items',

            'view_orders','update_order_status','assign_shipping','track_shipments',

            'view_reviews','delete_reviews',

            'view_payment_methods','create_payment_methods','edit_payment_methods','delete_payment_methods','notifications',
        ];

        foreach ($adminPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api-admin'
            ]);
        }

        // ---------------- USERS ----------------
        $userPermissions = [
            'browse_store','manage_cart','manage_wishlist','place_orders','write_reviews',
            'view_vip_discounts','access_vip_products','get_priority_support'
        ];

        foreach ($userPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api-user'
            ]);
        }
    }
}
