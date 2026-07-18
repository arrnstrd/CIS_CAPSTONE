# Enterprise Security Hardening Plan

## Objective
Implement enterprise-ready authentication and authorization for the school management system in a controlled, incremental way.

## Guiding Principles
- Do not introduce breaking changes without testing.
- Prefer additive changes over destructive rewrites.
- Protect routes by role and authentication state.
- Keep the implementation auditable and easy to review.
- Finish one batch fully before moving to the next.

## Batch Overview

### Batch 1 - Security Baseline and Scope Definition
Goal: define the security model and map protected areas.

Tasks:
- Review current authentication flow in AuthController and AuthService.
- Confirm supported roles: admin, teacher, scanner_operator, and any future roles.
- Identify which routes must require authentication.
- Identify which routes must require specific roles.
- Document the expected redirect behavior after login.

Deliverables:
- Security scope document.
- Route access matrix.
- Approval of role rules.

Acceptance Criteria:
- All major modules have a defined access expectation.
- No ambiguous route ownership remains.

### Batch 2 - Route Protection and Access Control
Goal: enforce authentication and role-based access consistently.

Tasks:
- Apply auth middleware to protected route groups.
- Apply role middleware to admin, teacher, scanner, and other role-specific pages.
- Ensure unauthenticated users are redirected to login.
- Ensure unauthorized users receive a 403 response.
- Fix broken or missing named routes used by redirects.

Deliverables:
- Protected route groups.
- Role-based middleware enforcement.
- Consistent login redirects.

Acceptance Criteria:
- Guests cannot access protected pages.
- Users cannot access pages outside their role scope.

### Batch 3 - Identity Hardening
Goal: improve login resilience and account safety.

Tasks:
- Enforce password complexity requirements.
- Add throttling and lockout handling for repeated failed attempts.
- Improve login error feedback without exposing sensitive details.
- Review session timeout and remember-me behavior.
- Ensure logout invalidates session properly.

Deliverables:
- Stricter auth rules.
- Better login failure handling.
- More secure session lifecycle behavior.

Acceptance Criteria:
- Weak passwords are rejected.
- Repeated failed attempts are throttled or suspended.
- Users are logged out securely.

### Batch 4 - MFA and Privileged Access Control
Goal: strengthen access for high-risk accounts.

Tasks:
- Add MFA support for admin and other privileged roles.
- Require MFA for sensitive actions.
- Review role escalation points.
- Restrict account creation and role updates to trusted users.

Deliverables:
- MFA workflow.
- Sensitive action protection.
- Privileged account controls.

Acceptance Criteria:
- High-risk accounts require extra verification.
- Sensitive actions are protected.

### Batch 5 - Audit Logging and Monitoring
Goal: make security events visible and reviewable.

Tasks:
- Log successful and failed authentication attempts.
- Log role changes, user status changes, and sensitive actions.
- Add basic monitoring hooks for unusual behavior.
- Prepare audit views or admin reporting for security logs.

Deliverables:
- Authentication audit trail.
- Admin-facing security monitoring data.

Acceptance Criteria:
- Security-related actions are recordable and reviewable.
- Suspicious activity can be investigated.

### Batch 6 - Production Hardening and Deployment Readiness
Goal: prepare the system for secure deployment.

Tasks:
- Enforce HTTPS-only behavior in production.
- Add security headers and cookie protections.
- Validate environment configuration for secrets and debug mode.
- Run regression tests for auth and authorization flows.
- Create deployment checklist and rollback guidance.

Deliverables:
- Production security checklist.
- Verified auth test coverage.
- Deployment readiness report.

Acceptance Criteria:
- The system is ready for staging/production review.
- Security tests pass.

## Suggested Order of Implementation
1. Batch 1
2. Batch 2
3. Batch 3
4. Batch 4
5. Batch 5
6. Batch 6

## Recommended Team Flow
- One batch per sprint or milestone.
- Keep PRs focused on a single batch.
- Add tests for every batch.
- Review security changes with a checklist before merge.

## Notes for the AI / Developer
When implementing, follow these rules:
- Use existing Laravel authentication patterns where possible.
- Do not invent new roles or table structures without confirming them.
- Keep changes additive and reversible.
- Prefer middleware and policies over ad-hoc checks.
