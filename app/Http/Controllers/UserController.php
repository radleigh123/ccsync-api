<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Http\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Kreait\Firebase\Exception\AuthException;

class UserController extends Controller
{
    use ApiResponse;

    protected UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all users
     */
    public function index()
    {
        $users = $this->service->getAll();
        return new UserCollection($users);
    }

    /**
     * Store a new user with Firebase Authentication and local database
     */
    public function store(RegisterRequest $request)
    {
        try {
            $user = $this->service->create($request->validated());
            return new UserResource($user);
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage(), code: 500);
        }
    }

    public function show(string $id)
    {
        return new UserResource($this->service->find($id));
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            $user = $this->service->update($id, $request->validated());
            return new UserResource($user);
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage(), code: 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            return $this->success(message: "Successfully deleted account", code: 204);
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage(), code: 500);
        }
    }

    /**
     * Send password reset email link to user's email
     */
    public function sendPasswordResetEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $this->service->resetPasswordLink($validated);
            return $this->success(message: "Successfully sent a reset password link.");
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage());
        }
    }

    /**
     * Send email verification link to user's email
     */
    public function sendEmailVerification(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $this->service->emailVerificationLink($validated);
            return $this->success(message: "Email verification sent successfully");
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage());
        }
    }

    // BUG: REFACTOR: REDIRECT TO FRONT INSTEAD DIRECTLY. FROM THERE ADD BUTTON ("Click to verify email")
    public function verifyEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            $user = $this->service->updateEmailVerification($validated);
            return $this->success(message: 'Successfully verified email.', code: 204);
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage());
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $userWithFirebase = $this->service->login($request->validated());
            return response()->json([
                'message' => 'Login successful',
                'user' => $userWithFirebase['user'],
                'firebase_user' => $userWithFirebase['firebaseUser'],
            ]);
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage(), code: $e->getCode());
        }
    }

    public function findUserSchoolId(Request $request)
    {
        $user = $this->service->findSchoolId($request->id_school_number);
        return new UserResource($user);
    }

    /* public function adminDashboard(Request $request)
    {
        $user = $request->get('auth_user');

        if ($user->hasRole('admin')) {
            return response()->json(['message' => 'Welcome admin']);
        }

        return response()->json(['message' => 'Access denied'], 403);
    } */
}
