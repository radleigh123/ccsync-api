<?php

namespace App\Http\Controllers\Requirement;

use App\Enums\Type;
use App\Http\Controllers\Controller;
use App\Models\Requirement;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;

class RequirementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return new RequirementComplianceResource($compliance->load(['offering','documents']));
        // return response()->json(['requirements' => Requirement::with('semester')->get()]);
        return response()->json(['requirements' => Requirement::all()]);
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

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['required', new Enum(Type::class)],
            'semester_id' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 422);
        }

        $requirement = Requirement::create($request->all());
        $requirement->load('semester');

        return response()->json([
            'requirement' => $requirement,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(['requirement' => Requirement::findOrFail($id)]);
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

        $req = Requirement::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => ['required', new Enum(Type::class)],
            'semester_id' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 422);
        }

        $req->update($request->all());
        $req->load('semester');

        return response()->json([
            'requirement' => $req,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $req = Requirement::findOrFail($id);
        $req->delete();
        return response()->json(['message' => 'Requirement removed successfully'], 200);
    }
}
