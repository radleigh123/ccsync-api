<?php

use App\Http\Controllers\FirebaseAuthController;
use App\Http\Controllers\UserController;
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
        // FIXME: NEED UI
        Route::post('/send-email-verification', [FirebaseAuthController::class, 'sendEmailVerification']);
        Route::delete('/delete-account', [FirebaseAuthController::class, 'deleteUser']);
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

    Route::get('/users', [UserController::class, 'getUserList']);

    Route::get('/users/id/{id}', [UserController::class, 'getUserById']);

    Route::put('/users/edit-user/id/{id}', [FirebaseAuthController::class, 'editUser']);
});

// Legacy Sanctum route
Route::get('/sanctum-user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
