# Resend Invitation

Implement Admin-only resend invitation functionality.

Requirements:

- Only pending users can receive/resend invitations.
- Existing invitation must be invalidated.
- A completely new token must be generated.
- New invitation expiration must be exactly 72 hours from resend.
- Old token must immediately become unusable.
- New invitation email must be sent.
- No password is generated.

Do not extend the old token.

Do not reuse the old token.

Follow the existing Admin authorization and route conventions.