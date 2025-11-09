<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        $terms = ['1st', '2nd', 'Summer'];
        $startYear = 2023;

        for ($i = 0; $i < 10; $i++) {
            $term = $terms[$i % 3];
            $year = $startYear + intdiv($i, 3);

            switch ($term) {
                case '1st':
                    $dateStart = "{$year}-08-01";
                    $dateEnd = date('Y-m-d', strtotime("+4 months", strtotime($dateStart)));
                    break;
                case '2nd':
                    $dateStart = "{$year}-01-20";
                    $dateEnd = date('Y-m-d', strtotime("+4 months", strtotime($dateStart)));
                    break;
                case 'Summer':
                    $dateStart = "{$year}-06-01";
                    $dateEnd = date('Y-m-d', strtotime("+2 months", strtotime($dateStart)));
                    break;
            }

            if ($i == 9) {
                Semester::firstOrCreate([
                    'title' => "{$term} Semester S.Y. {$year} - " . ($year + 1),
                    'date_start' => $dateStart,
                    'date_end' => $dateEnd,
                ]);
                break;
            }
            Semester::firstOrCreate([
                'title' => "{$term} Semester S.Y. {$year} - " . ($year + 1),
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
                'status' => Status::CLOSED
            ]);
        }
    }
}
