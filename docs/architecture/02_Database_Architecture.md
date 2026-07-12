# Database Architecture

## Existing Database

The current database is based on `CURRENT_DATABASE.json`.

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

- grading_periods
- assessment_categories
- grading_components
- assessments
- student_assessment_scores
- quarterly_grades

## Existing Tables to Modify

subjects

- Add subject_type if not existing.

teaching_assignments

Retain:

- session_type
- in_start
- late_threshold
- out_end

These fields are required by the classroom attendance scanner.

sections

Retain advisor_id.

No unnecessary redesign.

## Migration Rules

- Preserve existing data.
- Preserve foreign keys whenever possible.
- Do not rename tables unless instructed.
- Follow Laravel migration conventions.
- One migration per change.