<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Program;
use App\Models\Events;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // PROGRAMS
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

        // ROLES, USERS, MEMBERS
        $this->call(UserMemberSeeder::class);

        // EVENTS
        $events = [
            [
                'name' => 'CCS Acquaintance Party',
                'description' => 'A casual gathering to get to know each other.',
                'venue' => 'Room 219',
                'event_date' => '2024-07-01',
                'time_from' => '10:00',
                'time_to' => '15:00',
                'registration_start' => '2024-06-01',
                'registration_end' => '2024-06-30',
                'max_participants' => 150,
                'status' => 'open'
            ],
            [
                'name' => 'Intramurals',
                'description' => 'A friendly sports competition between teams.',
                'venue' => 'Auditorium',
                'event_date' => '2024-07-05',
                'time_from' => '09:00',
                'time_to' => '17:00',
                'registration_start' => '2024-06-01',
                'registration_end' => '2024-07-04',
                'max_participants' => 300,
                'status' => 'open'
            ]
        ];

        // Register some random members to each event
        foreach ($events as $eventData) {
            $event = Events::create($eventData);
            $members = Member::inRandomOrder()->limit(rand(10, 50))->get();
            foreach ($members as $member) {
                $event->members()->attach($member->id, [
                    'registered_at' => now()->subDays(rand(1, 30))
                ]);
            }
        }

        Events::factory(8)->create()->each(function ($event) {
            $memberCount = rand(5, min(30, $event->max_participants));
            $members = Member::inRandomOrder()->limit($memberCount)->get();

            foreach ($members as $member) {
                $event->members()->attach($member->id, [
                    'registered_at' => fake()->dateTimeBetween($event->registration_start, 'now')
                ]);
            }
        });
    }
}
