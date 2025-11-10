<?php

namespace App\Http\Controllers\Requirement;

use App\Enums\RequirementStatus;
use App\Http\Controllers\Controller;
use App\Models\Compliance;
use App\Models\ComplianceAudit;
use App\Models\ComplianceDocument;
use App\Models\Offering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class ComplianceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(['compliances' => Compliance::all()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'offering_id' => 'required|integer',
            'member_id' => 'required|integer',
            'note' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 422);
        }

        $offering_id = $request->offering_id;
        $member_id = $request->member_id;
        $notes = $request?->notes;

        $existing = Compliance::where('offering_id', $offering_id)
            ->where('member_id', $member_id)
            ->first();
        $max = Offering::find($offering_id)?->max_submissions ?? 1;

        if ($existing && $existing->attempt >= $max) {
            return response()->json([
                'message' => "Maximum attempts reached. Contact a PSITS officer.",
            ], 500);
        }

        if ($existing) {
            $existing->update([
                'attempt' => $existing ? $existing->attempt + 1 : 1,
                'notes' => $notes,
            ]);
            return response()->json([
                'compliance' => $existing,
            ], 201);
        }

        $compliance = Compliance::create(
            [
                'offering_id' => $offering_id,
                'member_id' => $member_id,
                'notes' => $notes,
            ]
        );

        // Until file services are added, for now dummy values
        ComplianceDocument::create([
            'compliance_id' => $compliance->id,
            'file_path' => 'path/to/file/',
            'file_name' => 'fileName',
            'mime' => 'image/jpeg',
            'uploaded_by' => $compliance->member_id,
        ]);

        return response()->json([
            'compliance' => $compliance,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(['offering' => Compliance::findOrFail($id)]);
    }

    // NOTE: Only OFFICER/ADMIN can update
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', new Enum(RequirementStatus::class)],
            'verified_at' => 'required|date',
            'verified_by' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 422);
        }

        $compliance = Compliance::findOrFail($id);

        $compliance->update([
            'status' => $request->status,
            'verified_at' => $request->verified_at,
            'verified_by' => $request->verified_by,
        ]);

        $audit = ComplianceAudit::create([
            'compliance_id' => $compliance->id,
            'new_status' => $compliance->status,
            'changed_by' => $compliance->verified_by,
        ]);

        return response()->json([
            'compliance' => $compliance,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $offer = Compliance::findOrFail($id);
        $offer->delete();
        return response()->json(['message' => 'Compliance removed successfully'], 200);
    }
}
