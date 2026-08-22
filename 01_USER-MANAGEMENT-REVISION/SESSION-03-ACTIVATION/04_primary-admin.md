# Primary Admin Protection

## Hard Constraint

Do NOT assume that the Primary Admin is:

- User ID 1
- superadmin@cis.edu.ph
- the first admin created
- any specific email address

## Audit

Inspect the existing codebase for an authoritative Primary Admin
identification mechanism.

Check:

- User model
- migrations
- seeders
- configuration
- policies
- gates
- middleware
- services
- constants
- database fields
- helper methods

## If an Authoritative Mechanism Exists

Use the existing mechanism.

Do not create a competing identification system.

## If No Mechanism Exists

This is a blocking ambiguity.

DO NOT:
- hardcode an ID
- hardcode an email
- infer the Primary Admin from the seeder
- invent a new rule without authorization

Document the finding and leave Primary Admin protection unchanged.

## Required Documentation

Record:
- what was searched
- what identification mechanism was found, if any
- what protection currently exists
- whether implementation was possible