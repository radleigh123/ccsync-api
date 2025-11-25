<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Http\Requests\Member\PromoteRequest;
use App\Http\Requests\Member\StoreMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Http\Resources\Member\MemberCollection;
use App\Http\Resources\Member\MemberResource;
use App\Services\MemberService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        return $this->success($this->service->getAll()->toResourceCollection());
    }

    /**
     * Store a new member
     */
    public function store(StoreMemberRequest $request)
    {
        $member = $this->service->create($request->validated());
        return $this->success($member->toResource(), 201);
    }

    /**
     * Display the specified member
     */
    public function show(string $id)
    {
        try {
            return $this->success(
                new MemberResource($this->service->find($id)),
                200,
                "Successfully retrieved member"
            );
        } catch (ModelNotFoundException $e) {
            return $this->error(message: "Member has not been registered");
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500
            );
        }
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

        return $this->success(
            new MemberResource($member),
            200,
            "Successfully updated member {$member->first_name}"
        );
    }

    /**
     * Remove the specified member
     */
    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            return $this->success(
                message: 'Succesfully deleted member',
                code: 204, // Change to 200, if want result message
            );
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500,
            );
        }
    }

    public function getMembersPagination(Request $request)
    {
        // TODO: limit columns
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        try {
            return $this->service->paginate($page, $perPage);
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500,
            );
        }
    }

    /**
     * Display the specified member based on id school number
     */
    public function getMember(Request $request)
    {
        try {
            $idSchoolNumber = $request->input('id_school_number', 1);
            if ($idSchoolNumber == 1) {
                throw new \Exception("ID School Number not inputted", 422);
            }
            $member = $this->service->findBySchoolNumber($idSchoolNumber);
            return $this->success(
                new MemberResource($member),
                200,
                "Successfully retrieved {$member->first_name} by School ID"
            );
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500,
            );
        }
    }

    /**
     * Examine if member is registered to event
     */
    public function checkMemberRegistration(Request $request, string $memberId)
    {
        $eventId = $request->input('event_id');
        $event = $this->service->checkEventRegistration($memberId, $eventId);
        $msgResult = is_null($event) ?
            "Member is not registered to this event."
            :
            "Member is registered to this event.";

        return $this->success(
            message: $msgResult,
            code: 200,
        );
    }

    /**
     * Assign a new role to a member (student)
     */
    public function promoteMember(PromoteRequest $request, string $memberId)
    {
        $validated = $request->validated();

        $newRole = $validated['role'];
        $user = $request->user(); // Get current user

        try {
            return $this->success(
                $this->service->promoteMemberToOfficer($user, $memberId, $newRole),
                200,
                "Successfully promoted member to {$newRole}."
            );
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
            );
        }
    }

    public function demoteOfficer(PromoteRequest $request, string $memberId)
    {
        $validated = $request->validated();

        $newRole = $validated['role'];
        $user = $request->user(); // Get current user

        try {
            return $this->success(
                $this->service->demoteOfficerToRole($user, $memberId, $newRole),
                200,
                "Successfully demoted officer to {$newRole}."
            );
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
            );
        }
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
