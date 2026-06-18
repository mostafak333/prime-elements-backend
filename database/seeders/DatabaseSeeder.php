<?php

namespace Database\Seeders;

use Database\Seeders\AdminSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call your custom role and permissions seeder first
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AdminSeeder::class,
            UserSeeder::class,
        ]);
    }
}
