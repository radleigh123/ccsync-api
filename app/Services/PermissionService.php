<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function getAll()
    {
        return Permission::all()->pluck('name');
    }

    public function create(string $perm)
    {
        return Permission::createOrFirst(['name' => $perm]);
    }

    /**
     * Find all users with the selected permissions
     */
    public function find(string $perm)
    {
        return User::permission($perm)->get();
    }

    public function update(string $id, string $newPerm)
    {
        $currPerm = Permission::findOrFail($id);
        $currPerm->update(['name' => $newPerm]);
        return $currPerm;
    }

    public function delete(string $id)
    {
        return Permission::findOrFail($id)->delete();
    }
}
