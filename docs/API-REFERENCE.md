# CCSync API Reference

The **CCSync API** follows a modular RESTful structure, with each resource corresponding to a specific functional area within the Student Body Organization system — such as users, members, events, and requirements.

All endpoints are prefixed with:
```http
/api/
```
For example:
```http
GET http://localhost:8000/api/users
```

> ⚠ All authenticated request must include **Authorization header**. Most routes are protected by Firebase authentication middleware (`firebase.auth`).
> ```http
> Authorization: Bearer <firebase_id_token>
> ```

---

## Authentication Routes (`/auth`)

| Method | Endpoint                        | Description                               | Auth Required |
| ------ | ------------------------------- | ----------------------------------------- | ------------- |
| `POST` | `/auth/login`                   | Verifies a Firebase token for user login  | ❌            |
| `POST` | `/auth/register`                | Registers a new user (from Firebase data) | ❌            |
| `POST` | `/auth/send-password-reset`     | Sends password reset email                | ❌            |
| `POST` | `/auth/send-email-verification` | Sends email verification link             | ✅            |

### POST `/auth/login`
Authenticate a user using a Firebase ID token.

##### Request Body
Inputs required/optional:
- `id_token` (required): Firebase ID token obtained after client signs in via `signInWithPassword`

```json
{
	"id_token": "<id_token>"
}
```

##### Response Body
```json
{
    "message": "Login successful",
    "user": {
        "id": 3,
        "display_name": "Administrator",
        "email": "keaneradleigh@gmail.com",
        "firebase_uid": "bT1oLwJqcZbR9uMbTjvMRA8EHcv2",
        "email_verified": true
    },
    "firebase_user": {
        "kind": "identitytoolkit#VerifyPasswordResponse",
        "localId": "bT1oLwJqcZbR9uMbTjvMRA8EHcv2",
        "email": "keaneradleigh@gmail.com",
        "displayName": "",
        "idToken": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjU0NTEzMjA5OWFkNmJmNjEzODJiNmI0Y2RlOWEyZGZlZDhjYjMwZjAiLCJ0eXAiOiJKV1QifQ.eyJpc3MiOiJodHRwczovL3NlY3VyZXRva2VuLmdvb2dsZS5jb20vY2NzeW5jLWVscGhwLTIwMjUiLCJhdWQiOiJjY3N5bmMtZWxwaHAtMjAyNSIsImF1dGhfdGltZSI6MTc2Mjc2NDM1MSwidXNlcl9pZCI6ImJUMW9Md0pxY1piUjl1TWJUanZNUkE4RUhjdjIiLCJzdWIiOiJiVDFvTHdKcWNaYlI5dU1iVGp2TVJBOEVIY3YyIiwiaWF0IjoxNzYyNzY0MzUxLCJleHAiOjE3NjI3Njc5NTEsImVtYWlsIjoia2VhbmVyYWRsZWlnaEBnbWFpbC5jb20iLCJlbWFpbF92ZXJpZmllZCI6ZmFsc2UsImZpcmViYXNlIjp7ImlkZW50aXRpZXMiOnsiZW1haWwiOlsia2VhbmVyYWRsZWlnaEBnbWFpbC5jb20iXX0sInNpZ25faW5fcHJvdmlkZXIiOiJwYXNzd29yZCJ9fQ.ovRJi7ceEm4x2j1r4m4f7uIR53zrff_4MfR6BWckfP2YrneOsrc4UA2e7o1zqbcQFO7p2cfdhEd9LngsLxitBVdyeg5oclX015nmAWr8L5OgfeYJVsl8Sv59hS-p2-hzprtBQJsFsf8RaCAEjMRk7_4E6U6en3wt1ZUy9sfVfb9G3FsWKH9P4cmoEysaSaZoOJpyNgC8DFoFxuscumPFKPdd5R3tNiEFUQe9MB2asY-d5BJulbrl0nVZ-1GY1C2vEj_A6IE4BZ4NF6aFoay5IGBLCy-7Eo352bVrOjb40g3gPqk7iQPT22ge8pxkHSITPs5qTLz1Uo68_gOvnJYrcA",
        "registered": true,
        "refreshToken": "AMf-vBzaXmSTwN3Wz1GNAH7JMNU2W-9ZpXjVILIe40Wr7r53kCz1apeYxJJgDiSlOEiOuID4g6DUrTXIGPkprGUFSISrd6iRKGvaIN-CkKFfkVT9VVrttFloniG3PQpigLTTVqlgS20UVKqOJjBXcTaGeD7w8QbozUE_MMd8la-R6vv-P5Jqf0-ro8CQvvR877V2Dj64AHBC5jneoRrYQRW-gcEp40CjbA",
        "expiresIn": "3600"
    }
```

### POST `/auth/register`
Registers a new user (from Firebase data).

##### Request Body
Inputs required/optional:
- `display_name` (required): A string username to be added to Firebase.
- `email` (required): User email
- `password` (required): User password
- `password_confirmation` (required): Exact user password

```json
{
	"display_name": "SUPER SUPER MAN",
	"email": "johndoe@example.com",
	"password": "123456",
	"password_confirmation": "123456"
}
```

##### Response Body
```json
{
    "user": {
        "id": 234,
        "display_name": "SUPER ADMIN",
        "email": "keaneradleigh123@gmail.com",
        "firebase_uid": "GWHbKiXrMRh5XcyooCFLLvQFChf1",
        "email_verified": false,
}
```

**Invalid Inputs**
```json
{
    "message": "Validation failed",
    "errors": {
        "display_name": [
            "The display name field is required."
        ],
        "email": [
            "The email has already been taken."
        ],
        "password": [
            "The password field confirmation does not match."
        ],
    }
}
```

### POST `/auth/send-password-reset` (INCOMPLETE)


### POST `/auth/send-email-verification` (INCOMPLETE)

---

## User Endpoints

Handles all operations related to user data such as registration, profile updates, and account retrieval.
Base Route: `/api/users`

| Method   | Endpoint      | Description                           | Middleware      |
| -------- | ------------- | ------------------------------------- | --------------- |
| `GET`    | `/users`      | Retrieve all users                    | `firebase.auth` |
| `GET`    | `/users/{id}` | Retrieve user by ID                   | `firebase.auth` |
| `PUT`    | `/users/{id}` | Update user details                   | `firebase.auth` |
| `DELETE` | `/users/{id}` | Delete a user (optional)              | `firebase.auth` |
| `GET`    | `/users/user` | Retrieve authenticated user via query | `firebase.auth` |

### GET `/users`
Retrieve all users.

##### Response Body
```json
{
	"users": [
		{
			"id": 1,
			"display_name": "john.doe",
			"email": "johndoe@example.com",
			"role_names": [
				"student"
			]
		},
		{
			"id": 2,
			"display_name": "mary.sae",
			"email": "marysae@example.com",
			"role_names": [
				"officer"
			]
		}
		...
	]
}
```

### GET `/users/{user_id}`
Retrieve a user by ID.

##### Response Body
```json
{
	"user": {
		"id": 423,
		"display_name": "john.doe",
		"email": "johndoe@example.com",
		"role_names": [
			"student"
		]
}
```

### PUT `/users/{user_id}`
Update a user by ID.

##### Request Body
- `email`: New user email.
- `display_name`: New display name.

```json
{
	"email": "admin@admin.com",
	"display_name": "Keane Radleigh"
}
```

##### Response Body
```json
{
	"success": true,
	"message": "User updated successfully"
}
```
