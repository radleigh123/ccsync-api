# Offering Controller

Offering endpoints and payloads.

Model fields (offerings table):

| Field | Type | Notes |
| --- | --- | --- |
| id | integer | Primary key |
| requirement_id | integer | foreign to requirements.id |
| semester_id | integer (nullable) | nullable foreign key |
| open_at | date (nullable) | optional |
| close_at | date (nullable) | optional |
| max_submissions | integer | default 1 |
| active | boolean | default true |
| created_at / updated_at | timestamps | — |

## Endpoints

| METHOD | ENDPOINT | PARAMS (required/optional) | Description |
| --- | --- | --- | --- |
| GET | /offerings | — | List all offerings (protected) |
| POST | /offerings | requirement_id (required), semester_id (optional), open_at (optional), close_at (optional), max_submissions (optional), active (optional) | Create offering (protected) |
| GET | /offerings/{id} | id (path) | Get offering by id |
| PUT | /offerings/{id} | payload | Update offering |
| DELETE | /offerings/{id} | — | Delete offering |

### POST /offerings

Request example:

```json
{
  "requirement_id": 4,
  "semester_id": 2,
  "open_at": "2025-09-01",
  "close_at": "2025-09-30",
  "max_submissions": 2,
  "active": true
}
```

Response (201):

```json
{
  "offering": { /* offering object with requirement relation */ }
}
```
