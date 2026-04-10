<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'admin',
            'description' => 'Administrator',
        ]);

        Role::create([
            'name' => 'user',
            'description' => 'User',
        ]);

        User::factory()->create([
            'name' => 'Paul Longo',
            'email' => 'paullongo@outlook.com',
            'password' => Hash::make('Password123'),
            'role_id' => Role::where('name', 'admin')->first()->id,
        ]);
    }
}
