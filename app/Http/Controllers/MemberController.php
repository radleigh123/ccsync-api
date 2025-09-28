<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MemberController extends Controller
{
    /**
     * Get all members with their programs
     */
    public function index()
    {
        $members = Member::with('program')->get();
        return response()->json(['members' => $members]);
    }

    /**
     * Store a new member
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'id_school_number' => 'required|integer|unique:members,id_school_number',
            'email' => 'nullable|email|unique:members,email',
            'birth_date' => 'required|date',
            'enrollment_date' => 'required|date',
            'program' => 'required|string|exists:programs,code',
            'year' => 'required|integer|between:1,4',
            'is_paid' => 'required|boolean'
        ]);

        // TODO: Improve later validation error handling ☠
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }

        $member = Member::create($request->all());
        $member->load('program');

        return response()->json([
            'message' => 'Member created successfully',
            'member' => $member
        ], 201);
    }

    /**
     * Display the specified member
     */
    public function show(string $id)
    {
        $member = Member::with('program')->findOrFail($id);
        return response()->json(['member' => $member]);
    }

    /**
     * Update the specified member
     */
    public function update(Request $request, string $id)
    {
        $member = Member::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'id_school_number' => 'sometimes|required|integer|unique:members,id_school_number,' . $member->id,
            'email' => 'nullable|email|unique:members,email,' . $member->id,
            'birth_date' => 'sometimes|required|date',
            'enrollment_date' => 'sometimes|required|date',
            'program' => 'sometimes|required|string|exists:programs,code',
            'year' => 'sometimes|required|integer|between:1,4',
            'is_paid' => 'sometimes|required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $member->update($request->all());
        $member->load('program');

        return response()->json([
            'message' => 'Member updated successfully',
            'member' => $member
        ]);
    }

    /**
     * Remove the specified member
     */
    public function destroy(string $id)
    {
        $member = Member::findOrFail($id);
        $member->delete();

        return response()->json(['message' => 'Member deleted successfully']);
    }

    /**
     * Get programs for dropdown
     */
    public function getPrograms()
    {
        $programs = Program::all();
        return response()->json(['programs' => $programs]);
    }
}
