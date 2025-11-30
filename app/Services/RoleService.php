<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleService
{
    /**
     * Get all roles in database.
     */
    public function getAll()
    {
        $roles = Role::all()->pluck('name');
        return $roles;
    }

    /**
     * Find all users with a certain role.
     */
    public function find(string $role)
    {
        return User::role($role)->get();
    }

    public function create(string $role)
    {
        return Role::firstOrCreate(['name' => $role]);
    }

    public function update(string $id, string $role)
    {
        $currRole = Role::findOrFail($id);
        $currRole->update(['name' => $role]);
        return $currRole;
    }

    public function delete(string $id)
    {
        return Role::findOrFail($id)->delete();
    }
}