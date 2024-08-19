<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'name' => 'Admin User',
            'lastname' => 'Adminowski',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_hr' => 1,
            'isActive' => 1,
            'is_deleted' => 0,
        ]);


        User::factory()->create([
            'name' => 'Test User',
            'lastname' => 'Testowy',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'is_hr' => 0,
            'isActive' => 1,
            'is_deleted' => 0,
        ]);
    }
}
