# Invitation Email

Implement the invitation email/mailable using the project's existing Laravel mail conventions.

The email must contain:

- Recipient's name
- Invitation/setup link
- 72-hour expiration information
- Basic instructions to complete account setup

The email MUST NOT contain:

- Default password
- Generated password
- Plaintext password
- Any credential that allows direct login

The invitation link must contain the invitation token required by the Session 3 password setup flow.

Inspect existing Mailables before creating a new pattern.