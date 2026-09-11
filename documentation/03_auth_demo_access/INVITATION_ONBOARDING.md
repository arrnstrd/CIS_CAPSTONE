# User Invitation & Account Onboarding Workflow

The **CIS Capstone** system implements a secure token-based invitation workflow for onboarding newly provisioned faculty and administrative staff.

---

## 1. Lifecycle Overview

When a Super Admin creates a new teacher or administrator account, the account is created in a pending state without a raw password. An invitation token is generated and emailed to the recipient.

```text
  [ Super Admin Creates Account ]
                 │
                 ▼
     [ InvitationService::generate() ]
                 │
                 ├─ Invalidate previous active tokens for user
                 ├─ Generate random 64-char plain token
                 ├─ Store SHA-256 hash in `invitation_tokens` table
                 └─ Set expiration (now + 7 days / 1 week)
                 │
                 ▼
       [ Send Invitation Email ]
        (App\Mail\InvitationMail)
                 │
                 ▼
   [ Recipient clicks link: /setup/{token} ]
                 │
                 ▼
       [ SetupController::show() ]
       (Renders resources/views/auth/setup.blade.php)
                 │
                 ▼
     [ User Sets Secure Password ]
                 │
                 ▼
    [ SetupController::complete() ]
                 │
                 ├─ Verify token & email match
                 ├─ Enforce password minimum 8 chars
                 ├─ Hash password with bcrypt
                 ├─ Mark token as used (`used_at = now()`)
                 └─ Activate User account status (`status = 'active'`)
                 │
                 ▼
       [ Redirect to Login ]
```

---

## 2. Security Guarantees

1. **Hash Storage:** The plaintext token is never stored in the database. Only its `SHA-256` digest is persisted in `invitation_tokens.token_hash`.
2. **7-Day (1-Week) Expiration:** Tokens are strictly valid for 7 days (168 hours) from generation.
3. **Single Use Invalidation:** Once used via `SetupController`, `used_at` timestamp is set, immediately invalidating the token for any subsequent attempts.
4. **Token Revocation:** Generating a new invitation token automatically invalidates any prior unused tokens for that user ID.
