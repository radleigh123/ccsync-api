# Requirement Controller

Requirement endpoints and payloads.

Model fields (requirements table):

| Field | Type | Notes |
| --- | --- | --- |
| id | integer | Primary key |
| name | string | required |
| description | text (nullable) | optional |
| type | enum | See App\Enums\Type |
| is_active | boolean | default true |
| semester_id | integer (nullable) | nullable foreign key |
| created_at / updated_at | timestamps | — |

## Endpoints

| METHOD | ENDPOINT | PARAMS (required/optional) | Description |
| --- | --- | --- | --- |
| GET | /requirements | — | List all requirements (protected) |
| POST | /requirements | name (required), description (optional), type (required), semester_id (optional) | Create requirement (protected) |
| GET | /requirements/{id} | id (path) | Get requirement by id |
| PUT | /requirements/{id} | payload | Update requirement |
| DELETE | /requirements/{id} | — | Delete requirement |

### POST /requirements

Request example:

```json
{
  "name": "Updated Grades",
  "description": "Submission of grades",
  "type": "document",
  "semester_id": 2
}
```

Response (201):

```json
{
  "requirement": { /* requirement object with semester relation */ }
}
```
