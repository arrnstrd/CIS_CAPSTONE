# AI Guide Prompt – Security Hardening Implementation

**Copy and paste this prompt when assigning security hardening work to an AI.**

---

## Reading Order (MANDATORY – Read Before Any Implementation)

Before working on any security hardening task, read these files in this exact order:

1. **[docs/security-hardening/status/security_hardening_status.md](docs/security-hardening/status/security_hardening_status.md)** — Current implementation status and batch completion matrix
2. **[docs/implementation/security-hardening-plan.md](docs/implementation/security-hardening-plan.md)** — Full 6-batch plan with objectives, tasks, and acceptance criteria
3. **[docs/architecture/AI_GUIDE.md](docs/architecture/AI_GUIDE.md)** — General AI development guidelines for this project
4. **[docs/architecture/PROJECT_CONTEXT.md](docs/architecture/PROJECT_CONTEXT.md)** — Project overview
5. **[docs/architecture/BACKEND_STANDARDS.md](docs/architecture/BACKEND_STANDARDS.md)** — Backend code standards and folder structure

---

## Key Rules for Security Hardening Work

- **Do not jump batches.** Follow the sequential order: Batch 1 → 2 → 3 → 4 → 5 → 6.
- **Finish one batch completely before moving to the next.** Do not mix unrelated batch tasks.
- **Preserve existing behavior.** Security hardening must not break current login, attendance, QR, or grading flows.
- **Test before committing.** Every change must have corresponding regression tests.
- **Do not invent new roles or permission models.** Use the role constants defined in `app/Models/User.php`.
- **Refer to the status document first.** Before implementing, check [security_hardening_status.md](docs/security-hardening/status/security_hardening_status.md) to see what's already done vs. what's still needed.

---

## Batch Context

Use this reference to understand what each batch covers:

- **Batch 1** – Security Foundation (role/permission model, access matrix) → ✅ COMPLETE
- **Batch 2** – Route Protection (auth + role middleware) → ✅ COMPLETE
- **Batch 3** – Identity Hardening (password policy, lockout, session timeout) → ⚠️ PARTIAL
- **Batch 4** – MFA & Privileged Access (MFA, sensitive action protection) → ⚠️ IN PROGRESS
- **Batch 5** – Audit & Monitoring (security event logging) → ❌ NOT STARTED
- **Batch 6** – Production Hardening (HTTPS, headers, deployment) → ❌ NOT STARTED

**Current Focus:** Complete Batch 3 (Identity Hardening), then start Batch 4 (MFA).

---

## Implementation Checklist

When assigned a security task:

1. ✅ Read all files in the Reading Order section above
2. ✅ Identify which batch the task belongs to
3. ✅ Check [security_hardening_status.md](docs/security-hardening/status/security_hardening_status.md) for what's already done in that batch
4. ✅ Verify the task does not conflict with existing features (login flow, attendance, QR, grading)
5. ✅ Follow the batch acceptance criteria from [security-hardening-plan.md](docs/implementation/security-hardening-plan.md)
6. ✅ Write regression tests for every change
7. ✅ Run tests locally to verify behavior
8. ✅ Update [security_hardening_status.md](docs/security-hardening/status/security_hardening_status.md) with completion status when done

---

## File Locations

| File                                                                                                                       | Purpose                                                                  |
| -------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------ |
| [docs/security-hardening/status/security_hardening_status.md](docs/security-hardening/status/security_hardening_status.md) | **Quick reference** – Current batch status and implementation summary    |
| [docs/implementation/security-hardening-plan.md](docs/implementation/security-hardening-plan.md)                           | **Full plan** – Detailed batch breakdown, tasks, and acceptance criteria |
| [docs/architecture/AI_GUIDE.md](docs/architecture/AI_GUIDE.md)                                                             | **Project guidelines** – General development standards and procedures    |
| [docs/architecture/BACKEND_STANDARDS.md](docs/architecture/BACKEND_STANDARDS.md)                                           | **Backend conventions** – Folder structure, naming, layer separation     |
| [app/Models/User.php](app/Models/User.php)                                                                                 | **Role definition** – Role constants and helper methods                  |
| [app/Http/Middleware/EnsureUserHasRole.php](app/Http/Middleware/EnsureUserHasRole.php)                                     | **Role enforcement** – Middleware for protecting routes by role          |
| [app/Services/Administration/AuthService.php](app/Services/Administration/AuthService.php)                                 | **Login logic** – Throttling, audit logging, error handling              |
| [tests/Feature/PrivilegedAccessControlTest.php](tests/Feature/PrivilegedAccessControlTest.php)                             | **Regression tests** – Example tests for auth and privilege control      |

---

## Common Commands

Run these commands frequently to verify your changes:

```bash
# Run all auth-related regression tests
php artisan test --filter=PrivilegedAccessControlTest

# Run all tests
php artisan test

# Syntax check a file
php -l <filepath>

# Migrate and seed for testing
php artisan migrate:fresh --seed
```

---

## Questions or Blockers?

Before asking for clarification:

1. Check [security_hardening_status.md](docs/security-hardening/status/security_hardening_status.md) to confirm the current batch context
2. Review [security-hardening-plan.md](docs/implementation/security-hardening-plan.md) for acceptance criteria
3. Verify the requested change doesn't conflict with existing features
4. Check if the batch you're working on is actually the next priority

---

**Last Updated:** 2026-07-17
