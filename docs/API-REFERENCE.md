# CCSync API Reference

The **CCSync API** follows a modular RESTful structure, with each resource corresponding to a specific functional area within the Student Body Organization system — such as users, members, events, and requirements.

All endpoints are prefixed with:

```http
/api/
```

## Controller documentation

Refer to the controller-level API docs for detailed request/response examples and field lists:

- [User](controllers/UserController.md)
- [Member](controllers/MemberController.md)
- [Event](controllers/EventController.md)
- [Requirement](controllers/requirement)

For example:
```http
GET http://localhost:8000/api/users
```

> ⚠ All authenticated request must include **Authorization header**. Most routes are protected by Firebase authentication middleware (`firebase.auth`).
> ```http
> Authorization: Bearer <firebase_id_token>
> ```

## Postman Collection (Pre-defined requests for TESTING)

**CLICK HERE:** https://myteam-0238.postman.co/workspace/CCSync~2330857d-5da0-4a20-8bed-e9a8772a75f0/collection/33039172-1d7301c4-c612-4333-9293-edf408f6bedf?action=share&creator=33039172&active-environment=33039172-cff55443-3a23-4f31-b1fe-494377258696
