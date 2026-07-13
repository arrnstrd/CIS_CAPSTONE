# Database Migrations Implementation Report

## Objective

Complete Step 1 (Scope the Request) and Step 2 (Plan Database Changes) from `docs/architecture/11_Implementation_Order.md` by adding only the Laravel 13 migrations required for missing tables identified from `CURRENT_DATABASE.json` and the corrected `FINAL_ERD.dbml`.

## Summary

Implemented additive-only database migrations for the missing academic, classroom attendance, and grading schema. No existing tables, models, requests, services, controllers, routes, or frontend files were modified.

## Files Created

- `database/migrations/2026_07_12_201347_create_subjects_table.php`
- `database/migrations/2026_07_12_201348_create_grading_periods_table.php`
- `database/migrations/2026_07_12_201349_create_assessment_categories_table.php`
- `database/migrations/2026_07_12_201350_create_teaching_assignments_table.php`
- `database/migrations/2026_07_12_201351_create_room_attendance_table.php`
- `database/migrations/2026_07_12_201352_create_assessments_table.php`
- `database/migrations/2026_07_12_201353_create_student_assessment_scores_table.php`
- `database/migrations/2026_07_12_201354_create_quarterly_grades_table.php`
- `docs/implementation/01_database_migrations.md`

## Files Modified

None.

## Database Changes

- Added `subjects` with plain target columns only: `id`, `code`, `name`, `level`, `created_at`, and `updated_at`.
- Added global `grading_periods` lookup table with unique `sequence` and four static rows: 1st Quarter, 2nd Quarter, 3rd Quarter, and 4th Quarter.
- Added `assessment_categories` lookup table.
- Added `teaching_assignments` with foreign keys to teachers, subjects, sections, and school years, plus session scanner fields.
- Added `room_attendance` with foreign keys to teaching assignments and enrollments. `time_out` is nullable.
- Added `assessments` with foreign keys to teaching assignments, assessment categories, and grading periods.
- Added `student_assessment_scores` with one score per assessment and enrollment.
- Added `quarterly_grades` with one computed grade row per teaching assignment, enrollment, and grading period.
- Added confirmed unique constraints and practical query indexes for codes, sequences, duplicate prevention, dates, and common lookup columns.

## Notes

- Existing tables were not altered because the finalized scope confirmed that current live schema alignment requires no existing-table changes.
- `subjects.subject_type` was intentionally not added in this phase.
- `schedule_configs` remains untouched and standalone.
- New foreign keys use cascading updates and restricted deletes to preserve referential integrity without deleting academic history implicitly.
- No seeders or dummy data were added. The four grading-period rows are static lookup data inserted by the table migration.

## Remaining Work

- Fix `Guardian::student()` relationship in the Model layer.
- Fix `Enrollment::adviser()` relationship in the Model layer.
- Add `subjects.subject_type` in a future migration after category values are decided.
- Implement Models, Form Requests, Services, Controllers, Routes, and Frontend work in later phases only when requested.
