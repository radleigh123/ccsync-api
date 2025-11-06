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
        try {
            $query = Member::all();
            // $query = Member::with(['user'])->get();
            return response()->json([
                'message' => 'Members retrieved successfully',
                'members' => $query
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new member
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
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
        $member = Member::with(['program', 'user'])->findOrFail($id);
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

    public function getMembersPagination(Request $request)
    {
        try {
            // TODO: limit columns
            $query = Member::with('program')->with('user')->get();

            if ($request->has('page') && $request->has('per_page')) {
                $perPage = $request->input('per_page', 10);
                $query = Member::with('program')->paginate($perPage);
            }

            if ($request->has('id_school_number') && $request->id_school_number > 0) {
                $query = Member::with('program')->where('id_school_number', $request->id_school_number)->get();
            }

            return response()->json([
                'message' => 'Members retrieved successfully',
                'members' => $query
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified member based on id school number
     */
    public function getMember(Request $request)
    {
        try {
            if ($request->has('id_school_number') && $request->id_school_number > 0) {
                // return response()->json(Member::with(['user'])->where('id_school_number', $request->id_school_number)->get());
                return response()->json([
                    'member' => Member::where('id_school_number', $request->id_school_number)->get()
                ]);
            }
            throw new \Exception('Something went wrong with ID school number');
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Failed to retrieve member through id school number",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Examine if member is registered to event
     */
    public function checkMemberRegistration(Request $request, string $id)
    {
        $query = Member::with('events')->findOrFail($id);
        $eventId = $request->input('event_id');

        $matchedEvent = $query->events->firstWhere('id', $eventId);

        return response()->json([
            'registered' => !is_null($matchedEvent),
            'event' => $matchedEvent
        ]);
    }

    /**
     * Get programs for dropdown
     */
    public function getPrograms()
    {
        return response()->json(['programs' => Program::all()]);
    }

    public function promoteMember(Request $request)
    {
        $user = $request->user();
        if ($user->hasAnyRole(['officer', 'admin'])) {
            return response()->json([
                'user' => $user
            ]);
        }

        return response()->json([
            'user' => $user->can('view members')
        ]);
    }
}
