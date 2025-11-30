<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Requirement\ComplianceController;
use App\Http\Controllers\Requirement\OfferingController;
use App\Http\Controllers\Requirement\RequirementController;
use App\Models\Compliance;
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
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'store']);

    Route::post('/send-password-reset', [UserController::class, 'sendPasswordResetEmail']);
    Route::post('/send-email-verification', [UserController::class, 'sendEmailVerification']); // FIXME: NEED UI
    Route::get('/verify-email', [UserController::class, 'verifyEmail']);
});

// Protected routes (require Firebase authentication)
Route::middleware('firebase.auth')->group(function () {
    Route::get('/user-sanctum', function (Request $request) {
        return response()->json([
            'user'              => $request->user(),
            'hasStudentRole'    => $request->user()->hasRole('student'),
        ]);
    });

    // Laravel matches routes top-to-bottom, move specific routes above the `apiResource`

    /**
     * Users specific routes (rare)
     */
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });
    Route::get('/user', [UserController::class, 'findUserSchoolId']);

    // TODO: Custom error role message
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/userz', [UserController::class, 'index']);
    });

    /**
     * Role specific routes
     */
    Route::prefix('roles')->group(function () {
        Route::prefix('permissions')->group(function () {
            Route::get('/{permission}', [PermissionController::class, 'show']);
        });
        Route::apiResource('permissions', PermissionController::class);

        Route::get('/{role}', [RoleController::class, 'show']);
    });
    Route::apiResource('roles', RoleController::class);

    /**
     * Profile specific routes
     */
    Route::prefix('profile')->group(function () {
        Route::put('/{memberId}/edit', [ProfileController::class, 'update']);
        Route::put('/{memberId}/editPassword', [ProfileController::class, 'updatePassword']);
    });

    /**
     * Members specific routes
     */
    Route::prefix('members')->group(function () {
        Route::get('/list', [MemberController::class, 'getMembersPagination']);
        Route::get('/member', [MemberController::class, 'getMember']);
        Route::get('/{memberId}/check', [MemberController::class, 'checkMemberRegistration']);
        Route::get('/search', [MemberController::class, 'searchMembers']);

    });
    Route::apiResource('members', MemberController::class);

    Route::middleware(['permission:promote members|promote officers'])->group(function () {
        Route::prefix('role')->group(function () {
            Route::post('/{memberId}/promote', [MemberController::class, 'promoteMember']);
            Route::post('/{memberId}/demote', [MemberController::class, 'demoteOfficer']);
        });
    });

    /**
     * Events specific routes
     */
    Route::prefix('events')->group(function () {
        Route::post('/{eventId}/add', [EventController::class, 'registerMember']);
        Route::delete('/{eventId}/delete', [EventController::class, 'unregisterMember']);
        Route::get('/{eventId}/members', [EventController::class, 'getEventMembers']);
    });
    Route::apiResource('events', EventController::class);

    /** 
     * Requirement specific routes
     */
    Route::prefix('compliances')->group(function () {
        Route::post('/{id}/submit', [Compliance::class, 'store'])
            ->middleware('role:student');

        Route::post('/{id}/verify', [Compliance::class, 'update'])
            ->middleware('role:officer');
    });
    Route::apiResource('compliances', ComplianceController::class);
    Route::prefix('requirements')->group(function () {
        Route::get('/list', [RequirementController::class, 'getPagination']);
    });
    Route::apiResource('requirements', RequirementController::class);
    Route::apiResource('offerings', OfferingController::class);

    /**
     * Officer
     */
    Route::prefix('officers')->group(function () {
        Route::get('/', [MemberController::class, 'getOfficersInOrder']);
    });
});

// Legacy Sanctum route
Route::get('/sanctum-user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
