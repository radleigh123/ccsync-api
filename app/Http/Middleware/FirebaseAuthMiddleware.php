<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FirebaseAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $idToken = $request->bearerToken() ?? $request->header('Firebase-Token');

        if (!$idToken) {
            return response()->json([
                'message' => 'Firebase ID token not provided'
            ], 401);
        }

        try {
            $auth = Firebase::auth();
            $verifiedIdToken = $auth->verifyIdToken($idToken);
            $firebaseUid = $verifiedIdToken->claims()->get('sub');

            $firebaseUser = $auth->getUser($firebaseUid);

            // Find the local user
            $user = User::where('firebase_uid', $firebaseUid)->first();

            // Add/Override data in current HTTP request object
            $request->merge(['auth_user' => $user]);
            Auth::login($user);

            if (!$user && $firebaseUser->email) {
                // Optionally create user if not exists
                $user = User::create([
                    'name' => $firebaseUser->displayName ?? 'Unknown User',
                    'email' => $firebaseUser->email,
                    'password' => Hash::make(uniqid()),
                    'firebase_uid' => $firebaseUid,
                    'email_verified_at' => $firebaseUser->emailVerified ? now() : null,
                ]);
            }

            // Add user to request
            if ($user) {
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });
            }

            // Add Firebase UID to request
            $request->attributes->set('firebase_user', $firebaseUser);
            $request->attributes->set('firebase_uid', $firebaseUid);
            $request->attributes->set('firebase_token', $verifiedIdToken);

            return $next($request);
        } catch (FailedToVerifyToken $e) {
            return response()->json([
                'message' => 'Invalid or expired Firebase token',
                'token' => $idToken,
                'error' => $e->getMessage()
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
