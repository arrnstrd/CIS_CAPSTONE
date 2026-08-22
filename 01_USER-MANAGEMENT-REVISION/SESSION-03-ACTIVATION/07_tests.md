# Session 3 Testing

## Testing Policy

Full PHPUnit testing is deferred to the final testing phase.

The current environment has an external Supabase database connection
problem that prevents database-backed tests from executing.

## Allowed During Session 3

- PHP syntax checks
- Route inspection
- Class/method existence checks
- Static code inspection
- Lightweight checks that do not require the database

## Do Not

- repeatedly run blocked PHPUnit tests
- claim tests passed when they did not execute
- treat code inspection as equivalent to passing tests

## Test Status

Record:

- tests actually executed
- tests blocked
- reason for blocked tests
- static verification performed

## Final Testing

A separate final testing session will execute the complete test suite
once the database environment is available.