# AGENTS.md

## Project Context

**Web-Based QR Student Attendance Monitoring with Centralized Academic
Management System** for Concepcion Integrated School (CIS).

- Laravel 13, PHP 8+, MySQL
- Bootstrap 5, vanilla JavaScript
- MVC architecture, feature-first folder organization
- Custom `ajaxCrud` JS utility (`fetch()` + `DOMParser` + `replaceMatchedRegions()`)
  for partial DOM updates — check for this before writing new AJAX handling
  from scratch
- Soft badge system (`bg-*-soft` classes with matching borders) — reuse this
  pattern for any new status/badge UI instead of inventing a new style

Full feature breakdown and current implementation status live in
`docs/architecture/PROJECT_CONTEXT.md` — read it if you need the bigger
picture beyond what's below.

## What's in `docs/` and When to Read Each Folder

This project keeps four kinds of documentation. Know which one answers your
question before you go looking in the wrong place.

- **`docs/architecture/`** — the original planning and schema documents
  (`AI_GUIDE.md`, `PROJECT_CONTEXT.md`, `DATABASE.md`,
  `BACKEND_STANDARDS.md`, `CURRENT_DATABASE.json`, `FINAL_ERD.dbml`).
  **The build-out phases described in `AI_GUIDE.md` (Steps 1–8: Migrations →
  Models → Form Requests → Services → Auth/RBAC → Controllers → Routes) are
  already complete.** Treat this folder as the historical spec and schema
  reference, not a to-do list. Still consult `DATABASE.md` and
  `CURRENT_DATABASE.json` before touching any table, column, or
  relationship — they're the source of truth for what exists.

- **`docs/implementation/`** — one dated/numbered markdown file per
  completed task (`01_database_migrations.md`, `03_models.md`,
  `06_controllers.md`, etc.), written by whichever AI agent did that task.
  Each file is a short report: what changed, why, and any gotchas. Read the
  relevant one before touching a feature someone (or some agent) already
  worked on, so you don't redo or contradict prior decisions. After you
  finish a task, add your own file here — this is how progress gets tracked
  across sessions.

- **`docs/security-hardening/`** — hardening plans and security-specific
  work (currently `security-hardening-plan.md`). Only relevant when the task
  is explicitly about auth, permissions, validation hardening, or similar —
  don't apply these constraints to unrelated feature work unless asked.

- **`docs/status/`** — overall project status tracking, for a bird's-eye
  view of what's done vs. pending across the whole system. Check here first
  if you're unsure whether a feature already exists before building it.

## Before Doing Anything

1. Check `docs/status/` for whether this feature/area is already built.
2. Check `docs/implementation/` for an existing report on this area — don't
   redo finished work or contradict a documented decision without asking.
3. For backend/database/model work: verify against `DATABASE.md` and
   `CURRENT_DATABASE.json` — never assume a column or relationship exists
   because it "should."

Do not rely on memory of a previous session — this project's schema has
changed multiple times and past assumptions may be stale.

## Backend Task Workflow

Before writing new backend code, check what already exists in this order:
1. **Controller** — is there already a method that does this or something close?
2. **Service class** (`app/Services/...`) — business logic belongs here, not in the controller.
3. **Form Request** (`app/Http/Requests/...`) — validation belongs here, not inline in the controller.
4. **Model relationships** — confirm against `DATABASE.md`, not assumption.

## Frontend Task Workflow

Before writing new frontend code, check what already exists:
1. Is there an existing Blade component (`resources/views/components/...`)
   that does this or something close? Reuse it instead of writing a new one.
2. Does the existing `ajaxCrud` JS utility already cover this interaction
   pattern (partial DOM replacement, fetch-based forms)?
3. Match the existing soft-badge / nav-pill / card conventions already used
   elsewhere in the app rather than introducing a new visual pattern.

## Non-Negotiable Rules

- Do not invent table names, column names, or foreign key directions. If a
  relationship or column isn't explicitly documented in `DATABASE.md`, stop
  and ask instead of guessing what "seems logical."
- `CURRENT_DATABASE.json` is the source of truth for what currently exists.
  `FINAL_ERD.dbml` is the target. Never assume a column exists because it
  "should" — verify against the JSON export.
- Do not modify frontend files unless explicitly requested, and vice versa
  — do not modify backend files for a frontend-only task unless explicitly
  requested.
- Do not remove existing features or change attendance/QR scan behavior
  unless explicitly requested.
- Do not create migrations for tables that already exist — check
  `CURRENT_DATABASE.json` first. As of this revision, all 8 previously-missing
  tables (`subjects`, `teaching_assignments`, `room_attendance`,
  `grading_periods`, `assessment_categories`, `assessments`,
  `student_assessment_scores`, `quarterly_grades`) already exist.
- Preserve existing data. Additive migrations only unless told otherwise.
- One migration per responsibility. Reversible migrations only.
- Stop at the requested scope. Do not jump ahead to unrelated layers
  (e.g. touching Controllers/Routes when only asked to fix a Model) unless
  asked.
- After finishing a task, write a short implementation report to
  `docs/implementation/0#_<task>.md` — what changed, why, and any gotchas
  for the next session (human or AI) to pick up from.

## Known Pre-existing Bugs (do not "fix" silently, but don't reintroduce them either)

- `Guardian::student()` is currently `hasOne(Student::class)` — should be
  `belongsTo(Student::class, 'student_id')`. Only fix if the current task
  is explicitly about Models.
- `Enrollment::adviser()` references `adviser_id`, a column already dropped
  from `enrollments`. Dead code — only remove if the current task is
  explicitly about Models.

## Reasoning Effort

Schema, migration, and model relationship work is error-prone at low
reasoning effort — this project has already hit hallucinated foreign keys
and phantom columns from an earlier pass. For any task touching migrations,
models, or table relationships, use medium-or-higher reasoning effort even
if the global config default is low.