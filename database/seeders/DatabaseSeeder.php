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
        // Seed roles first, then users (users need roles to exist)
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        // Optionally create additional random users
        // User::factory(10)->create();
    }
}
