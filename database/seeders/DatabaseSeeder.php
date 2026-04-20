<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Type;
use App\Models\CurrentState;

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

        Type::create([
            'name' => 'Bug',
            'description' => 'A bug is a problem with the software that needs to be fixed.',
            'slug' => 'bug',
        ]);

        Type::create([
            'name' => 'Feature',
            'description' => 'A feature is a new feature that needs to be added to the software.',
            'slug' => 'feature',
        ]);

        Type::create([
            'name' => 'Task',
            'description' => 'A task is a small task that needs to be completed.',
            'slug' => 'task',
        ]);

        CurrentState::create([
            'name' => 'Open',
            'description' => 'A ticket that is open and needs to be resolved.',
            'slug' => 'open',
        ]);

        CurrentState::create([
            'name' => 'In Progress',
            'description' => 'A ticket that is in progress and needs to be completed.',
            'slug' => 'in-progress',
        ]);

        CurrentState::create([
            'name' => 'Closed',
            'description' => 'A ticket that is closed and no longer needs to be resolved.',
            'slug' => 'closed',
        ]);



        User::create([
            'name' => 'Paul Longo',
            'email' => 'paullongo@outlook.com',
            'password' => Hash::make('Password123'),
            'role_id' => Role::where('name', 'admin')->first()->id,
        ]);
    }
}
