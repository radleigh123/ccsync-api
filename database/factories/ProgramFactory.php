<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $programs = [
            ['code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science'],
            ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology'],
            ['code' => 'BSIS', 'name' => 'Bachelor of Science in Information Systems'],
            ['code' => 'BSCE', 'name' => 'Bachelor of Science in Computer Engineering'],
        ];

        $program = fake()->randomElement($programs);

        return [
            'code' => $program['code'],
            'name' => $program['name'],
        ];
    }
}
