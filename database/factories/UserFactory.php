<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
        $userName = fake()->unique()->userName();

        return [
            'display_name' => $userName,
            'email' => $this->generateEmail($userName),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'firebase_uid' => 'firebase_' . Str::random(10),
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

    /**
     * Generae a random email domain added to the username.
     * @param string $userName
     * @return string
     */
    private function generateEmail(string $userName): string
    {
        $domain = fake()->safeEmailDomain();
        $suffix = fake()->randomNumber(3);
        return "{$userName}{$suffix}@{$domain}";
    }
}
