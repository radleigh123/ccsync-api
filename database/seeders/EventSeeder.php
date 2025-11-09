<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Event;
use App\Models\Member;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $semester = Semester::orderByDesc('id')->first();
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
                'status' => Status::OPEN,
                'semester_id' => $semester->id,
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
                'status' => Status::OPEN,
                'semester_id' => $semester->id,
            ]
        ];

        // Register some random members to each event
        foreach ($events as $eventData) {
            $event = Event::create($eventData);
            $members = Member::inRandomOrder()->limit(rand(30, 100))->get();
            foreach ($members as $member) {
                /* $event->members()->attach($member->id, [
                    'registered_at' => now()->subDays(rand(1, 30))
                ]); */
                $event->members()->syncWithoutDetaching([
                    $member->id => ['registered_at' => now()->subDays(rand(1, 30))]
                ]);
            }
        }

        Event::factory(8)->create()->each(function ($event) {
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
