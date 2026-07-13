# AI Guide

This is the first file an AI agent should read.

## Required Reading Order

1. `AI_GUIDE.md` (this file)
2. `PROJECT_CONTEXT.md`
3. `CURRENT_DATABASE.json`
4. `FINAL_ERD.dbml`
5. `DATABASE.md`
6. `BACKEND_STANDARDS.md`

Do not jump directly to the Implementation Order below without reading the above first.

---

## How To Use The Reference Files

- `CURRENT_DATABASE.json` — the current database export (phpMyAdmin). Ground truth for what exists today.
- `FINAL_ERD.dbml` — the target schema (corrected version, verified against live migrations and models).
- `DATABASE.md` — migration plan + table-by-table spec + business rules.
- `BACKEND_STANDARDS.md` — folder structure, Laravel conventions, layered architecture.
- `PROJECT_CONTEXT.md` — what the project is, current feature status.

---

## Required Procedure (before generating any code)

1. Identify the requested feature or change.
2. Check whether it affects the database, backend code, frontend code, or all of them.
3. If schema is affected: compare `CURRENT_DATABASE.json` with `FINAL_ERD.dbml`. List only the missing tables, columns, indexes, or relationships required for the request.
4. Check `DATABASE.md` before writing migrations or model relationships.
5. Check `BACKEND_STANDARDS.md` before choosing folders, naming classes/routes/methods, or deciding whether logic belongs in a Controller, Form Request, Service, or Model.
6. Apply the Development Rules (guardrails) below.
7. Follow the Implementation Order below when creating files.
8. Generate only the code required by the user's request. Stop at the requested scope.

## Conflict Resolution

If two documents appear to conflict, use this priority order:

1. The user's latest instruction.
2. `CURRENT_DATABASE.json` for the current database state.
3. `FINAL_ERD.dbml` for the target database state.
4. `DATABASE.md` for schema details and business rules.
5. `BACKEND_STANDARDS.md` for implementation style.

When uncertain, preserve existing functionality and avoid redesigning unrelated modules.

---

## Implementation Order

Follow this order when implementing backend changes.

### Step 1: Scope The Request
1. Identify the feature or module being changed.
2. Identify whether the change needs database work.
3. Identify whether the change needs backend code only or frontend code too.
4. Do not include unrelated modules.

### Step 2: Plan Database Changes
1. Read `CURRENT_DATABASE.json`.
2. Read `FINAL_ERD.dbml`.
3. Read `DATABASE.md`.
4. Create migrations only for missing or changed schema items required by the request.
5. Modify existing tables before creating dependent foreign keys.
6. Create new lookup tables before tables that reference them.
7. Add indexes and foreign keys after required columns exist.

> Status: Step 2 is already complete as of this revision. All 8 previously-missing
> tables (`subjects`, `teaching_assignments`, `room_attendance`, `grading_periods`,
> `assessment_categories`, `assessments`, `student_assessment_scores`,
> `quarterly_grades`) have been created via migration. No further schema work is
> needed unless a new request explicitly calls for it.

### Step 3: Update Models
1. Add or update Eloquent models for affected tables.
2. Add relationships exactly as defined in `DATABASE.md` — do not infer relationships from column names or assumptions; use the FK directions documented there.
3. Keep large business logic out of models.

### Step 4: Add Form Requests
1. Create Form Request classes for create and update actions.
2. Put validation rules in Form Requests.
3. Do not validate directly inside controllers.

### Step 5: Add Services
1. Put business logic in Services.
2. Use transactions when multiple related records are created or updated.
3. Preserve the existing attendance and QR behavior.

### Step 6: Add Controllers
1. Keep controllers thin.
2. Controllers should receive requests, call Form Requests, call Services, and return responses or views.
3. Do not place grade computation, attendance processing, QR generation, or complex rules inside controllers.

### Step 7: Add Routes
1. Use resource routes where appropriate.
2. Follow the route naming conventions in `BACKEND_STANDARDS.md`.
3. Avoid deeply nested routes unless required.

### Step 8: Frontend
Do not modify frontend files unless the user explicitly requests frontend changes.

### Completion
After completing the requested phase:
1. Summarize the implementation.
2. List every file created.
3. List every file modified.
4. Explain why each change was made.
5. Generate an implementation report in Markdown.
6. List any remaining work that is outside the requested scope.
7. Create an md file for each change under the `docs/implementation` folder.

Stop after completing the requested phase.

---

## Development Rules (non-negotiable guardrails)

1. Do not modify frontend files unless the user explicitly requests frontend changes.
2. Do not remove existing features.
3. Do not change existing attendance or QR scan behavior unless the user explicitly requests it.
4. Preserve existing database data.
5. Preserve existing APIs whenever possible.
6. Do not invent tables, columns, modules, seeders, or dummy data.
7. Do not redesign unrelated modules.
8. Use Laravel migrations for schema changes — never modify the database directly.
9. Follow the Implementation Order above.
10. Follow `BACKEND_STANDARDS.md` for architecture and conventions.
11. When a relationship's FK direction is unclear or not explicitly documented in `DATABASE.md`, ask before assuming — do not guess based on what "seems logical."