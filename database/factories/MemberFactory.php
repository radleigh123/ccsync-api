<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $programCodes = ['BSCS', 'BSIT', 'BSIS', 'BSCE'];

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'suffix' => fake()->optional(0.2)->randomElement(['Jr.', 'Sr.', 'III', 'IV', 'V']),
            'id_school_number' => $this->generateSchoolNumber(),
            'email' => fake()->unique()->safeEmail(),
            'birth_date' => fake()->dateTimeBetween('-30 years', '-15 years')->format('Y-m-d'),
            'enrollment_date' => fake()->dateTimeBetween('-4 years', 'now')->format('Y-m-d'),
            'program' => fake()->randomElement($programCodes),
            'year' => fake()->numberBetween(1, 4),
            'is_paid' => fake()->boolean(70), // 70% chance of being paid
            'gender' => fake()->randomElement(Gender::cases()),
            'biography' => fake()->sentence(),
            'phone' => fake()->phoneNumber(),
        ];
    }

    /**
     * Generate a random 8-digit school number that starts with 19, 20, or 21.
     * @return string
     */
    private function generateSchoolNumber(): string
    {
        $prefix = fake()->randomElement(['19', '20', '21']);
        $suffix = fake()->numerify('######'); // 6 random digits
        return "{$prefix}{$suffix}";
    }
}
