# Final Integration

## Objective

Verify the complete user lifecycle after Sessions 1–3.

## Expected Lifecycle

Admin/Teacher creates user
        ↓
PENDING
        ↓
Invitation generated
        ↓
Invitation email
        ↓
/setup/{token}
        ↓
Valid token
        ↓
Set password
        ↓
ACTIVE
        ↓
Login

## Integration Checklist

Verify:

- [ ] Pending users have no default password.
- [ ] Pending users cannot log in.
- [ ] Valid invitation allows password setup.
- [ ] Invalid invitation is rejected.
- [ ] Expired invitation is rejected.
- [ ] Used invitation is rejected.
- [ ] Password is securely hashed.
- [ ] Successful setup changes PENDING → ACTIVE.
- [ ] Invitation becomes used after successful setup.
- [ ] Used invitation cannot be reused.
- [ ] Active users can log in.
- [ ] Inactive users remain blocked.
- [ ] Suspended users remain blocked.
- [ ] Scanner Operator access remains restricted.
- [ ] Existing invitation/resend behavior remains intact.
- [ ] Existing user-management behavior remains intact.

## Scope

Do not introduce unrelated changes during integration.