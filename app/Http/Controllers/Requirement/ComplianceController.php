<?php

namespace App\Http\Controllers\Requirement;

use App\Enums\RequirementStatus;
use App\Http\Controllers\Controller;
use App\Models\Compliance;
use App\Models\ComplianceAudit;
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 422);
        }

        $compliance = Compliance::create($request->all());
        $compliance->load('offering');
        $compliance->load('member');

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
        $compliance->load('offering');
        $compliance->load('member');

        $audit = ComplianceAudit::create([
            'compliance_id' => $compliance->id,
            'new_status' => $compliance->status,
            'changed_by' => $compliance->member->id,
        ]);

        return response()->json([
            'compliance' => $compliance,
            'audit' => $audit,
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
