# Session 2 Scope — Invitation System

## Goal

Implement the persistent invitation system for pending users.

The flow for this session is:

Admin creates user
→ user is `pending`
→ invitation is generated
→ invitation is stored securely
→ invitation email is sent
→ invitation remains valid for 72 hours

Password setup/activation is Session 3.

## In Scope

- Invitation storage
- Secure token generation
- Token expiration
- Single-use/invalidation support
- Invitation model
- Invitation service
- Invitation email/mailable
- Integration with user creation
- Resend invitation
- Tests specifically for the invitation system

## Out of Scope

Do NOT implement these in Session 2:

- Password setup
- Account activation after password setup
- Primary Admin protection
- Scanner Operator changes
- Authentication redesign
- Teacher functionality redesign
- UI/UX redesign
- Permanent deletion changes
- Sanctum/Passport/API authentication

## Important

Do not invent requirements.

Inspect the existing project first and follow its Laravel conventions.

If an architectural decision is genuinely ambiguous, document it rather than silently making a large unrelated change.