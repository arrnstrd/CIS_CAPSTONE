# Invitation Integration

Connect the invitation system to pending-user creation.

When an Admin creates a new managed user:

1. Create the user as `pending`.
2. Do not assign a usable password.
3. Generate an invitation.
4. Persist the invitation.
5. Send the invitation email.

Teacher-created User accounts that use the same user-creation lifecycle should also follow the invitation principle.

Do not change unrelated Teacher behavior.

Use transactions where appropriate so user creation does not leave inconsistent invitation state.

If email sending behavior requires a decision about synchronous vs queued delivery, inspect the existing project convention first. Do not introduce unnecessary infrastructure.