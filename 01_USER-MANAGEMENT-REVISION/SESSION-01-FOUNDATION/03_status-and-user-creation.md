
# Session 1 — Status and User Creation

## Objective

Revise account creation so newly created managed accounts do not receive default passwords.

This session establishes the correct account state and creation behavior.

The invitation mechanism is NOT implemented in this session.

---

## Account States

The revised lifecycle requires a `pending` state.

Expected states relevant to this session:

- `pending`
- `active`
- `inactive`

The existing `suspended` status must be preserved if existing functionality depends on it.

Do not change the meaning of existing statuses without evidence from the codebase or requirements.

---

## Admin User Creation

Admin creates a user using:

- first name
- last name
- email
- role

Admin does NOT provide:

- password
- password_confirmation

### New Account State

After successful creation:

```text
status = pending
password = no usable password
````

The newly created account must not be able to authenticate.

---

## Default Password Removal

Inspect the entire relevant backend code for default-password creation.

Investigate patterns such as:

* hardcoded password strings
* `Hash::make(...)`
* `bcrypt(...)`
* `Str::random(...)` used as a password
* password assignment inside User creation services
* password assignment inside controllers
* password assignment inside model events
* TeacherController default password
* other production account-creation paths

Do not blindly remove passwords from unrelated fixtures, tests, or seeders.

Classify each occurrence as:

1. production account-creation logic
2. test fixture
3. development/database seeder
4. unrelated functionality

Only production account-creation behavior is in scope.

---

## Teacher Creation

The existing Teacher creation flow currently assigns a default/hardcoded password.

That behavior must be removed.

Teacher-created User accounts must not receive a default password.

The Teacher account should follow the same basic account-state principle:

```text
User created
    ↓
pending
    ↓
no usable password
```

The invitation/password-setup mechanism itself belongs to Session 2/3 and must NOT be implemented here.

### Preserve Existing Teacher Functionality

When modifying TeacherController or related code, preserve:

* Teacher record creation
* User/Teacher relationship
* database transaction handling
* existing validation
* update behavior
* destroy/deactivation behavior
* Teacher routes
* Teacher permissions
* Teacher middleware
* unrelated Teacher functionality

Do not redesign the Teacher module.

---

## User Creation Validation

User creation must NOT require:

* password
* password_confirmation

Remove password requirements from the creation flow only.

Do not unnecessarily remove password validation from:

* password changes
* existing authenticated password updates
* unrelated authentication functionality
* tests
* other flows

Password validation for the future invitation password-setup flow belongs to a later session.

---

## Password Storage

The new account must not have a usable password.

First inspect the existing:

* users migration
* User model
* password casts
* authentication implementation

Determine the safest implementation compatible with the existing schema.

Do NOT blindly change the password column to nullable if the existing architecture does not require it.

Do NOT invent a special unusable password hash unless necessary.

The requirement is:

> A newly created pending account must not have a usable password.

---

## Status Assignment

When an Admin creates a new managed user:

```text
role   = selected role
status = pending
password = no usable password
```

Do not automatically activate the account.

Do not generate an invitation token in this session.

Do not send an invitation email in this session.

---

## Existing Users

Do not blindly convert all existing users to `pending`.

Existing active/inactive/suspended accounts must remain consistent with their current state unless a migration is explicitly required by the existing architecture.

The new `pending` state is primarily for newly created accounts.

---

## Authentication Compatibility

The authentication system must recognize that a `pending` account is not an active account.

A pending account must not be allowed to log in.

However, the full invitation-based activation flow is not implemented in Session 1.

Session 1 should only establish the correct state behavior.

---

## Transaction Safety

Preserve existing transaction boundaries.

If Teacher creation currently uses:

```php
DB::transaction(...)
```

do not remove that transaction.

If Admin user creation already has transaction handling, preserve it.

Do not introduce unnecessary service/controller restructuring solely for this revision.

---

## Role Preservation

Existing roles remain:

* `admin`
* `teacher`
* `scanner_operator`

Do not introduce additional roles.

Do not rename existing roles unless the codebase already requires it.

---

## Primary Admin

Do NOT implement a new Primary Admin identification mechanism in this task.

Do NOT assume:

* Primary Admin = ID 1
* Primary Admin = `superadmin@cis.edu.ph`
* Primary Admin = first admin
* Primary Admin = first seeded user
* Primary Admin = first created account

If the codebase has an authoritative Primary Admin mechanism, document it in the audit and preserve it.

If no authoritative mechanism exists, record this as an unresolved issue in:

```text
06_session-result.md
```

Primary Admin protection may be handled in a later session once the identification mechanism is established.

---

## No Permanent Deletion

Normal User Management must not introduce a permanent-delete operation.

If the existing system already uses:

* SoftDeletes
* archive
* restore

preserve that behavior unless it directly conflicts with the finalized requirements.

Do not replace soft deletion with hard deletion.

---

## Completion Criteria

Session 1 user creation is considered complete when:

* [ ] New Admin-created users start as `pending`
* [ ] New users receive no usable password
* [ ] Password is not required during user creation
* [ ] Teacher creation no longer assigns a default password
* [ ] Existing roles remain intact
* [ ] Existing Teacher functionality remains intact
* [ ] Existing active users are not accidentally converted to pending
* [ ] Existing inactive/suspended behavior is preserved
* [ ] Pending accounts cannot authenticate
* [ ] No invitation-token implementation has been introduced
* [ ] No invitation email implementation has been introduced
* [ ] No password-setup flow has been introduced
* [ ] Relevant PHPUnit tests pass

---

## Explicit Session Boundary

STOP after completing this file's requirements.

Do NOT proceed to:

* invitation token generation
* invitation token storage
* invitation expiration
* invitation email
* invitation service
* resend invitation
* password setup
* password setup routes
* token validation
* account activation through invitation

Those belong to later sessions.

```
```
