# CCSync API - Copilot Instructions

## Project Overview
This is a Laravel 12 API backend for CCSync (likely a synchronization system) that uses Firebase Authentication as its primary auth system alongside Laravel's local database for user management. The API serves as a bridge between Firebase Auth and a traditional Laravel backend.

## Architecture Pattern: Firebase-First Authentication

**Critical**: This project uses Firebase Authentication as the primary auth mechanism, NOT Laravel's built-in auth.

### Authentication Flow
- All API routes are protected with `firebase.auth` middleware (see `app/Http/Middleware/FirebaseAuthMiddleware.php`)
- Firebase ID tokens are required via `Authorization: Bearer <token>` or `Firebase-Token` header
- The middleware automatically creates/syncs local User records when Firebase users authenticate
- Users have both `firebase_uid` and local database `id` fields

### Key Files for Auth Integration
- `app/Http/Controllers/FirebaseAuthController.php` - Main auth controller (411 lines)
- `app/Http/Middleware/FirebaseAuthMiddleware.php` - Token verification & user sync
- `routes/api.php` - Route structure with Firebase middleware groups
- `config/firebase.php` - Firebase service configuration

## User Model Extensions

The User model includes custom fields beyond Laravel defaults:
```php
// Additional fillable fields in app/Models/User.php
'firebase_uid',      // Links to Firebase user
'id_school_number',  // Unique school identifier
'role',             // enum: 'user', 'admin', 'guest'
```

## API Route Structure

Routes are organized with clear middleware boundaries:

```php
// Public routes
Route::get('/', ...)          // API health check
Route::get('/ping', ...)      // Simple ping/pong

// Firebase auth routes (mixed public/protected)
Route::prefix('auth')->group(function () {
    // Public: login, register, verify-token, send-password-reset
    Route::middleware('firebase.auth')->group(function () {
        // Protected: get user, email verification, account deletion
    });
});

// All other routes require firebase.auth middleware
Route::middleware('firebase.auth')->group(function () {
    // User management routes
});
```

## Development Workflow

### Quick Start Commands
```bash
# Development server (via composer script)
composer run dev                # Equivalent to: php artisan serve

# Testing (uses Pest, not PHPUnit)
composer run test              # Runs: php artisan config:clear && php artisan test
./vendor/bin/pest              # Direct Pest execution

# Database setup
php artisan migrate           # Uses SQLite by default (see composer.json post-install)
```

### Testing Framework: Pest
- Uses Pest testing framework instead of PHPUnit
- Test configuration in `tests/Pest.php`
- Feature tests extend `Tests\TestCase::class`
- RefreshDatabase trait is commented out (line 15 in Pest.php)

## Dependencies & Integrations

### Firebase Integration
- `kreait/laravel-firebase: ^6.1` - Firebase Admin SDK
- Firebase config expects service account credentials
- Firebase project name from `FIREBASE_PROJECT` env var (defaults to 'app')

### Modern Laravel Features
- Laravel 12 with PHP 8.5+ requirement
- Laravel Sanctum included but legacy (see routes/api.php line 54)
- Uses SQLite database by default for development
- Laravel Pint for code styling

## Database Schema Notes

Users table has Firebase-specific fields:
```php
$table->string('firebase_uid')->unique()->nullable();
$table->string('id_school_number')->unique()->nullable();
$table->enum('role', ['user', 'admin', 'guest'])->default('user');
```

## Code Patterns & Conventions

### Error Handling
- Controllers use structured JSON responses with message/errors format
- Firebase exceptions are caught and converted to appropriate HTTP responses
- Extensive logging in FirebaseAuthController (see line 28)

### Request Validation
- Standard Laravel validation with Validator facade
- Consistent error response format across controllers

### Service Integration
- Firebase service accessed via `Firebase::auth()` facade
- Auto-sync between Firebase users and local User models in middleware

## Development Tips

1. **Firebase Setup**: Ensure Firebase service account credentials are configured before testing auth routes
2. **Database**: Project uses SQLite by default - database file created automatically via composer scripts
3. **API Testing**: Use `/ping` endpoint to verify API is running without auth requirements
4. **User Sync**: Local users are auto-created when Firebase users first authenticate through the middleware
5. **Role Management**: Users have role-based access with 'user', 'admin', 'guest' enum values

## Related Frontend
This API pairs with a React frontend in the `ccsync-v1` directory (separate project in workspace) that handles Firebase Auth UI and token management.