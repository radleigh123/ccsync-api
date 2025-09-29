<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Events>
 */
class EventsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventDate = $this->faker->dateTimeBetween('now', '+6 months');
        $registrationStart = $this->faker->dateTimeBetween('-1 month', 'now');
        $registrationEnd = $this->faker->dateTimeBetween($registrationStart, $eventDate);

        // Generate logical event times
        $timeFromHour = $this->faker->numberBetween(8, 16); // 8 AM to 4 PM
        $timeFromMinute = $this->faker->randomElement(['00', '15', '30', '45']);
        $timeFrom = sprintf('%02d:%s', $timeFromHour, $timeFromMinute);

        // Ensure time_to is always after time_from (2-8 hours later)
        $durationHours = $this->faker->numberBetween(2, 8);
        $timeToHour = min(23, $timeFromHour + $durationHours); // Don't exceed 23:00
        $timeToMinute = $this->faker->randomElement(['00', '15', '30', '45']);
        $timeTo = sprintf('%02d:%s', $timeToHour, $timeToMinute);

        return [
            'name' => $this->faker->randomElement([
                'CCS Acquaintance Party',
                'Intramurals',
                'Tech Talk: Web Development',
                'Programming Contest',
                'Alumni Homecoming',
                'Career Fair',
                'Coding Bootcamp',
                'Game Development Workshop'
            ]),
            'description' => $this->faker->sentence(10),
            'venue' => $this->faker->randomElement([
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
            'max_participants' => $this->faker->numberBetween(50, 500),
            'status' => $this->faker->randomElement(['open', 'closed', 'cancelled'])
        ];
    }

    private function randomTime($isStart)
    {
        if ($isStart) {
            return $this->faker->time('H:i', '10:00');
        } else {
            return $this->faker->time('H:i', '17:00');
        }
    }
}
