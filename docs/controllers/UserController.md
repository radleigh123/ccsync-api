# User Controller

This document describes the authentication and user management endpoints handled by `UserController`.

Model fields (users table):

| Field | Type | Notes |
| --- | --- | --- |
| id | integer | Primary key |
| display_name | string | required |
| email | string | unique, required |
| email_verified | boolean | nullable |
| firebase_uid | string|null | nullable, unique — maps Firebase user |
| password | string | hashed password |
| remember_token | string|null | nullable |
| created_at / updated_at | timestamps | |

## Endpoints

| METHOD | ENDPOINT | PARAMS (required/optional) | Description |
| --- | --- | --- | --- |
| POST | /auth/login | id_token (body, required) | Verify Firebase ID token and return local user info |
| POST | /auth/register | display_name (required), email (required), password (required), password_confirmation (required), id_school_number (optional) | Create Firebase user + local user |
| POST | /auth/send-password-reset | email (body, required) | Send password reset link via Firebase |
| POST | /auth/send-email-verification | id_token (body, required) | Send email verification link (protected) |
| GET | /users | — | List all users (protected)
| GET | /users/{id} | id (path, required) | Get user by id (protected)
| PUT | /users/{id} | email (optional), display_name (optional) | Update user (protected)
| DELETE | /users/{id} | — | Delete user (protected)
| PUT | /profile/{id}/editProfileInfo | display_name (optional), biography (optional) | Update profile information (protected)
| PUT | /profile/{id}/editPersonal | email (optional), phone (optional), gender (optional) | Update personal info (protected)
| PUT | /profile/{id}/editPassword | current_password (body, required), password (body, required), password_confirmation (body, required) | Change user password (protected)

### POST /auth/login

Verifies a Firebase ID token and returns the corresponding local user.

Request body:

```json
{
  "id_token": "<firebase_id_token>"
}
```

Successful response (200):

```json
{
  "user": {
    "id": 3,
    "display_name": "Administrator",
    "email": "user@example.com",
    "firebase_uid": "bT1o...",
    "email_verified": true,
  },
  "firebase_claims": { /* token claims object */ }
}
```

Errors:

- 401: invalid/expired token
- 422: missing `id_token`

### POST /auth/register

Create a new Firebase user and a local User record. The controller will validate fields and create the Firebase account first, then the local user.

Request body (example):

```json
{
  "display_name": "SUPER ADMIN",
  "email": "johndoe@example.com",
  "password": "123456",
  "password_confirmation": "123456",
}
```

Successful response (201):

```json
{
  "user": {
    "id": 234,
    "display_name": "SUPER ADMIN",
    "email": "johndoe@example.com",
    "firebase_uid": "GWHbKiX...",
    "email_verified": false
  }
}
```

Validation error example (422):

```json
{
  "message": "Validation failed",
  "errors": {
    "display_name": ["The display name field is required."],
    "email": ["The email has already been taken."],
    "password": ["The password field confirmation does not match."]
  }
}
```

### GET /users

List all users (protected). Response contains minimal user fields by default.

Response (200):

```json
{
  "users": [
      {
          "id": 1,
          "display_name": "Student Student",
          "email": "localstudent@student.com",
          "role_names": [
              "student"
          ]
      },
      {
          "id": 2,
          "display_name": "Officer Officer",
          "email": "localofficer@officer.com",
          "role_names": [
              "officer"
          ]
      },
      ...
  ]
}
```

## Profile

### PUT /profile/{id}/editProfileInfo

Change display name and biography.

Request body:

```json
{
  "display_name": "DISPLAY.NAME",
  "biography": "biography paragraph",
}
```

Response (200):

```json
{
  "success": true,
  "message": "User & Member updated successfully",
  "user": { /* updated user */ },
  "member": { /* updated member */ }
}
```

### PUT /profile/{id}/editPersonal

Change email, phone, and gender.

Request body:

```json
{
  "email": "email@example.com",
  "phone": "+631234567890",
  "gender": "other"
}
```

Response (200):

```json
{
  "success": true,
  "message": "User & Member updated successfully",
  "user": { /* updated user */ },
  "member": { /* updated member */ }
}
```

Conflict Error (409):

```json
{
  "success": false,
  "message": "Failed to update Firebase user",
  "error": "The email address is already in use by another account."
}
```

### PUT /profile/{id}/editPassword

Change local password and on Firebase.

Request body:

```json
{
  "current_password": "oldpass",
  "password": "newpass",
  "password_confirmation": "newpass"
}
```

Response (200):

```json
{
  "success": true,
  "message": "Password updated successfully",
  "user": { /* updated user */ }
}
```

Unauthorized Error (401):

```json
{
  "success": false,
  "message": "Current password is not correct"
}
```
