# Authentication Integration

## Objective

Only ACTIVE users may authenticate.

## Required Status Behavior

| Status | Login |
|---|---|
| PENDING | Denied |
| INACTIVE | Denied |
| SUSPENDED | Denied |
| ACTIVE | Allowed |

## Requirements

Inspect the existing AuthService and authentication flow first.

Preserve:
- existing authentication behavior
- rate limiting
- login logging
- session handling
- existing error handling

Do not unnecessarily rewrite the authentication system.

The activation flow must result in:

PENDING → password setup → ACTIVE → login allowed