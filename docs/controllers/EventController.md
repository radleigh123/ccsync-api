# Event Controller

Event endpoints and payloads. Model fields (events table):

| Field | Type | Notes |
| --- | --- | --- |
| id | integer | Primary key |
| name | string | required |
| description | `text|null` | optional |
| venue | string | required |
| event_date | date | required |
| time_from | time | required |
| time_to | time | required |
| registration_start | date | required |
| registration_end | date | required |
| max_participants | unsigned integer | required |
| status | enum | See App\Enums\Status |
| semester_id | `integer|null` | nullable foreign key |
| created_at / updated_at | timestamps | |

Event registrations (event_registrations table):

| Field | Type | Notes |
| --- | --- | --- |
| id | integer | Primary key |
| event_id | integer | foreign to events.id |
| member_id | integer | foreign to members.id |
| registered_at | timestamp | default current |

## Endpoints

| METHOD | ENDPOINT | PARAMS (required/optional) | Description |
| --- | --- | --- | --- |
| GET | /events | query: status, upcoming, current, open | List events (protected) |
| POST | /events | event payload (see below) | Create event (protected) |
| GET | /events/{id} | id (path) | Get event details (protected) |
| PUT/PATCH | /events/{id} | event payload | Update event (protected) |
| DELETE | /events/{id} | — | Delete event (protected) |
| POST | /events/{id}/add | member_id (body, required) | Register member to event |
| DELETE | /events/{id}/delete/{memberId} | — | Unregister member from event |
| GET | /events/{id}/members | page, per_page | Get members registered to event |

 
### GET /events
List events, supports filters.

Response (200):

```json
{
  "events": [
    {
      "id": 1,
      "name": "CCS Acquaintance Party",
      "description": "A casual gathering to get to know each other.",
      "venue": "Room 219",
      "event_date": "2024-07-01",
      "time_from": "10:00:00",
      "time_to": "15:00:00",
      "registration_start": "2024-06-01",
      "registration_end": "2024-06-30",
      "status": "open",
      "is_full": false,
      "is_registration_open": false,
      "due_days": -1,
      "max_participants": 150,
      "attendees": 51,
      "available_slots": 99,
      "created_at": "2025-11-09T15:43:30.000000Z",
      "updated_at": "2025-11-09T15:43:30.000000Z"
    },
    ...
  ]
}
```

 
### POST /events
Create an event.

Request (example):

```json
{
  "name": "Orientation",
  "venue": "Main Hall",
  "event_date": "2025-10-01",
  "time_from": "09:00",
  "time_to": "12:00",
  "registration_start": "2025-09-01",
  "registration_end": "2025-09-30",
  "max_participants": 100
}
```

Response (201):

```json
{
  "message": "Event created successfully",
  "event": { /* event object */ }
}
```

 
### POST /events/{id}/add
Register a member to an event.

Request:

```json
{
  "member_id": 45
}
```

Response (200):

```json
{
  "message": "Member registered successfully",
  "data": {
    "event": "Orientation",
    "member": "John Doe",
    "registered_at": "2025-09-24T12:34:56Z"
  }
}
```
