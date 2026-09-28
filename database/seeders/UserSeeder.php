<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\LocalEducationUnit;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * FICTIONAL DEMO ACCOUNTS — password for every demo account is "password".
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $sagarmatha = School::query()->where('school_code', 'SCH-KAV-001')->firstOrFail();
        $kavreLeu = LocalEducationUnit::query()
            ->where('name', 'Kavrepalanchok Local Education Unit')
            ->firstOrFail();

        User::query()->firstOrCreate(
            ['email' => 'school.officer@sagarmatha.edu.np'],
            [
                'name' => 'Sagarmatha School Officer',
                'phone' => '9800000301',
                'password' => 'password',
                'role' => Role::SCHOOL_OFFICER,
                'status' => User::STATUS_ACTIVE,
                'school_id' => $sagarmatha->id,
                'email_verified_at' => now(),
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'local.officer@kavre.gov.np'],
            [
                'name' => 'Kavrepalanchok Local Education Officer',
                'phone' => '9800000302',
                'password' => 'password',
                'role' => Role::LOCAL_OFFICER,
                'status' => User::STATUS_ACTIVE,
                'local_education_unit_id' => $kavreLeu->id,
                'email_verified_at' => now(),
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'committee@shikshasahayata.np'],
            [
                'name' => 'Selection Committee Member',
                'phone' => '9800000303',
                'password' => 'password',
                'role' => Role::COMMITTEE,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'aasha.student@mail.com'],
            [
                'name' => 'Aasha Student',
                'phone' => '9800000304',
                'password' => 'password',
                'role' => Role::STUDENT,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'guardian.aasha@mail.com'],
            [
                'name' => 'Aasha Guardian',
                'phone' => '9800000305',
                'password' => 'password',
                'role' => Role::GUARDIAN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ]
        );
    }
}
