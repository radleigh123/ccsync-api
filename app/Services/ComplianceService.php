<?php

namespace App\Services;

use App\Models\Compliance;
use App\Models\ComplianceAudit;
use App\Models\Offering;

class ComplianceService
{
    public function getAll()
    {
        return Compliance::with(['offering', 'member', 'audits', 'documents'])->get();
    }

    public function create(array $data)
    {
        $comp = Compliance::where('offering_id', $data['offering_id'])
            ->where('member_id', $data['member_id'])
            ->first();

        $attempts = $comp->attempt;
        $maxSubmission = Offering::find($data['offering_id'])?->max_submissions;
        $notes = $data['notes'] ?? "";

        if ($comp !== null && $attempts >= $maxSubmission) {
            throw new \Exception("Maximum attempts reached. Contact a PSITS officer.", 400);
        }

        // If Compliance already exists, update only attempt or note
        if ($comp !== null) {
            $incrAttempt = $comp->attempt + 1;
            $comp->update([
                'attempt'   => $incrAttempt,
                'notes'     => $notes,
            ]);
            return $comp;
        }

        return Compliance::create($data);
    }

    public function find(string $id)
    {
        return Compliance::with(['offering', 'member', 'audits', 'documents'])->findOrFail($id);
    }

    public function update(string $id, array $data)
    {
        $comp = Compliance::findOrFail($id);
        $comp->update($data);

        $audit = ComplianceAudit::create([
            'compliance_id' => $comp->id,
            'new_status'    => $data['status'],
            'changed_by'    => $data['verified_by'],
        ]);

        return $comp->load(['audits']);
    }

    public function delete(string $id)
    {
        return Compliance::findOrFail($id)->delete();
    }
}
