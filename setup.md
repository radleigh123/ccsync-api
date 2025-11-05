# CCSync API – Setup Guide

## 1. Prerequisites
- PHP 8.5+, Composer 2.6+ (How to install: [click here](https://laravel.com/docs/12.x/installation#:~:text=a%20Laravel%20Application-,Installing%20PHP%20and%20the%20Laravel%20Installer,-Before%20creating%20your))
- XAMPP

## 2. Clone & Install
```bash
git clone <repo-url>
cd ccsync-api
composer install
```

## 3. Environment Configuration
1. Duplicate `.env.example` → `.env`.
2. Set:
   - `APP_URL=http://localhost:8000`
   - `FIREBASE_CREDENTIALS="path/to/firebase-service-account.json"`
3. Generate keys: `php artisan key:generate`.

## 4. Firebase Setup
- Download the Firebase Admin SDK JSON from the console.
- Store it outside version control (e.g., `storage/app/firebase-service-account.json`) and point `FIREBASE_CREDENTIALS` to the absolute path.
- Ensure Firebase Authentication providers match expected login methods (email/password, etc.).

## 5. Database Prep
```bash
php artisan migrate
```
For other drivers, update `.env` and rerun migrations.

## 6. Run the API
```bash
composer run dev
```
API listens at `http://127.0.0.1:8000`. Health check: `GET /api/ping`.

## 7. Testing Workflow
```bash
composer run test    # wraps php artisan test with config clear
./vendor/bin/pest    # direct Pest execution
```

## 8. Auth Verification
- Obtain a Firebase ID token from the `ccsync-v1` frontend or Firebase SDK.
- Call protected routes with `Authorization: Bearer <token>` (middleware: `firebase.auth`).
- First successful call auto-syncs the user into `users` table with `firebase_uid`.

<br>

# Useful References
- https://laravel.com/docs/12.x/routing
- https://laravel.com/docs/12.x/eloquent
- https://firebase.google.com/docs/auth/android/manage-users#create_a_user
