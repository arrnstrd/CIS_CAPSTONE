# Session 1 — Scope

## IMPLEMENT IN THIS SESSION

### 1. User Account States

Prepare the User model/database to support:

- pending
- active
- inactive

The existing `suspended` status must NOT be removed automatically.

First inspect whether `suspended` is used elsewhere.

If it is used, preserve it.

If it is unused, report that finding rather than inventing a new behavior.

### 2. Admin User Creation

Admin-created users must:

- have first name
- have last name
- have email
- have role
- start as `pending`
- NOT receive a default password

The creation request must NOT require:

- password
- password_confirmation

A newly created pending account must not have a usable password.

### 3. Teacher User Creation

The existing Teacher creation flow currently assigns a default/hardcoded password.

Remove that behavior.

Teacher creation must not assign a default password.

Preserve the rest of the Teacher functionality.

Do NOT redesign Teacher functionality.

### 4. User Management

The backend must support:

- View User
- Edit User
- Deactivate Account
- Reactivate Account

Normal User Management must NOT permanently delete users.

Preserve the existing soft-delete/archive behavior if it already exists.

### 5. Authentication Status

Authentication must respect account status:

- `active` → may log in
- `pending` → may not log in
- `inactive` → may not log in
- `suspended` → preserve existing behavior

Do not rewrite the authentication system.

Preserve:

- session authentication
- rate limiting
- login logging
- existing role redirection

### 6. Tests

Add/update only tests directly related to Session 1.

At minimum cover:

- pending user creation
- no usable password on new user
- correct role
- Teacher creation does not assign a default password
- pending user cannot log in
- inactive user cannot log in
- reactivated user can log in

---

# DO NOT IMPLEMENT IN SESSION 1

Do NOT implement:

- invitation tokens
- invitation_tokens table
- invitation service
- invitation email
- invitation mailable
- invitation resend
- password setup page
- password setup controller
- password setup routes
- token validation
- token expiration
- token invalidation
- password setup activation
- Primary Admin protection unless the existing codebase already has an authoritative identification mechanism
- Scanner Operator redesign
- UI/UX redesign
- unrelated refactoring
- unrelated tests

These belong to later work.

---

# HARD CONSTRAINTS

1. Do not invent requirements.
2. Do not restructure the application unnecessarily.
3. Do not introduce Sanctum or Passport.
4. Use the existing session-based authentication.
5. Do not create default passwords.
6. Do not send passwords through any account-creation mechanism.
7. Do not modify Teacher behavior unrelated to this revision.
8. Do not remove existing roles.
9. Do not remove `suspended` without verifying its usage.
10. Do not assume Primary Admin = ID 1.
11. Do not assume Primary Admin = a specific email.
12. Do not create a new Primary Admin identification rule in this session.
13. Do not permanently delete users through normal User Management.
14. Do not implement Session 2 or Session 3 functionality.

---

# WORKING RULE

Inspect first.

Do not modify files immediately.

Before implementation, identify:

- current User model
- users migration(s)
- UserController
- TeacherController
- AuthService
- AuthController
- authentication middleware
- role middleware
- relevant routes
- existing User tests
- existing Teacher tests
- all references to user status
- all locations where a default password is assigned

Then implement only the scoped changes.