# Session 3 — Scope

## In Scope

1. Password setup using invitation tokens
2. Account activation
3. Authentication status enforcement
4. Primary Admin protection audit
5. Scanner Operator access verification
6. Final integration audit

## Out of Scope

Do NOT:
- redesign the invitation system
- create another token system
- create another invitation table
- add default passwords
- modify student functionality
- modify attendance functionality
- modify QR functionality
- redesign scanner functionality
- invent Primary Admin identification
- hardcode Primary Admin ID
- hardcode Primary Admin email
- perform unrelated refactoring

## Testing

Do not repeatedly execute PHPUnit while the external Supabase
database is unavailable.

Static and syntax checks are allowed.

Full testing will happen in a separate final testing phase.