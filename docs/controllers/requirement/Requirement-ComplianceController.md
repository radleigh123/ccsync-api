# Compliance Controller

Compliance endpoints. A compliance is a member's submission to an offering.

Model fields (compliances table):

| Field | Type | Notes |
| --- | --- | --- |
| id | integer | Primary key |
| offering_id | integer | foreign to offerings.id |
| member_id | integer | foreign to members.id |
| status | enum | See App\Enums\RequirementStatus (pending/verified/rejected) |
| attempt | integer | submission attempt count |
| submitted_at | timestamp (nullable) | default current |
| verified_at | timestamp (nullable) | when verified |
| verified_by | unsigned big integer (nullable) | references members.id (officer who verified) |
| created_at / updated_at | timestamps | |

Also: `compliance_documents` and `compliance_audits` tables exist for uploaded files and status history.

## Endpoints

| METHOD | ENDPOINT | PARAMS (required/optional) | Description |
| --- | --- | --- | --- |
| GET | /compliances | — | List all compliances (protected) |
| POST | /compliances | offering_id (required), member_id (required), note (optional) | Submit compliance / requirement attempt |
| GET | /compliances/{id} | id (path) | Get compliance by id |
| PUT | /compliances/{id} | status (required for change), verified_at (optional), verified_by (optional) | Update compliance status (officer/admin) |
| DELETE | /compliances/{id} | — | Remove compliance |
| POST | /compliances/{id}/submit | offering_id, member_id | Alternate submission route |
| POST | /compliances/{id}/verify | verification payload | Verify submission (officer) |

 
### POST /compliances

Submit a compliance (student submission of requirement/offering).

Request example:

```json
{
  "offering_id": 12,
  "member_id": 45,
  "note": "Submitted via portal"
}
```

Response (201):

```json
{
  "compliance": {
    "id": 99,
    "offering_id": 12,
    "member_id": 45,
    "notes": "Submitted via portal",
    "attempt": 1,
    "submitted_at": "2025-09-24T12:34:56Z"
  }
}
```

 
### PUT /compliances/{id}

Officer/admin updates compliance status.

Request example:

```json
{
  "status": "verified",
  "verified_at": "2025-09-24T13:00:00Z",
  "verified_by": 3
}
```

Response (200):

```json
{
  "compliance": { /* updated compliance object */ }
}
```
