# Session 3 — Context

## Purpose

Session 3 implements the account activation portion of the Backend User Management Revision.

Sessions 1 and 2 are already completed.

Session 1 established:
- PENDING user status
- No default passwords
- Admin-created users start as PENDING
- Teacher-created users start as PENDING
- Pending users cannot log in

Session 2 established:
- Invitation token storage
- InvitationToken model
- InvitationService
- Invitation email
- Invitation resend
- Invitation integration with user creation
- 72-hour token expiration
- Single-use invitation tokens
- SHA-256 token hashing

## Session 3 Goal

Complete the lifecycle:

PENDING
→ invitation
→ password setup
→ ACTIVE
→ login

## Important

Do not redesign or replace the invitation system from Session 2.

Use the existing:
- InvitationToken
- InvitationService
- InvitationMail
- invitation_tokens table

Testing is deferred to the final testing phase because the current
Supabase database connection is unavailable.