<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Http\Requests\Member\StoreMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Http\Resources\Member\MemberCollection;
use App\Http\Resources\Member\MemberResource;
use App\Http\Services\MemberService;
use App\Models\Member;
use App\Models\Program;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    use ApiResponse;

    protected MemberService $service;

    public function __construct(MemberService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all members with their programs
     */
    public function index()
    {
        $members = $this->service->getAll();
        return new MemberCollection($members);
    }

    /**
     * Store a new member
     */
    public function store(StoreMemberRequest $request)
    {
        $validated = $request->validated();

        $member = $this->service->create($validated);

        return new MemberResource($member);
    }

    /**
     * Display the specified member
     */
    public function show(string $id)
    {
        return new MemberResource($this->service->find($id));
    }

    /**
     * Update the specified member
     */
    public function update(UpdateMemberRequest $request, string $id)
    {
        $validated = $request->validated();

        // Retrieve the validated the input data...
        // $validated = $request->safe()->only(['first_name', 'last_name']);

        $member = $this->service->update($id, $validated);

        return new MemberResource($member);
    }

    /**
     * Remove the specified member
     */
    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            $this->success(message: 'Succesfully deleted member', code: 204);
        } catch (\Exception $e) {
            $this->error(message: $e->getMessage());
        }
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
}
