<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->numerify('98########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => Role::STUDENT,
            'status' => User::STATUS_ACTIVE,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::ADMIN]);
    }

    public function guardian(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::GUARDIAN]);
    }

    public function schoolOfficer(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::SCHOOL_OFFICER]);
    }

    public function localOfficer(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::LOCAL_OFFICER]);
    }

    public function committee(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::COMMITTEE]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['status' => User::STATUS_INACTIVE]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
