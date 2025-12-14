<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles
        $adminRole = Role::firstOrCreate(
            ['role_name' => 'ADMIN'],
            ['id' => (string) Str::uuid()]
        );

        $userRole = Role::firstOrCreate(
            ['role_name' => 'CUSTOMER'],
            ['id' => (string) Str::uuid()]
        );

        // Seed users
        User::factory()->create([
            'id' => (string) Str::uuid(),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        User::factory()->create([
            'id' => (string) Str::uuid(),
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role_id' => $userRole->id,
        ]);

    }
}
