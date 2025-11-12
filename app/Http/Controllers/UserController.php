<?php

namespace App\Http\Controllers;

use App\Enums\Gender;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Enum;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Laravel\Firebase\Facades\Firebase;

class UserController extends Controller
{
    protected $firebaseAuth;

    public function __construct()
    {
        $this->firebaseAuth = Firebase::auth();
    }

    /**
     * Get all users
     */
    public function index()
    {
        return response()->json(['users' => User::all()]);
    }

    /**
     * Store a new user with Firebase Authentication and local database
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        Log::info('Register method called', ['data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'display_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // --- FIREBASE RECORD ---
            $firebaseUser = $this->firebaseAuth->createUser([
                'displayName' => $request->display_name,
                'email' => $request->email,
                'password' => $request->password,
                'emailVerified' => false,
            ]);

            // --- USER DATABASE RECORD ---
            $user = User::create([
                'display_name' => $request->display_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'firebase_uid' => $firebaseUser->uid,
                'email_verified_at' => null,
            ]);

            // --- ROLE ASSIGNMENT (DEFAULT:STUDENT) ---
            $user->assignRole('student');

            DB::commit();

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'display_name' => $user->display_name,
                    'email' => $user->email,
                    'firebase_uid' => $firebaseUser->uid,
                    'email_verified' => false,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($firebaseUser)) {
                try {
                    $this->firebaseAuth->deleteUser($firebaseUser->uid);
                } catch (\Exception $deleteException) {
                    Log::error('Failed to delete Firebase user after rollback', [
                        'error' => $deleteException->getMessage()
                    ]);
                }
            }

            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        return response()->json(['user' => User::findOrFail($id)]);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $origFirebaseUid = $user->firebase_uid;
        $origEmail = $user->email;
        $origDisplayName = $user->display_name;

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'display_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            try {
                // Update user database
                $user->update($validator->validated());

                // Update user Firebase
                $this->firebaseAuth->updateUser($origFirebaseUid, [
                    'email' => $request->email,
                    'displayName' => $request->display_name,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully',
                ]);
            } catch (\Exception $dbException) {
                DB::rollBack();

                try {
                    $this->firebaseAuth->updateUser($origFirebaseUid, [
                        'email' => $origEmail,
                        'displayName' => $origDisplayName,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to update Firebase user', [
                        'success' => false,
                        'firebase_uid' => $origFirebaseUid,
                        'error' => $e->getMessage()
                    ]);
                }

                throw $dbException;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user from Firebase and local database
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $origEmail = $user->email;
        $origPassword = $user->password;

        try {
            DB::beginTransaction();

            try {
                $user->delete();
                DB::commit();
                $this->firebaseAuth->deleteUser($user->firebase_uid);
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully',
                ]);
            } catch (\Exception $dbException) {
                DB::rollBack();

                try {
                    $this->firebaseAuth->createUserWithEmailAndPassword($origEmail, $origPassword);
                } catch (\Exception $deleteException) {
                    Log::error('Failed to created Firebase user', [
                        'success' => false,
                        'error' => $deleteException->getMessage()
                    ]);
                }

                throw $dbException;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Delete user failed',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Verify firebase token from client
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

            return response()->json([
                'success' => true,
                'message' => 'Token verified successfully',
                'user' => [
                    'id' => $user->id,
                    'display_name' => $user->display_name,
                    'email' => $user->email,
                    'firebase_uid' => $firebaseUid,
                    'email_verified' => $firebaseUser->emailVerified,
                    'id_school_number' => $user->id_school_number,
                ],
                'firebase_claims' => $verifiedIdToken->claims()->all(),
            ]);
        } catch (FailedToVerifyToken $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ID token',
                'error' => $e->getMessage()
            ], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token verification failed',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user profile by ID School Number or Firebase UID
     * OBSOLETE
     */
    public function getUserById(Request $request)
    {
        try {
            if ($request->has('id_school_number') && $request->id_school_number > 0) {
                $user = User::with(['member' => function ($query) {
                    $query->select(
                        'user_id',
                        'first_name',
                        'last_name',
                        'suffix',
                        'birth_date',
                        'enrollment_date',
                        'program',
                        'year',
                        'is_paid',
                        'gender',
                        'biography',
                        'phone'
                    );
                }])
                    ->where('id_school_number', $request->id_school_number)
                    ->first();
                return response()->json([
                    'user' => $user
                ]);
            }

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
        $users = User::select('id', 'display_name', 'email', 'email_verified_at', 'id_school_number')->get();

        return response()->json(['users' => $users], 200);
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

            return response()->json([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'display_name' => $user->display_name,
                    'email' => $user->email,
                    'firebase_uid' => $user->firebase_uid,
                    'email_verified' => !is_null($user->email_verified_at),
                ],
                'firebase_user' => $firebaseUser,
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

    /* public function adminDashboard(Request $request)
    {
        $user = $request->get('auth_user');

        if ($user->hasRole('admin')) {
            return response()->json(['message' => 'Welcome admin']);
        }

        return response()->json(['message' => 'Access denied'], 403);
    } */

    // --- Profile Page ---

    public function updateProfileInformation(Request $request, string $id)
    {
        $user = User::with('member')->findOrFail($id);

        $firebaseUid = $user->firebase_uid;
        $origDisplayName = $user->display_name;

        $validator = Validator::make($request->all(), [
            'display_name' => 'max:255|string',
            'biography' => 'max:255|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            try {
                // Update user database
                $user->update(['display_name' => $request->display_name]);
                $user->member->update(['biography' => $request->biography]);

                // Update user Firebase
                $this->firebaseAuth->updateUser($firebaseUid, [
                    'displayName' => $request->display_name,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'User & Member updated successfully',
                    'user' => $user->getChanges(),
                    'member' => $user->member->getChanges()
                ]);
            } catch (\Exception $dbException) {
                DB::rollBack();

                try {
                    $this->firebaseAuth->updateUser($firebaseUid, [
                        'displayName' => $origDisplayName,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to update Firebase user', [
                        'success' => false,
                        'error' => $e->getMessage()
                    ]);
                }

                throw $dbException;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePersonal(Request $request, string $id)
    {
        $user = User::with('member')->findOrFail($id);
        $firebaseUid = $user->firebase_uid;

        $validator = Validator::make($request->all(), [
            'email' => 'max:255|string',
            'phone' => 'max:255|string',
            'gender' => [new Enum(Gender::class)]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            try {
                $validatedData = $validator->validated();

                // Update user database
                $user->update(['email' => $validatedData['email']]);
                $user->member->update($validatedData);

                // Update user Firebase
                $this->firebaseAuth->updateUser($firebaseUid, [
                    'email' => $validatedData['email'],
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'User & Member updated successfully',
                    'user' => [
                        $user->getPrevious(),
                        $user->getChanges()
                    ],
                    'member' => [
                        $user->member->getPrevious(),
                        $user->member->getChanges(),
                    ],
                ]);
            } catch (\Exception $dbException) {
                DB::rollBack();

                try {
                    $this->firebaseAuth->updateUser($firebaseUid, [
                        'email' => $user->getOriginal('email'),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to update Firebase user', [
                        'success' => false,
                        'error' => $e->getMessage()
                    ]);
                }

                throw $dbException;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePassword(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $firebaseUid = $user->firebase_uid;

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|min:6|string',
            'password' => 'required|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is not correct.',
            ], 401);
        }

        try {
            DB::beginTransaction();

            try {
                $validatedData = $validator->validated();

                // Update user database
                $user->update(['password' => Hash::make($validatedData['password'])]);

                // Update user password Firebase
                /* $this->firebaseAuth->updateUser($firebaseUid, [
                    'password' => $validatedData['password'],
                ]); */
                $this->firebaseAuth->changeUserPassword($firebaseUid, $validatedData['password']);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Password updated successfully',
                    'user' => [
                        $user->getPrevious(),
                        $user->getChanges()
                    ],
                ]);
            } catch (\Exception $dbException) {
                DB::rollBack();

                try {
                    $this->firebaseAuth->updateUser($firebaseUid, [
                        'password' => $request->current_password,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to update Firebase user', [
                        'success' => false,
                        'error' => $e->getMessage()
                    ]);
                }

                throw $dbException;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
