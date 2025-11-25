<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Program;
use App\Models\User;
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
            return response()->json(['members' => Member::all()]);
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
            'middle_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'id_school_number' => 'required|integer|unique:members,id_school_number',
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

        // load user_id and semester_id (localStorage) from request
        $user = User::findOrFail($request->user_id);

        $member = Member::create([
            ...$request->all(),
            'user_id' => $user->id,
        ]);
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
        return response()->json(['member' => Member::findOrFail($id)]);
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
            // $query = Member::with('user')->where('role=officer');

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

    // --- Promotion logic ---

    /**
     * Assign a new role to a member (student)
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function promoteMember(Request $request, string $id)
    {
        $user = $request->user();
        $memberId = $id;
        $newRole = $request->input('role');

        // i think it the role will change the admin as president or vice president
        if (! $user->hasAnyRole(['officer', 'admin'])) {
            return response()->json([
                'message' => 'Unauthorized: Only officers or admins can promote members.'
            ], 403);
        }

        $member = Member::with('user')->findOrFail($memberId);
        $student = $member->user;   

        if ($user->hasRole('officer') && $newRole === 'admin') {
            return response()->json([
                'message' => 'Officers cannot promote members to admin.'
            ], 403);
        }

        $student->syncRoles([$newRole]); // remove previous, assign new

        return response()->json([
            'message' => "Member promoted to {$newRole} successfully.",
            'member' => $member->load('user')
        ]);
    }

    public function demoteOfficer(Request $request, string $id)
    {
        $user = $request->user();
        $memberId = $id;
        $newRole = $request->input('role');

        if (! $user->hasRole('admin')) {
            return response()->json([
                'message' => 'Unauthorized: Only admins can demote officer.'
            ], 403);
        }

        $member = Member::with('user')->findOrFail($memberId);
        $officer = $member->user;

        if ($newRole === 'admin') {
            return response()->json([
                'message' => 'Cannot demote to admin.'
            ], 403);
        }

        $officer->syncRoles([$newRole]); // remove previous, assign new

        return response()->json([
            'message' => "Officer demoted to {$newRole} successfully.",
            'member' => $member->load('user')
        ]);
    }

    //to get officers in order

    public function getOfficersInOrder()
    {
        try {
            // Desired officer hierarchy
            $roleOrder = [
                'president',
                'vice president',
                'treasurer',
                'auditor',
                'representative',
                'officer'
            ];

            // Get all users with any of these roles + load member info
            $users = User::role($roleOrder)
                ->with(['member', 'roles']) 
                ->get();

            // Sort based on role hierarchy
            $sorted = $users->sortBy(function ($user) use ($roleOrder) {
                return array_search($user->roles->first()->name, $roleOrder);
            })->values();

            // Map to clean output structure
            $officers = $sorted->map(function ($user) {
                $member = $user->member;

                return [
                    'id' => $user->id,
                    'role' => $user->roles->first()->name,
                    'email' => $user->email,
                    'name' => trim(
                        ($member->first_name ?? '') . ' ' .
                        ($member->middle_name ? $member->middle_name . ' ' : '') .
                        ($member->last_name ?? '') . ' ' .
                        ($member->suffix ?? '')
                    ),
                    'member_info' => $member,
                ];
            });

            return response()->json([
                'message' => 'Officer list retrieved successfully.',
                'officers' => $officers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving officers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function searchMembers(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json([]);
        }

        $members = Member::where('first_name', 'LIKE', "%$query%")
            ->orWhere('last_name', 'LIKE', "%$query%")
            ->orWhere('id_school_number', 'LIKE', "%$query%")
            ->with('user')
            ->limit(10)
            ->get();

        return response()->json($members);
    }

}
