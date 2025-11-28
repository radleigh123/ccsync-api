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

    public function create(string $role)
    {
        return Role::firstOrCreate(['name' => $role]);
    }

    /**
     * Find all users with a certain role.
     */
    public function find(string $role)
    {
        return User::role($role)->get();
    }
}