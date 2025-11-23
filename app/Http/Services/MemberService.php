<?php

namespace App\Http\Services;

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
}
