<?php

namespace Database\Factories;

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
            'UserID' => (string) \Illuminate\Support\Str::uuid(),
            'Username' => 'user_' . \Illuminate\Support\Str::random(8),
            'Password' => static::$password ??= \Illuminate\Support\Facades\Hash::make('password'),
            'FullName' => 'Test User ' . \Illuminate\Support\Str::random(4),
            'Role' => 'นักเรียน',
            'Status' => 'ปกติ',
            'remember_token' => \Illuminate\Support\Str::random(10),
        ];
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
