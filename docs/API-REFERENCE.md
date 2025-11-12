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
- [Requirement](controllers/Requirement-RequirementController.md)
- [Offering](controllers/Requirement-OfferingController.md)
- [Compliance](controllers/Requirement-ComplianceController.md)

For example:
```http
GET http://localhost:8000/api/users
```

> ⚠ All authenticated request must include **Authorization header**. Most routes are protected by Firebase authentication middleware (`firebase.auth`).
> ```http
> Authorization: Bearer <firebase_id_token>
> ```
