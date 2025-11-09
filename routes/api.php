<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;
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

Route::post('/test/login', [UserController::class, 'login']);

// Firebase Authentication routes
Route::prefix('auth')->group(function () {
    // Public auth routes
    Route::post('/login', [UserController::class, 'verifyToken']);
    Route::post('/register', [UserController::class, 'store']);
    Route::post('/send-password-reset', [UserController::class, 'sendPasswordResetEmail']);

    Route::post('/send-email-verification', [UserController::class, 'sendEmailVerification'])
        ->middleware('firebase.auth'); // FIXME: NEED UI
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
     * Users specific routes (rare)
     */
    Route::prefix('users')->group(function () {
        Route::get('/user', [UserController::class, 'getUserById']);
    });
    Route::apiResource('users', UserController::class);

    /**
     * Profile specific routes
     */
    Route::prefix('profile')->group(function () {
        Route::put('/{id}/editProfileInfo', [UserController::class, 'updateProfileInformation']);
        Route::put('/{id}/editPersonal', [UserController::class, 'updatePersonal']);
        Route::put('/{id}/editPassword', [UserController::class, 'updatePassword']);
    });

    /**
     * Members specific routes
     */
    Route::prefix('members')->group(function () {
        Route::get('/list', [MemberController::class, 'getMembersPagination']);
        Route::get('/member', [MemberController::class, 'getMember']);
        Route::get('/{id}/check', [MemberController::class, 'checkMemberRegistration']);
    });
    Route::apiResource('members', MemberController::class);

    Route::middleware(['permission:promote members|promote officers'])->group(function () {
        Route::prefix('role')->group(function () {
            Route::post('/{id}/promote', [MemberController::class, 'promoteMember']);
            Route::post('/{id}/demote', [MemberController::class, 'demoteOfficer']);
        });
    });

    /**
     * Events specific routes
     */
    Route::prefix('events')->group(function () {
        Route::post('/{id}/add', [EventController::class, 'registerMember']);
        Route::delete('/{id}/delete/{memberId}', [EventController::class, 'unregisterMember']);
        Route::get('/{id}/members', [EventController::class, 'getEventMembers']);
    });
    Route::apiResource('events', EventController::class);
});

// Legacy Sanctum route
Route::get('/sanctum-user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
