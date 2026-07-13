# Database Architecture

## Existing Database

The current database is based on `CURRENT_DATABASE.json`.

`CURRENT_DATABASE.json` is a phpMyAdmin export of the current implementation. Use it to identify existing tables, columns, and data that must be preserved.

Do not recreate existing tables unless instructed.

Modify existing tables only when necessary.

## Target Database

The target database is defined in `FINAL_ERD.dbml`.

## Existing Tables

- users
- teachers
- students
- guardians
- school_years
- sections
- subjects
- enrollments
- teaching_assignments
- qr_codes
- attendance_logs
- room_attendance
- flagged_scans
- email_logs

## New Tables



Create:

- subjects
- teaching_assignments
- room_attendance
- grading_periods
- assessment_categories
- assessments
- student_assessment_scores
- quarterly_grades



sections

Retain advisor_id.

No unnecessary redesign.

## Migration Rules

- Preserve existing data.
- Preserve foreign keys whenever possible.
- Do not rename tables unless instructed.
- Follow Laravel 13 migration conventions.
- One migration per change.
- Generate reversible migrations.
