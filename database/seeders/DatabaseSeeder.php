<?php

namespace Database\Seeders;

use App\Models\User;
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
        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'Test',
            'middle_name' => null,
            'last_name' => 'User',
            'suffix' => null,
            'email' => 'test@example.com',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
