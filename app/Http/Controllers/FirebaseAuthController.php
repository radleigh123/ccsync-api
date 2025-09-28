<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FirebaseAuthController extends Controller
{
    protected $firebaseAuth;

    public function __construct()
    {
        $this->firebaseAuth = Firebase::auth();
    }

    /**
     * Login user (mainly used for testing purposes, Postman)
     */
    public function login(Request $request)
    {
        Log::info('Login method called', ['data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $signInResult = $this->firebaseAuth->signInWithEmailAndPassword($request->email, $request->password);
            $firebaseUser = $signInResult->data();

            $firebaseUid = $firebaseUser['localId'] ?? null;

            if (!$firebaseUid) {
                return response()->json([
                    'message' => 'Failed to retrieve Firebase UID'
                ], 500);
            }

            $user = User::where('firebase_uid', $firebaseUid)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found in local database'
                ], 404);
            }

            $customToken = $this->firebaseAuth->createCustomToken($firebaseUid, [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ]);

            return response()->json([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'firebase_uid' => $user->firebase_uid,
                    'email_verified' => !is_null($user->email_verified_at),
                ],
                'firebase_token' => $customToken->toString(),
                'firebase_uid' => $firebaseUid,
            ]);
        } catch (AuthException $e) {
            return response()->json([
                'message' => 'Firebase authentication failed',
                'error' => $e->getMessage()
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register a new user with Firebase Authentication and local database
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        Log::info('Register method called', ['data' => $request->all()]);

        /* $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]); */
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'id_number' => 'required|string|max:10|unique:users,id_school_number',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Start a database transaction
            DB::beginTransaction();

            $firebaseUser = $this->firebaseAuth->createUser([
                'email' => $request->email,
                'password' => $request->password,
                'displayName' => $request->name,
                'emailVerified' => false,
            ]);

            try {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'firebase_uid' => $firebaseUser->uid,
                    'id_school_number' => $request->id_number,
                    'email_verified_at' => null,
                    'role' => 'user',
                ]);

                // Commit the transaction
                DB::commit();

                $customToken = $this->firebaseAuth->createCustomToken($firebaseUser->uid, [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                ]);

                return response()->json([
                    'message' => 'User registered successfully',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'firebase_uid' => $firebaseUser->uid,
                        'email_verified' => false,
                        'role' => $user->role,
                        'id_school_number' => $user->id_school_number,
                    ],
                    'firebase_token' => $customToken->toString(),
                    'firebase_uid' => $firebaseUser->uid,
                ], 201);
            } catch (\Exception $dbException) {
                DB::rollBack();

                try {
                    $this->firebaseAuth->deleteUser($firebaseUser->uid);
                } catch (\Exception $deleteException) {
                    Log::error('Failed to delete Firebase user', [
                        'firebase_uid' => $firebaseUser->uid,
                        'error' => $deleteException->getMessage()
                    ]);
                }

                throw $dbException;
            }
        } catch (AuthException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Firebase registration failed',
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyToken(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($request->id_token);
            $firebaseUid = $verifiedIdToken->claims()->get('sub');

            // Get Firebase User
            $firebaseUser = $this->firebaseAuth->getUser($firebaseUid);

            $user = User::where('firebase_uid', $firebaseUid)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $firebaseUser->displayName ?? 'Unknown User',
                    'email' => $firebaseUser->email,
                    'password' => Hash::make(uniqid()),
                    'firebase_uid' => $firebaseUid,
                    'email_verified_at' => $firebaseUser->emailVerified ? now() : null,
                ]);
            }

            return response()->json([
                'message' => 'Token verified successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'firebase_uid' => $firebaseUid,
                    'email_verified' => $firebaseUser->emailVerified,
                    'role' => $user->role,
                    'id_school_number' => $user->id_school_number,
                ],
                'firebase_claims' => $verifiedIdToken->claims()->all(),
            ]);
        } catch (FailedToVerifyToken $e) {
            return response()->json([
                'message' => 'Invalid ID token',
                'error' => $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token verification failed',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user profile by Firebase UID
     */
    public function getUser(Request $request)
    {
        try {
            $firebaseUid = $request->user()->firebase_uid ?? $request->header('Firebase-UID');

            if (!$firebaseUid) {
                return response()->json([
                    'message' => 'Firebase UID not provided'
                ], Response::HTTP_BAD_REQUEST);
            }

            $firebaseUser = $this->firebaseAuth->getUser($firebaseUid);
            $localUser = User::where('firebase_uid', $firebaseUid)->first();

            return response()->json([
                'user' => [
                    'local' => $localUser,
                    'firebase' => [
                        'uid' => $firebaseUser->uid,
                        'email' => $firebaseUser->email,
                        'display_name' => $firebaseUser->displayName,
                        'email_verified' => $firebaseUser->emailVerified,
                        'disabled' => $firebaseUser->disabled,
                        'created_at' => $firebaseUser->metadata->createdAt?->format('Y-m-d H:i:s'),
                        'last_login' => $firebaseUser->metadata->lastLoginAt?->format('Y-m-d H:i:s'),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get user',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $link = $this->firebaseAuth->sendPasswordResetLink($request->email);

            return response()->json([
                'message' => 'Password reset email sent successfully',
            ]);
        } catch (AuthException $e) {
            return response()->json([
                'message' => 'Failed to send password reset email',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Send email verification
     */
    public function sendEmailVerification(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($request->id_token);
            $firebaseUid = $verifiedIdToken->claims()->get('sub');

            $link = $this->firebaseAuth->sendEmailVerificationLink($firebaseUid);

            return response()->json([
                'message' => 'Email verification sent successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send email verification',
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

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

    /**
     * Edit user by ID from Firebase and local database
     */
    public function editUser(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
            'id' => 'required|integer', // Changed from id_school_number to id
            'email' => 'required|email',
            'display_name' => 'required|string|max:255',
            'id_school_number' => 'required|string|max:10',
        ]);

        try {
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($request->id_token);
            // Get user by ID instead of school number
            $user = User::findOrFail($request->id);
            $firebaseUid = $user->firebase_uid;

            // Update user in Firebase
            $this->firebaseAuth->updateUser($firebaseUid, [
                'email' => $request->email,
                'displayName' => $request->display_name,
            ]);

            // Update user in local database
            $user->update([
                'email' => $request->email,
                'name' => $request->display_name, // Make sure this matches your column name
                'id_school_number' => $request->id_school_number,
            ]);

            return response()->json([
                'message' => 'User updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete user from Firebase and local database
     */
    public function deleteUser(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
            'id_school_number' => 'required|string|max:10',
        ]);

        try {
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($request->id_token);
            // $firebaseUid = $verifiedIdToken->claims()->get('sub');
            $firebaseUid = User::where('id_school_number', $request->id_school_number)->value('firebase_uid');

            // Delete from Firebase
            $this->firebaseAuth->deleteUser($firebaseUid);

            // Delete from local database
            User::where('firebase_uid', $firebaseUid)->delete();

            return response()->json([
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
