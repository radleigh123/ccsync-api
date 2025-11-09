<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Semester;

/**
 * @extends Factory<Semester>
 */
class SemesterFactory extends Factory
{
    protected static int $counter = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $terms = ['First', 'Second', 'Summer'];
        $termIndex = self::$counter % count($terms);
        $term = $terms[$termIndex];

        $baseYear = 2023 + intdiv(self::$counter, count($terms));
        $dateStart = "{$baseYear}-06-01";
        $dateEnd = date('Y-m-d', strtotime("+5 months", strtotime($dateStart)));

        self::$counter++;

        return [
            'title' => $this->generateSemesterTitle($term, $dateStart),
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
        ];
    }

    private function generateSemesterTitle(string $term, string $year): string
    {
        return "{$term} Semester S.Y. {$year} - " . ($year + 1);
    }
}
