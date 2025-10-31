<?php

use App\Http\Controllers\EventsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MemberController;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function (Request $request) {
    return response()->json(['message' => 'CCSync API is running.']);
});

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::get('/register', function () {
    return response()->json(['message' => 'Register endpoint']);
})->name('test');

Route::post('/test/login', [UserController::class, 'login']);

// Firebase Authentication routes
Route::prefix('auth')->group(function () {
    // Public auth routes
    Route::post('/login', [UserController::class, 'verifyToken']);
    Route::post('/register', [UserController::class, 'store']);
    Route::post('/send-password-reset', [UserController::class, 'sendPasswordResetEmail']);

    // Protected auth routes (require Firebase token)
    Route::middleware('firebase.auth')->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}',  [UserController::class, 'update']);
            Route::delete('/{id}',  [UserController::class, 'destroy']);
        });
        Route::post('/send-email-verification', [UserController::class, 'sendEmailVerification']); // FIXME: NEED UI
    });
});

// Protected routes (require Firebase authentication)
Route::middleware('firebase.auth')->group(function () {
    Route::get('/user-sanctum', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'firebase_uid' => $request->firebase_uid
        ]);
    });

    // Laravel matches routes top-to-bottom, move specific routes above the `apiResource`

    /**
     * Members specific routes
     */
    Route::prefix('members')->group(function () {
        Route::get('/{id}/check', [MemberController::class, 'checkMemberRegistration']);
    });
    Route::apiResource('members', MemberController::class);

    /**
     * Events specific routes
     */
    Route::prefix('events')->group(function () {
        Route::post('/{id}/register', [EventsController::class, 'registerMember']);
        Route::delete('/{id}/unregister/{memberId}', [EventsController::class, 'unregisterMember']);
        Route::get('/{id}/members', [EventsController::class, 'getEventMembers']);
    });
    Route::apiResource('events', EventsController::class);
});

// Legacy Sanctum route
Route::get('/sanctum-user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
