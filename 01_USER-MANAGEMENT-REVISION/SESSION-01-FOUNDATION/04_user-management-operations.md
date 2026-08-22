# Session 01 — User Management Operations

## Purpose

Implement the backend operations required for managing existing users.

This session covers:

- View users
- View a single user
- Edit user information
- Deactivate user
- Reactivate user
- Resend invitation

Do not implement the password setup flow in this file.

---

## Existing Behavior

Inspect the existing `UserController`, routes, middleware, models, requests, and tests before modifying anything.

Preserve existing functionality unless it directly conflicts with the finalized requirements.

---

## Required Account States

The system must distinguish between:

- `pending`
- `active`
- `inactive`

Do not invent additional account states.

If the existing system has `suspended`, inspect where it is used before changing or removing it.

---

## Required Operations

### 1. View User

Admin must be able to retrieve user information required by the User Management UI.

Include existing relevant information such as:

- Name
- Email
- Role
- Status
- Employee ID
- Invitation information, if available
- Last login, if already supported

Do not add unnecessary fields just for this feature.

---

### 2. Edit User

Admin must be able to edit appropriate user information.

At minimum, inspect whether these are already supported:

- First name
- Last name
- Email
- Role

Do NOT add password editing to normal user editing.

Password setup is handled separately through the invitation flow.

---

### 3. Deactivate User

Admin can deactivate an account.

Expected behavior:

- `active` → `inactive`
- User must no longer be able to log in.
- Existing user data must remain.
- Do not permanently delete the user.

Primary Admin protection must be enforced server-side.

---

### 4. Reactivate User

Admin can reactivate an inactive account.

Expected behavior:

- `inactive` → `active`
- User can log in again using their existing password.

Do not automatically generate or reset a password during reactivation.

---

### 5. Resend Invitation

Resend is only valid for users whose status is:

`pending`

When resending:

1. Invalidate the existing invitation.
2. Generate a completely new secure token.
3. Give the new token a fresh 72-hour expiration.
4. Send a new invitation email.
5. The old token must no longer work.

Never extend or reuse the previous token.

Resend must not be available for active users.

---

## Primary Admin Protection

The Primary Admin must never be:

- Deleted
- Permanently removed
- Deactivated
- Demoted
- Stripped of the Admin role

Protection must be enforced in backend business logic.

Do not rely only on UI restrictions.

Do NOT invent a Primary Admin identification mechanism.

If the existing codebase does not provide an authoritative way to identify the Primary Admin, document this as a blocker instead of hardcoding an email or ID.

---

## No Permanent Delete

Normal User Management must NOT provide a permanent-delete operation.

Existing soft-delete/archive behavior may remain if it is already part of the system, but do not introduce a new hard-delete path.

---

## Authorization

User Management operations must remain restricted to Admin users.

Use the existing authentication and role middleware.

Do not introduce:

- Sanctum
- Passport
- API token authentication

The project uses Laravel session-based authentication.

---

## Teacher Preservation

Do not modify Teacher permissions, Teacher routes, or Teacher-specific behavior unless required by the invitation/account lifecycle.

Admin management of Teacher accounts is allowed.

The Teacher's own functionality must continue working after these changes.

---

## Scanner Operator

Scanner Operators must remain restricted to their existing permitted functionality.

Do not broaden their access while implementing User Management.

---

## Implementation Constraints

Before changing code:

1. Inspect existing routes.
2. Inspect `UserController`.
3. Inspect existing validation/request classes.
4. Inspect User model.
5. Inspect existing authorization middleware.
6. Inspect related tests.

Follow existing project conventions.

Do not restructure unrelated code.

Do not modify UI/UX.

Do not create frontend components.

---

## Completion Criteria

This file is complete when:

- Admin can view users.
- Admin can view an individual user where required.
- Admin can edit users.
- Admin can deactivate users.
- Admin can reactivate users.
- Admin can resend invitations for pending users.
- Old invitation tokens become invalid when resent.
- Primary Admin protection exists or the missing identification mechanism is explicitly documented as a blocker.
- No hard-delete operation is introduced.
- Teacher functionality remains intact.
- Scanner Operator access remains restricted.