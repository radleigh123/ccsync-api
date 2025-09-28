<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Program;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::factory()->create([
            'name' => 'ADMIN',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Program seed
        $programs = [
            ['code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science'],
            ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology'],
            ['code' => 'BSIS', 'name' => 'Bachelor of Science in Information Systems'],
            ['code' => 'BSCE', 'name' => 'Bachelor of Science in Computer Engineering'],
        ];

        foreach ($programs as $program) {
            Program::firstOrCreate(
                ['code' => $program['code']],
                ['name' => $program['name']]
            );
        }

        // Member seed
        Member::factory(300)->create();
    }
}
