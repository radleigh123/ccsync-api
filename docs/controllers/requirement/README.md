
# Requirements workflow

1. Officer/Admin creates a Requirement and one or more Offerings (one offering per semester).
2. Students fetch available Offerings and view requirement details.
3. Student submits a form (with files) which creates a Compliance record and one or more ComplianceDocument entries.
4. Officer/Admin reviews the Compliance. When the decision is made, create a ComplianceAudit entry and update the Compliance status (verified/rejected, etc.).
5. If a Requirement is set to `is_active = false`, associated Offerings should be set to `active = false` as well — this requires a background job or model observer to update offerings in production (DEVELOPMENT IN PROGRESS).

## Notes and implementation guidance

- Max submissions: the `offerings.max_submissions` field controls how many attempts a student can make (controller enforces this when creating a new compliance attempt).
- Attempt tracking: `compliances.attempt` should be incremented for retries; uniqueness is enforced by (`offering_id`, `member_id`, `attempt`) per migrations.
- File storage: store files outside the webroot (use configured storage disk). Save `file_path` and `mime` in `compliance_documents` and consider adding virus scanning/validation.
- Requirement deactivation: If an admin sets `requirements.is_active = false`, you should mark related `offerings.active = false`. IN-DEVELOPMENT (if naay time hehe):
	- a job for asynchronous, large-scale updates so the request doesn't block (and add retries/monitoring).

## Security and validation

 - Officer/Admin actions must be further protected by role/permission (Spatie) checks.
 - Validate uploaded file types and sizes in the controller before saving. Enforce per-offering allowed mime types if applicable.

## Example end-to-end sequence (concise)

1. Admin POST /requirements -> returns requirement id.
2. Admin POST /offerings (with requirement_id) -> returns offering id.
3. Student GET /offerings -> sees offering (active and within open/close dates).
4. Student POST /compliances (multipart/form-data with files) -> creates compliance + compliance_documents.
5. Admin reviews -> PUT /compliances/{id} to set status to `verified` or `rejected` and server creates `compliance_audits` entry.
