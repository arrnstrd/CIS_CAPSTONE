# AGENTS.md

## Before Doing Anything

Before making any backend, database, or model change, read these files in
this order:

1. `docs/architecture/AI_GUIDE.md`
2. `docs/architecture/PROJECT_CONTEXT.md`
3. `docs/architecture/CURRENT_DATABASE.json`
4. `docs/architecture/FINAL_ERD.dbml`
5. `docs/architecture/DATABASE.md`
6. `docs/architecture/BACKEND_STANDARDS.md`

Do not skip this even if the task looks simple. Do not rely on memory of a
previous session — this project's schema has changed multiple times and
past assumptions may be stale.

## Non-Negotiable Rules

- Do not invent table names, column names, or foreign key directions. If a
  relationship or column isn't explicitly documented in `DATABASE.md`, stop
  and ask instead of guessing what "seems logical."
- `CURRENT_DATABASE.json` is the source of truth for what currently exists.
  `FINAL_ERD.dbml` is the target. Never assume a column exists because it
  "should" — verify against the JSON export.
- Do not modify frontend files unless explicitly requested.
- Do not remove existing features or change attendance/QR scan behavior
  unless explicitly requested.
- Do not create migrations for tables that already exist — check
  `CURRENT_DATABASE.json` first. As of this revision, all 8 previously-missing
  tables (`subjects`, `teaching_assignments`, `room_attendance`,
  `grading_periods`, `assessment_categories`, `assessments`,
  `student_assessment_scores`, `quarterly_grades`) already exist.
- Preserve existing data. Additive migrations only unless told otherwise.
- One migration per responsibility. Reversible migrations only.
- Stop at the requested scope. Follow the Implementation Order (Steps 1-8)
  in `AI_GUIDE.md` — do not jump ahead to later steps (Services,
  Controllers, Routes, Frontend) unless asked.
- After finishing a phase, write an implementation report to
  `docs/implementation/0#_<task>.md` per the Completion section in
  `AI_GUIDE.md`.

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