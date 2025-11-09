<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Event;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventDate = fake()->dateTimeBetween('now', '+6 months');
        $registrationStart = fake()->dateTimeBetween('-1 month', 'now');
        $registrationEnd = fake()->dateTimeBetween($registrationStart, $eventDate);

        $timeFromHour = fake()->numberBetween(8, 16); // 8 AM to 4 PM
        $timeFromMinute = fake()->randomElement(['00', '15', '30', '45']);
        $timeFrom = sprintf('%02d:%s', $timeFromHour, $timeFromMinute);

        $durationHours = fake()->numberBetween(2, 8); // Ensure time_to is always after time_from (2-8 hours later)
        $timeToHour = min(23, $timeFromHour + $durationHours); // Don't exceed 23:00
        $timeToMinute = fake()->randomElement(['00', '15', '30', '45']);
        $timeTo = sprintf('%02d:%s', $timeToHour, $timeToMinute);

        $semester = Semester::orderByDesc('id')->first();

        return [
            'name' => fake()->randomElement([
                'CCS Acquaintance Party',
                'Intramurals',
                'Tech Talk: Web Development',
                'Programming Contest',
                'Alumni Homecoming',
                'Career Fair',
                'Coding Bootcamp',
                'Game Development Workshop'
            ]),
            'description' => fake()->sentence(10),
            'venue' => fake()->randomElement([
                'Room 219',
                'Auditorium',
                'Computer Laboratory',
                'Gymnasium',
                'Conference Room A',
                'Online via Zoom'
            ]),
            'event_date' => $eventDate,
            'time_from' => $timeFrom,
            'time_to' => $timeTo,
            'registration_start' => $registrationStart,
            'registration_end' => $registrationEnd,
            'max_participants' => fake()->numberBetween(50, 500),
            'status' => fake()->randomElement(Status::cases()),
            'semester_id' => $semester->id,
        ];
    }
}
