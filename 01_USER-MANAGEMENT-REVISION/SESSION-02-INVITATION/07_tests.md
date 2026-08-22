# Session 2 Tests

Add focused tests for the invitation system.

Prioritize:

1. Pending user receives an invitation.
2. Invitation record is created.
3. Invitation has approximately/exactly 72-hour expiration according to the application's time handling.
4. Invitation token is not exposed through stored plaintext data if hashed storage is used.
5. Invitation email contains the setup link.
6. Invitation email does not contain a password.
7. Expired invitation is invalid.
8. Used/invalidated invitation is invalid.
9. Resending invalidates the old invitation.
10. Resending generates a new token.
11. Resending gives the new invitation a fresh 72-hour expiration.
12. Non-admin users cannot resend invitations.
13. Non-pending users cannot be resent invitations.

Important:

Run focused tests first.

Do NOT run the entire test suite blindly if it is known to hang or become expensive.

If a test hangs, stop it and report which test/command caused the problem.

Do not modify unrelated tests simply to make the test suite pass.