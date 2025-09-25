<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Get user list
    public function getUserList(Request $request)
    {
        // filtered columns (not include password, remember_token, and etc.)
        $users = User::select('id', 'name', 'email', 'email_verified_at', 'id_school_number', 'role')->get();

        return response()->json(['users' => $users], 200);
    }

    /**
     * Get a specific user by database ID
     */
    public function getUserById(Request $request, $id)
    {
        $user = User::where('id', $id)
            ->select('id', 'name', 'email', 'email_verified_at', 'id_school_number', 'role')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['user' => $user], 200);
    }
}
