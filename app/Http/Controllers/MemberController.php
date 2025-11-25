<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Http\Requests\Member\PromoteRequest;
use App\Http\Requests\Member\StoreMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Http\Resources\Member\MemberCollection;
use App\Http\Resources\Member\MemberResource;
use App\Http\Services\MemberService;
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
        return $this->success(
            new MemberCollection($this->service->getAll()),
            200,
            "Successfully retrieved members"
        );
    }

    /**
     * Store a new member
     */
    public function store(StoreMemberRequest $request)
    {
        $validated = $request->validated();
        $member = $this->service->create($validated);

        return $this->success(
            new MemberResource($member),
            201,
            "Successfully stored member"
        );
    }

    /**
     * Display the specified member
     */
    public function show(string $id)
    {
        return $this->success(
            new MemberResource($this->service->find($id)),
            200,
            "Successfully retrieved member"
        );
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
}
