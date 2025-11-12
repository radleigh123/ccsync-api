# Member Controller

Member endpoints and payloads. Model fields (members table):

| Field | Type | Notes |
| --- | --- | --- |
| id | integer | Primary key |
| user_id | integer | foreign to users.id |
| first_name | string | required |
| middle_name | string | required |
| last_name | string (nullable) | optional |
| suffix | string (nullable) | optional |
| id_school_number | unsigned integer | unique, required |
| birth_date | date | required |
| enrollment_date | date | required |
| program | string | references programs.code |
| year | tinyint | 1..4 |
| is_paid | boolean | default false |
| gender | enum | see App\\Enums\\Gender |
| biography | text (nullable) | optional |
| phone | string (nullable) | unique, optional |
| semester_id | integer (nullable) | nullable foreign key |
| created_at / updated_at | timestamps | — |

## Endpoints

| METHOD | ENDPOINT | PARAMS (required/optional) | Description |
| --- | --- | --- | --- |
| GET | /members | query: page, per_page, id_school_number (optional) | List members (protected) |
| POST | /members | member payload (see below) | Create a new member (protected) |
| GET | /members/{id} | id (path) | Get member by id (protected) |
| PUT | /members/{id} | member payload | Update member (protected) |
| DELETE | /members/{id} | — | Delete member (protected) |
| GET | /members/list | page, per_page, id_school_number | Paginated members or filter by id_school_number |
| GET | /members/member | id_school_number (query) | Get member by id_school_number |
| GET | /members/{id}/check | event_id (query) | Check if member is registered to event |
| GET | /programs | — | List programs for dropdown |
| POST | /role/{id}/promote | role (body) | Promote a member (requires roles/permissions) |
| POST | /role/{id}/demote | role (body) | Demote an officer (requires admin) |

### GET /members

Response (200):

```json
{
  members: [
    {
      "id": 1,
      "first_name": "Student Student",
      "middle_name": "Metz",
      "last_name": "Member",
      "suffix": null,
      "id_school_number": 20599993,
      "birth_date": "2006-03-28",
      "enrollment_date": "2025-08-16",
      "program": {
        "code": "BSIT",
        "name": "Bachelor of Science in Information Technology"
      },
      "year": 1,
      "is_paid": true,
      "gender": "other",
      "biography": "Reprehenderit minima iusto accusantium sit nulla natus.",
      "phone": "+1 (206) 915-9086",
      "created_at": "2025-11-09T15:43:25.000000Z",
      "updated_at": "2025-11-09T15:43:25.000000Z",
      "user": {
        "id": 1,
        "email": "localstudent@student.com",
        "role_names": [
          "student"
        ]
      }
    },
    ...
  ]
}
```
 
### POST /members
Create a member. The endpoint expects minimal required fields and will link to an existing user via `user_id` if provided.

Request (example):

```json
{
  "user_id": 12,             // an existing user
  "first_name": "John",
  "middle_name": "A.",
  "last_name": "Doe",
  "suffix": "Dr.",
  "id_school_number": 10001,
  "birth_date": "2004-01-01",
  "enrollment_date": "2022-08-01",
  "program": "BSIT",
  "year": 2,
  "is_paid": true
}
```

Response (201):

```json
{
  "message": "Member created successfully",
  "member": { /* local member */ },
}
```

 
### GET /members/list?page=1&per_page=10
Response (200):

```json
{
  "message": "Members retrieved successfully",
  "members": {
    "current_page": 1,
    "data": [
      { /* local member1 */ },
      { /* local member2 */ },
      ...
    ],
    "first_page_url": "http://localhost:8000/api/members/list?page=1",
    "from": 1,
    "last_page": 12,
    "last_page_url": "http://localhost:8000/api/members/list?page=12",
    "links": [
      {
          "url": null,
          "label": "&laquo; Previous",
          "page": null,
          "active": false
      },
      {
          "url": "http://localhost:8000/api/members/list?page=1",
          "label": "1",
          "page": 1,
          "active": true
      },
      ...
    ],
    "next_page_url": "http://localhost:8000/api/members/list?page=2",
    "path": "http://localhost:8000/api/members/list",
    "per_page": 20,
    "prev_page_url": null,
    "to": 20,
    "total": 234
  }
}
```

 
### POST /role/{id}/promote
Promote a member to a role. Requires appropriate permission.

Request:

```json
{
  "role": "officer"
}
```

Response (200):

```json
{
  "message": "Member promoted to officer successfully.",
  "member": { /* updated member with roles */ }
}
```
