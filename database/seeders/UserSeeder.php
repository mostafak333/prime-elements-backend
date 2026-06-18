<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $customerRole = Role::findByName('Customer', 'api-user');

        $user = User::firstOrCreate([
            'email' => 'customer@prime.com'
        ], [
            'name' => 'John Doe',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($customerRole);
    }
}
