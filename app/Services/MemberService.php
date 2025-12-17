<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Member;
use App\Models\User;

class MemberService
{
    public function getAll()
    {
        return Member::with('user')->get();
    }

    public function create(array $data)
    {
        User::findOrFail($data['user_id']); // Check if user ID exists

        $member = Member::create($data);
        return $member->load(['user']);
    }

    public function find(string $id)
    {
        return Member::with(['user', 'semester'])->findOrFail($id);
    }

    public function update(string $id, array $data)
    {
        $member = Member::findOrFail($id);
        $member->update($data);

        return $member->load(['semester']);
    }

    public function delete(string $id)
    {
        return Member::findOrFail($id)->delete();
    }

    public function paginate($page = null, $perPage = null)
    {
        return Member::with('user')
            ->paginate(
                perPage: $perPage,
                page: $page,
            )->toResourceCollection();
    }

    public function findBySchoolNumber($idSchoolNumber)
    {
        return Member::with('user')->where('id_school_number', $idSchoolNumber)->firstOrFail();
    }

    public function checkEventRegistration($memberId, $eventId)
    {
        $member = Member::with('events')->findOrFail($memberId);
        return $member->events->find($eventId);
    }

    public function promoteMemberToOfficer(User $user, string $memberId, string $newRole)
    {
        $member = Member::with('user')->find($memberId);

        if ($member === null) {
            throw new \Exception("Chosen member is not yet registered to the organization.");
        }

        $currRole = $this->getMemberCurrentRole($member);

        if (! $user->hasRole(Role::OFFICER)) {
            throw new \Exception("Unauthorized: Only officers can promote members.", 403);
        }

        if ($newRole == $currRole) {
            throw new \Exception("Promoting member again with the same role is not allowed.", 403);
        }

        if ($newRole == "student") {
            throw new \Exception("Promoting member back to Student is not allowed.", 403);
        }

        if ($newRole == "officer") {
            throw new \Exception("Must select a specific role to promote member.", 403);
        }

        if ($this->isHighLevelPromotion($newRole) && ! $user->hasAnyRole([Role::ADMIN, Role::PRESIDENT, Role::VICE_PRESIDENT_INT, Role::VICE_PRESIDENT_EXT])) {
            throw new \Exception("Unauthorized: Only president or vice-president can promote to high-level executive roles.", 403);
        }

        if ($this->isRoleOccupied($newRole)) {
            throw new \Exception("The role of {$newRole} is already occupied by another user.", 403);
        }

        $member->user->syncRoles(Role::STUDENT, Role::OFFICER, $newRole);

        return $member;
    }

    public function demoteOfficerToRole(User $user, string $memberId, string $newRole)
    {
        $member = Member::with('user')->find($memberId);

        if ($member === null) {
            throw new \Exception("Impossible to demote, chosen officer is not even registered to the organization.");
        }

        $currRole = $this->getMemberCurrentRole($member);

        if (! $user->hasAnyRole([Role::ADMIN, Role::PRESIDENT, Role::VICE_PRESIDENT_INT, Role::VICE_PRESIDENT_EXT])) {
            throw new \Exception("Unauthorized: Only president, or vice-president can demote officers.", 403);
        }

        if ($newRole == $currRole) {
            throw new \Exception("Demoting officer again with the same role is not allowed.", 403);
        }

        if ($newRole == "president") {
            throw new \Exception("Can not demote to president.", 403);
        }

        if ($currRole == "president" && ! $user->hasAnyRole([Role::ADMIN, Role::PRESIDENT, Role::VICE_PRESIDENT_INT, Role::VICE_PRESIDENT_EXT])) {
            throw new \Exception("Unauthorized: Only admin or vice-president can demote the president.", 403);
        }

        // This means officer is no longer part of PSITS
        if ($newRole == "student") {
            $member->user->syncRoles(Role::STUDENT);
            return $member;
        }

        $member->user->syncRoles(Role::STUDENT, Role::OFFICER, $newRole);
        return $member;
    }

    private function isRoleOccupied(string $newRole): bool
    {
        // Roles that should be unique: PRESIDENT, VICE-PRESIDENT-INT, VICE-PRESIDENT-EXT
        $uniqueRoles = [
            "president",
            "vice-president-internal",
            "vice-president-external",
        ];

        if (in_array($newRole, $uniqueRoles)) {
            $existingUser = User::role($newRole)->first();
            return $existingUser !== null;
        }
        return false;
    }

    private function getMemberCurrentRole(Member $member)
    {
        $roles = $member->user->getRoleNames();

        if ($roles->isEmpty()) {
            return null;
        }

        return $roles->last();
    }

    /**
     * Check if promotion is to high-level executive role
     */
    private function isHighLevelPromotion(string $role): bool
    {
        return in_array($role, ["president", "vice-president-internal", "vice-president-external"]);
    }
}
