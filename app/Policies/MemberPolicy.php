<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view members');
    }

    public function view(?User $user, Member $member): bool
    {
        // Admins & Officers can view anyone
        if ($user->can('view members')) {
            return true;
        }

        // Students can only view themselves 
        return $user->id == $member->user_id;
    }

    public function create(User $user): bool
    {
        return $user->can('add members');
    }

    public function update(User $user, Member $member): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Can edit members except with admin/officer roles
        if ($user->hasRole('officer')) {
            return $user->can('edit members') && ! $member->user->hasAnyRole(['admin', 'officer']);
        }

        return false;
    }

    public function delete(User $user, Member $member): bool
    {
        // Can delete members, except admin
        return $user->can('delete members') && ! $member->user->hasRole('admin');
    }

    public function promote(User $user, Member $member): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        // Only promote students only
        if ($user->hasRole('officer')) {
            return $user->can('promote member') && $member->user->hasRole('student');
        }

        return false;
    }
}
