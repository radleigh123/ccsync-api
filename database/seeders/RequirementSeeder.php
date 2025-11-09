<?php

namespace Database\Seeders;

use App\Models\Offering;
use App\Models\Requirement;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RequirementSeeder extends Seeder
{
    public function run(): void
    {
        $semester = Semester::orderByDesc('id')->first();

        $requirements = [
            [
                'name' => 'Clearance form',
                'description' => 'Submit clearance document',
                'type' => 'document',
            ],
            [
                'name' => 'Payment receipt',
                'description' => 'Upload your official payment receipt',
                'type' => 'document',
            ],
            [
                'name' => 'Intramurals confetti',
                'description' => '',
                'type' => 'other',
                'is_active' => false,
            ],
        ];

        $offerings = [
            [
                'open_at' => Carbon::parse('2025-10-15'),
                'close_at' => Carbon::parse('2025-12-15'),
                'max_submissions' => 5,
            ],
            [
                'open_at' => Carbon::parse('2025-11-15'),
                'close_at' => Carbon::parse('2025-11-30'),
                'max_submissions' => 5,
            ],
            [
                'open_at' => Carbon::parse('2025-09-15'),
                'close_at' => Carbon::parse('2025-10-30'),
                'max_submissions' => 3,
                'active' => false,
            ]
        ];

        for ($i = 0; $i < count($requirements); $i++) {
            $req = $requirements[$i];
            $off = $offerings[$i];

            $reqCreated = Requirement::create([
                'semester_id' => $semester->id,
                'name' => $req['name'],
                'description' => $req['description'],
                'type' => $req['type'],
                'is_active' => $i === (count($requirements) - 1) ? false : true,
            ]);
            Offering::create([
                'requirement_id' => $reqCreated->id,
                'semester_id' => $semester->id,
                'open_at' => $off['open_at'],
                'close_at' => $off['close_at'],
                'max_submissions' => $off['max_submissions'],
                'active' => $i === (count($offerings) - 1) ? false : true,
            ]);
        }
    }
}
