<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Helper\DiskHelper;
use App\Http\Requests\Member\UpdateProfileRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Services\MemberService;
use App\Services\UserService;
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

    public function uploadPicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|file|image|max:5120' // 5mb MAX
        ]);

        try {
            // Save to bucket
            $path = $request->file('profile_picture')->store('profile_pictures', 's3');
            $disk = DiskHelper::getS3Disk('s3');
            $url = $disk->temporaryUrl($path, now()->addMinutes(60));
            $result = $this->userService->updateProfilePath($path, $request->user()->id);

            if (! $result) {
                throw new \Exception("Something went wrong storing profile picture path");
            }

            return $this->success(data: $url, message: "Successfully updated profile picture", code: 201);
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage());
        }
    }

    public function getPicture(string $memberId)
    {
        $member = $this->memberService->find($memberId);
        $user = $member->user;
        try {
            $member = $this->memberService->find($memberId);
            $user = $member->user;

            if (! $user->avatar_path) {
                throw new \Exception("No profile picture found for this user");
            }

            $disk = DiskHelper::getS3Disk('s3');
            $url = $disk->temporaryUrl($user->avatar_path, now()->addMinutes(60));

            return $this->success(data: $url, message: "Successfully retrieved profile picture", code: 200);
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage());
        }
        /* return response()->json([
            'member' => $member,
            'user'  => $user,
        ]); */
    }
}
