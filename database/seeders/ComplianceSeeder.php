<?php

namespace Database\Seeders;

use App\Enums\RequirementStatus;
use App\Models\Compliance;
use App\Models\ComplianceAudit;
use App\Models\ComplianceDocument;
use App\Models\Member;
use App\Models\Offering;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComplianceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $member = Member::inRandomOrder()->first();
        $complianceId = null;

        foreach (Offering::where('active', '=', true)->get() as $offer) {
            // NOTE: Simple Eloquent INSERT
            $compliance = new Compliance;
            $compliance->offering_id = $offer->id;
            $compliance->member_id = $member->id;
            $compliance->save();
            /* $compliance = Compliance::create([
                'offering_id' => $offer->id,
                'member_id' => $member->id,
            ]); */

            ComplianceDocument::create([
                'compliance_id' => $compliance->id,
                'file_path' => 'root/user/document/',
                'file_name' => 'Payment receipt',
                'mime' => 'image/jpeg',
            ]);
        }

        // Imitation of reviewing member's submission
        $user = User::role('officer')->first();
        $officer = Member::findOrFail($user->id);

        $audit = ComplianceAudit::create([
            'compliance_id' => $compliance->id,
            'new_status' => RequirementStatus::APPROVED,
            'changed_by' => $officer->id,
        ]);
        $compliance->update([
            'verified_at' => $audit->created_at,
            'verified_by' => $audit->changed_by,
            'status' => RequirementStatus::APPROVED,
        ]);
    }
}
