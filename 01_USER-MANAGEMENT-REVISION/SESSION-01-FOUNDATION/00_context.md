# Session 1 — Context

## Project

CIS Capstone — Concepcion Integrated School

## Backend Environment

- Laravel
- Laravel Sail / Docker
- Session-based authentication
- Existing Laravel application — modify the existing architecture instead of restructuring it

## User Roles

The existing system has:

- Admin
- Teacher
- Scanner Operator

Do not introduce additional roles.

## Session Objective

Prepare the existing User Management and account lifecycle foundation for the revised invitation-based authentication flow.

This session focuses ONLY on:

- user account states
- user creation
- removal of default passwords
- user editing
- account deactivation
- account reactivation
- authentication behavior related to account status
- focused PHPUnit tests for the changes

The invitation mechanism itself belongs to Session 2.

Password setup belongs to Session 3.

## Important Existing Behavior

The existing system already has:

- role-based middleware
- session authentication
- login rate limiting
- login logging
- password hashing
- soft deletes
- Admin/Teacher/Scanner Operator roles
- existing User Management functionality
- existing Teacher functionality

Preserve these unless they directly conflict with the finalized requirements.