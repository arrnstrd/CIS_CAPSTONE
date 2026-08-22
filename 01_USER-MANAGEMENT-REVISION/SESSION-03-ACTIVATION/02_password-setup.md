# Password Setup

## Objective

Implement the password setup flow using the existing invitation system.

## Required Flow

GET /setup/{token}

1. Validate token through InvitationService.
2. Reject invalid tokens.
3. Reject expired tokens.
4. Reject used tokens.
5. Display password setup form only for valid invitations.

POST /setup/{token}

1. Validate token again.
2. Validate password and confirmation.
3. Apply the existing password requirements.
4. Hash the password using Laravel's password hashing.
5. Set the user's password.
6. Change status from PENDING to ACTIVE.
7. Mark the invitation token as used.
8. Redirect appropriately after successful activation.

## Security Requirements

- Never store plaintext passwords.
- Never allow an expired token to activate an account.
- Never allow a used token to activate an account.
- Never allow an invalid token to activate an account.
- Invitation tokens remain single-use.
- Do not create a second token mechanism.

## Transaction

Where appropriate, password update, status activation, and token
consumption should be handled atomically.