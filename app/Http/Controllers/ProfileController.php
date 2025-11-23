<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Http\Requests\Member\UpdateProfileRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Services\MemberService;
use App\Http\Services\UserService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;
    protected UserService $userService;
    protected MemberService $memberService;

    public function __construct(UserService $userService, MemberService $memberService)
    {
        $this->userService = $userService;
        $this->memberService = $memberService;
    }

    public function update(UpdateProfileRequest $request, string $memberId)
    {

        $validatedEmail = $request->safe()->only(['email', 'display_name']); // Only validate the email for Users
        $validatedMember = $request->safe()->except(['email', 'display_name']); // Validate the other inputs

        try {
            $member = $this->memberService->update($memberId, $validatedMember);

            $userId = $this->memberService->find($memberId)->user->id;
            $user = $this->userService->update($userId, $validatedEmail);

            return $this->success(
                [
                    'user' => $user,
                    'member' => $member,
                ],
                201,
                "User & Member updated successfully"
            );
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage());
        }
    }

    public function updatePassword(ChangePasswordRequest $request, string $memberId)
    {
        try {
            $userId = $this->memberService->find($memberId)->user->id;
            $this->userService->updatePassword($userId, $request->validated());

            return $this->success(message: "Successfully updated password.", code: 201);
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage());
        }
    }
}
