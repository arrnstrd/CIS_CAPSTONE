# Session 01 — Tests

## Purpose

Add or update PHPUnit tests for the User Management foundation implemented during Session 1.

Use PHPUnit.

Do not rewrite unrelated tests.

---

## Before Changes

Run the existing relevant tests first.

Inspect:

- User tests
- Authentication tests
- Authorization tests
- Teacher-related tests
- User Management tests, if they exist

Establish a baseline before modifying anything.

---

## Required Test Coverage

### User Creation

Verify:

- Admin can create a user.
- Newly created user has `pending` status.
- Newly created user does not have a usable password.
- Password is not required during normal user creation.
- Appropriate invitation record is created if invitation functionality is implemented in this session.

---

### User Editing

Verify:

- Admin can edit allowed user fields.
- Unauthorized roles cannot edit users.
- Password cannot be changed through normal User Management editing.

---

### Deactivation

Verify:

- Admin can deactivate an active user.
- Deactivated user's status becomes `inactive`.
- Deactivated user cannot log in.
- Existing user data remains intact.
- Primary Admin cannot be deactivated.

---

### Reactivation

Verify:

- Admin can reactivate an inactive user.
- Status becomes `active`.
- Reactivated user can log in using their existing password.

---

### Resend Invitation

Verify:

- Only pending users can receive a resend invitation.
- Resending invalidates the old token.
- A new token is generated.
- New token has a fresh 72-hour expiration.
- Old token cannot be used.
- Active users cannot receive invitation resends.

---

### Primary Admin

If an authoritative Primary Admin identification mechanism exists, test:

- Primary Admin cannot be deactivated.
- Primary Admin cannot be demoted.
- Primary Admin cannot have the Admin role removed.
- Primary Admin cannot be permanently deleted.

If no authoritative identification mechanism exists:

- Do NOT invent one solely for tests.
- Document the blocker in `06_session-result.md`.

---

### Authorization

Verify:

- Admin can access User Management.
- Teacher cannot access Admin User Management.
- Scanner Operator cannot access Admin User Management.

---

### Teacher Regression

Run relevant existing Teacher tests.

The purpose is to ensure User Management changes did not unintentionally break Teacher functionality.

Do not rewrite Teacher tests unless a test genuinely fails because of the revised account lifecycle.

---

## Test Constraints

- PHPUnit only.
- Do not introduce Pest.
- Do not rewrite unrelated tests.
- Do not weaken existing security tests just to make the suite pass.
- Do not remove existing tests unless they are genuinely obsolete.
- If an old test expects a default password, update that specific test to reflect the invitation-based flow.

---

## Completion Criteria

Session 1 tests should demonstrate that:

1. User creation no longer depends on a default password.
2. Pending accounts cannot log in.
3. Active accounts can log in.
4. Inactive accounts cannot log in.
5. Admin-only User Management remains protected.
6. Deactivation/reactivation works.
7. Invitation resend invalidates the old invitation.
8. Teacher functionality remains intact.
9. Scanner Operator restrictions remain intact.
10. Primary Admin protection is tested if its identification mechanism is available.