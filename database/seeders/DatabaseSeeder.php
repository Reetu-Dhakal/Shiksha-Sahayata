<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * ALL DATA CREATED BY THIS SEEDER IS FICTIONAL DEMO DATA.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@shikshasahayata.np'],
            [
                'name' => 'System Administrator',
                'phone' => '9800000001',
                'password' => 'password',
                'role' => Role::ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );
    }
}
