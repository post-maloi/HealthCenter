<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@clinic.local'],
            [
                'first_name' => 'System',
                'middle_name' => null,
                'last_name' => 'Admin',
                'suffix' => null,
                'password' => 'admin12345',
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
