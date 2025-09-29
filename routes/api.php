<?php

use App\Http\Controllers\EventsController;
use App\Http\Controllers\FirebaseAuthController;
use App\Http\Controllers\MemberController;
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

// Firebase Authentication routes
Route::prefix('auth')->group(function () {
    // Public auth routes
    Route::post('/login', [FirebaseAuthController::class, 'login']);
    Route::post('/register', [FirebaseAuthController::class, 'register']);
    Route::post('/verify-token', [FirebaseAuthController::class, 'verifyToken']);
    Route::post('/send-password-reset', [FirebaseAuthController::class, 'sendPasswordResetEmail']);

    // Protected auth routes (require Firebase token)
    Route::middleware('firebase.auth')->group(function () {
        Route::get('/user', [FirebaseAuthController::class, 'getUser']);
        Route::post('/send-email-verification', [FirebaseAuthController::class, 'sendEmailVerification']); // FIXME: NEED UI
        Route::delete('/delete-account', [FirebaseAuthController::class, 'deleteUser']);
        Route::prefix('user')->group(function () {
            Route::get('/', [FirebaseAuthController::class, 'getUserList']);

            Route::get('/{id}', [FirebaseAuthController::class, 'getUserById']);

            Route::put('/edit-user/{id}', [FirebaseAuthController::class, 'editUser']);
        });
    });
});

// Protected routes (require Firebase authentication)
Route::middleware('firebase.auth')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'firebase_uid' => $request->firebase_uid
        ]);
    });

    /**
     * '/' - GET/POST
     * '/{id}' - GET
     * '/{id}' - PUT/PATCH
     * '/{id}' - DELETE
     */
    Route::apiResource('member', MemberController::class);
    Route::apiResource('events', EventsController::class);

    /**
     * Events specific routes
     */
    Route::prefix('events')->group(function () {
        Route::post('/{id}/register', [EventsController::class, 'registerMember']);
        Route::delete('/{id}/unregister/{memberId}', [EventsController::class, 'unregisterMember']);
    });
});

// Legacy Sanctum route
Route::get('/sanctum-user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
