<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::findByName('SuperAdmin', 'api-admin');
        $managerRole = Role::findByName('Manager', 'api-admin');

        $admin = Admin::firstOrCreate([
            'email' => 'admin@prime.com'
        ], [
            'name' => 'System Super Admin',
            'password' => Hash::make('password123'),
            'is_super' => true,
        ]);

        $admin->assignRole($superAdminRole);

        $manager = Admin::firstOrCreate([
            'email' => 'manager@prime.com'
        ], [
            'name' => 'Store Manager',
            'password' => Hash::make('password123'),
        ]);

        $manager->assignRole($managerRole);
    }
}
