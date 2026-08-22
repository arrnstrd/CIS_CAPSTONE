# Invitation Service

Create an InvitationService or follow the project's existing service-layer convention.

The service should own invitation logic instead of putting it directly in controllers.

It should handle:

- Generate invitation
- Secure token generation
- Token storage
- 72-hour expiration
- Token validation
- Token invalidation
- Resend invitation
- Sending the invitation email

Important rules:

- Never generate a password.
- Never include a password in an invitation.
- Never reuse an old invitation token.
- Resend must invalidate the old invitation first.
- A new invitation must receive a new token.
- Expired invitations must be rejected.
- Used/invalidated invitations must be rejected.

Keep controllers thin.

Do not implement password activation here. That belongs to Session 3.