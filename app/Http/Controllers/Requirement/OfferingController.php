<?php

namespace App\Http\Controllers\Requirement;

use App\Http\Controllers\Controller;
use App\Http\Resources\Requirement\OfferingCollection;
use App\Models\Offering;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfferingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return new RequirementComplianceResource($compliance->load(['offering','documents']));
        // return response()->json(['requirements' => Requirement::with('semester')->get()]);
        // return response()->json(['offerings' => Offering::with('requirement')->get()]);
        return new OfferingCollection(Offering::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (! $request->has('semester_id')) {
            return response()->json([
                'message' => "Missing semester_id",
            ], 400);
        }

        if (! $request->has('requirement_id')) {
            return response()->json([
                'message' => "Missing requirement_id",
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'requirement_id' => 'required|integer',
            'semester_id' => 'nullable|integer',
            'open_at' => 'required|date',
            'close_at' => 'required|date',
            'max_submissions' => 'integer',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 422);
        }

        $offering = Offering::create($request->all());
        $offering->load('requirement');

        return response()->json([
            'offering' => $offering,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(['offering' => Offering::findOrFail($id)]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (! $request->has('semester_id')) {
            return response()->json([
                'message' => "Missing semester_id",
            ], 400);
        }

        if (! $request->has('requirement_id')) {
            return response()->json([
                'message' => "Missing requirement_id",
            ], 400);
        }

        $offer = Offering::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'requirement_id' => 'required|integer',
            'semester_id' => 'nullable|integer',
            'open_at' => 'required|date',
            'close_at' => 'required|date',
            'max_submissions' => 'integer',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 422);
        }

        $offer->update($request->all());
        $offer->load('requirement');

        return response()->json([
            'offering' => $offer,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $offer = Offering::findOrFail($id);
        $offer->delete();
        return response()->json(['message' => 'Offering removed successfully'], 200);
    }
}
