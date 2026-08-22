# Invitation Storage

Implement persistent invitation storage.

Requirements:

- Invitation must be stored server-side.
- Token must be cryptographically secure.
- Token must not be stored as plaintext if a secure hashed-token approach fits the existing architecture.
- Invitation must belong to a user.
- Invitation must have an expiration timestamp.
- Expiration must be exactly 72 hours from generation.
- Invitation must support invalidation/single-use tracking.
- Resending must invalidate the previous invitation.
- A new invitation must receive a completely new token and a fresh 72-hour expiration.

Before creating a migration:

1. Inspect existing token/password-reset infrastructure.
2. Determine whether an existing structure can safely support invitations.
3. Do not mix invitation and password-reset concerns unless the existing architecture clearly supports it.

Prefer a dedicated invitation structure if that is cleaner and safer.

Only create the columns actually required by the implementation.

Document the final schema in this file after implementation.