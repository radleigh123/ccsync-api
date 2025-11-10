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

**General Conventions**

| Type              | Description                              |
| ----------------- | ---------------------------------------- |
| Base URL          | `http://localhost:8000/api/`             |
| Format            | JSON request & response                  |
| Authentication    | Firebase Token (Bearer Token)            |
| HTTP Status Codes | Standards (200, 201, 400, 401, 404, 500) | 

All authenticated request must include **Authorization header**:
```http
Authorization: Bearer <firebase_id_token>
```

## User Management

IN PROGRESS
