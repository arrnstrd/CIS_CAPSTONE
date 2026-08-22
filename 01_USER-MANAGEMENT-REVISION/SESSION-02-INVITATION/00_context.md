# Session 2 Context — Invitation System

This session continues the Backend User Management Revision for the CIS Laravel capstone.

Session 1 is complete.

Session 1 established:
- New users are created with `pending` status.
- Admin-created users do not receive a default password.
- Teacher-created users no longer receive the hardcoded `Password123`.
- Pending users cannot log in.
- Existing Teacher functionality should remain unchanged except for the required removal of default-password creation.
- Password setup routes may already exist, but the actual invitation/password setup implementation belongs to later work.

Read the Session 1 result before making changes:
`SESSION-01-FOUNDATION/06_session-result.md`

The project uses Laravel Sail/Docker.

Do not assume the previous AI's implementation is perfect. Inspect the current code before changing anything.