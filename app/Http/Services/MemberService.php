<?php

namespace App\Http\Services;

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
        return Member::where('id_school_number', $idSchoolNumber)->firstOrFail();
    }

    public function checkEventRegistration($memberId, $eventId)
    {
        $member = Member::with('events')->findOrFail($memberId);
        return $member->events->find($eventId);
    }

    public function promoteMemberToOfficer(User $user, string $id, string $newRole)
    {
        $member = Member::with('user')->find($id);
        if ($member === null) {
            throw new \Exception("Chosen member is not yet registered to the organization.");
        }

        if (! $user->hasAllRoles(['student', 'officer'])) {
            throw new \Exception("Unauthorized: Only officers can promote members.", 403);
        }

        if ($newRole == 'student') {
            throw new \Exception("Promoting member to Student is not allowed.", 403);
        }

        if (! $user->hasRole([Role::ADMIN, Role::PRESIDENT]) && ($newRole == 'admin' || $newRole == 'president')) {
            throw new \Exception("Only President can promote members to admin", 403);
        }

        $member->user->syncRoles(Role::STUDENT, Role::OFFICER, $newRole);

        return $member;
    }

    public function demoteOfficerToRole(User $user, string $id, string $newRole)
    {
        $member = Member::with('user')->find($id);
        if ($member === null) {
            throw new \Exception("Impossible to demote, chosen officer is not even registered to the organization.");
        }

        // NOTE: Subject to change
        if (! $user->hasAnyRole(['admin', 'president', 'vice-president'])) {
            throw new \Exception("Unauthorized: Only president, or vice-president can demote officers.", 403);
        }

        if ($newRole == 'president') {
            throw new \Exception("Can not demote to president.", 403);
        }

        // This means officer is no longer part of PSITS
        if ($newRole == 'student') {
            $member->user->syncRoles(Role::STUDENT);
            return $member;
        }

        $member->user->syncRoles(Role::STUDENT, Role::OFFICER, $newRole);

        return $member;
    }
}
