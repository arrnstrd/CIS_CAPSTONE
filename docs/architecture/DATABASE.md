# Database

This document defines the complete database reference for the project:
architecture/migration rules, table-by-table specification, and business rules.

Use together with:
- `CURRENT_DATABASE.json` — phpMyAdmin export, current live state
- `FINAL_ERD.dbml` — target schema (corrected version)

The AI must compare both and generate only the necessary database modifications.

> **Correction note:** An earlier version of this document contained columns and
> relationships that did not match the live database (e.g. `users.teacher_id`,
> `teachers.first_name`/`email`/`phone`, `students.guardian_id`,
> `qr_codes hasMany AttendanceLogs`, `flagged_scans belongsTo QrCode`,
> `sections belongsTo SchoolYear`). Those have been corrected below against
> `CURRENT_DATABASE.json` and the full migration history. Do not reintroduce
> the old versions.

---

# Part 1: Database Architecture & Migration Rules

## Existing Database

The current database is based on `CURRENT_DATABASE.json`. Use it to identify
existing tables, columns, and data that must be preserved.

Do not recreate existing tables unless instructed.
Modify existing tables only when necessary.

## Existing Tables (live, already correct — see Part 2 for full spec)

`users`, `teachers`, `guardians`, `students`, `school_years`, `sections`,
`enrollments`, `qr_codes`, `attendance_logs`, `flagged_scans`, `email_logs`,
`schedule_configs`

## New Tables — Status: Already Created

The following tables were part of the target ERD and did not exist in the
original database. **They have already been created via migration** (Step 2
of the Implementation Order is complete). Do not attempt to recreate them —
only alter them if a new request explicitly requires it:

- `subjects`
- `teaching_assignments`
- `room_attendance`
- `grading_periods` (static lookup — 4 rows, no `school_year_id`)
- `assessment_categories`
- `assessments`
- `student_assessment_scores`
- `quarterly_grades`

Notes on these tables:
- `subjects.subject_type` was intentionally NOT added — deferred to a future migration once category values are decided.
- `grading_periods` is a global/shared lookup, not scoped per school year.
- `schedule_configs` remains untouched and standalone; whether `teaching_assignments` eventually supersedes it is a deferred decision, not blocking.

## sections

Retain `advisor_id`. No unnecessary redesign.

## Migration Rules

- Preserve existing data.
- Preserve foreign keys whenever possible.
- Do not rename tables unless instructed.
- Follow Laravel 13 migration conventions.
- One migration per change.
- Generate reversible migrations.

---

# Part 2: Table Specifications

## General Standards

**Primary Keys** — every table uses `id`, bigint, auto increment.

**Foreign Keys** — all relationships use foreign key constraints. Use
cascading updates whenever appropriate. Avoid cascading deletes unless
explicitly required (prefer `restrictOnDelete()` for non-nullable FKs,
`nullOnDelete()` for nullable FKs).

**Timestamps** — every table contains `created_at` and `updated_at` unless
otherwise specified. (`room_attendance` is an intentional exception —
`created_at` only, no `updated_at`.)

**Naming** — singular model names, plural table names, snake_case columns,
Laravel conventions.

---

## users

Stores authentication credentials for administrators and teachers.

| Column | Type | Nullable | Notes |
|---|---|---|---|
| id | bigint | No | Primary Key |
| employee_id | varchar(100) | Yes | Unique |
| first_name | varchar(100) | No | |
| last_name | varchar(100) | No | |
| role | enum | No | admin / teacher / scanner_operator |
| status | enum | No | active / inactive / suspended |
| email | varchar(100) | No | Unique |
| email_verified_at | timestamp | Yes | |
| password | varchar(255) | No | Hashed |
| remember_token | varchar(100) | Yes | |
| deleted_at | timestamp | Yes | Soft delete |
| created_at / updated_at | timestamp | No | |

**Relationships:** hasOne Teacher

---

## teachers

Stores teacher account linkage and status only. Personal details (name,
email, etc.) live on `users`, not here.

| Column | Type |
|---|---|
| id | bigint |
| user_id | bigint, FK → users.id, unique |
| status | enum (active / inactive) |
| created_at / updated_at | timestamp |

**Relationships:**
- belongsTo User
- hasMany TeachingAssignments
- hasMany Sections (as Advisor, via `advisor_id`)

---

## guardians

Stores guardian information. One guardian record links to exactly one
student (a student may have multiple guardian rows for different
relationships, e.g. mother + father).

| Column | Type |
|---|---|
| id | bigint |
| student_id | bigint, FK → students.id |
| name | varchar |
| relationship | enum (mother / father / sibling / guardian) |
| email | varchar |
| created_at / updated_at | timestamp |

**Relationships:** belongsTo Student

---

## students

Stores permanent student information. Should not contain
enrollment-specific data.

| Column | Type |
|---|---|
| id | bigint |
| student_number | varchar, unique |
| lrn | varchar, unique |
| first_name | varchar |
| middle_name | varchar, nullable |
| last_name | varchar |
| sex | enum |
| birthdate | date |
| address | varchar |
| status | enum |
| created_at / updated_at | timestamp |

**Relationships:**
- hasOne Guardian
- hasMany Enrollments
- hasOne QrCode

---

## school_years

| Column | Type |
|---|---|
| id | bigint |
| school_year | varchar, unique |
| is_active | boolean |
| created_at / updated_at | timestamp |

**Relationships:**
- hasMany Enrollments
- hasMany TeachingAssignments

(No direct relationship to Sections — `sections` has no `school_year_id` column.)

---

## sections

Does not carry a `school_year_id` in the current schema — sectioning is
scoped per school year through `enrollments` and `teaching_assignments`
instead.

| Column | Type |
|---|---|
| id | bigint |
| name | varchar |
| level | enum |
| grade_level | int |
| advisor_id | bigint, FK → teachers.id, nullable |
| capacity | int, default 40 |
| status | enum |
| created_at / updated_at | timestamp |

**Relationships:**
- belongsTo Teacher (as Advisor, via `advisor_id`)
- hasMany Enrollments
- hasMany TeachingAssignments

---

## subjects

| Column | Type |
|---|---|
| id | bigint |
| code | varchar |
| name | varchar |
| level | enum |
| created_at / updated_at | timestamp |

**Relationships:** hasMany TeachingAssignments

---

## enrollments

Represents a student's enrollment for a specific school year.

**Relationships:**
- belongsTo Student, Section, SchoolYear
- hasMany AttendanceLogs, RoomAttendance
- hasMany StudentAssessmentScores, QuarterlyGrades

---

## teaching_assignments

Represents one teacher handling one subject for one section during one
school year. This is the primary entity of the Teacher Portal.

Required scanner fields: `session_type`, `in_start`, `late_threshold`, `out_end`

**Relationships:**
- belongsTo Teacher, Subject, Section, SchoolYear
- hasMany Assessments, RoomAttendance, QuarterlyGrades

---

## qr_codes

| Column | Type |
|---|---|
| id | bigint |
| student_id | bigint, FK → students.id |
| code | varchar, unique |
| image_path | varchar, nullable |
| is_active | boolean |
| created_at / updated_at | timestamp |

**Relationships:** belongsTo Student

(No relationship to AttendanceLogs — `attendance_logs` links through
`enrollment_id`, not `qr_code_id`. There is no `qr_code_id` column on
`attendance_logs`.)

---

## attendance_logs

Stores gate attendance scans.

| Column | Type |
|---|---|
| id | bigint |
| enrollment_id | bigint, FK → enrollments.id |
| scan_type | enum (IN / OUT / RE_ENTRY / RE_EXIT) |
| session_type | enum (morning / afternoon) |
| scan_time | datetime |
| scanned_by_user_id | bigint, FK → users.id, nullable |
| device_id | varchar, nullable |
| created_at / updated_at | timestamp |

**Relationships:**
- belongsTo Enrollment
- belongsTo User (as scanned_by_user_id)
- hasMany FlaggedScans
- hasOne EmailLog

(No relationship to QrCode.)

---

## room_attendance

Stores classroom attendance generated by classroom QR scanning.
No `updated_at` column (intentional).

**Relationships:** belongsTo TeachingAssignment, Enrollment

---

## flagged_scans

Stores suspicious/flagged attendance scans.

| Column | Type |
|---|---|
| id | bigint |
| attendance_log_id | bigint, FK → attendance_logs.id, nullable |
| flag_type | enum |
| description | text, nullable |
| created_at / updated_at | timestamp |

**Relationships:** belongsTo AttendanceLog

(Not QrCode — there is no `qr_code_id` column on `flagged_scans`.)

---

## email_logs

Stores email notification history.

**Relationships:** belongsTo AttendanceLog, belongsTo Student

---

## Grading Module

### grading_periods
Static/global lookup — not scoped per school year (no `school_year_id`
column). Expected to contain exactly 4 rows: 1st–4th Quarter.

**Relationships:** hasMany Assessments, QuarterlyGrades

### assessment_categories
Seed data: Written Work, Performance Task, Quarterly Assessment.

**Relationships:** hasMany Assessments

### assessments
Examples: Quiz, Seatwork, Laboratory, Performance Task, Quarterly Examination.

**Relationships:** belongsTo TeachingAssignment, AssessmentCategory,
GradingPeriod; hasMany StudentAssessmentScores

### student_assessment_scores
One record per Enrollment + Assessment.

**Relationships:** belongsTo Assessment, Enrollment

### quarterly_grades
Computed values — teachers never manually encode these. System computes
Written Works Grade, Performance Tasks Grade, Quarterly Assessment Grade,
Initial Grade, Transmuted Grade.

**Relationships:** belongsTo TeachingAssignment, Enrollment, GradingPeriod

---

# Part 3: Business Rules

## Students
- A student may have multiple enrollments throughout their academic history.
- Only one enrollment may be active per school year.
- Students cannot be scanned if inactive.

## QR Codes
- Each student has one active QR code.
- QR codes must be unique.
- Inactive QR codes cannot be used.

## Teaching Assignments
A teaching assignment represents Teacher + Subject + Section + School Year.
It is the primary entity of the Teacher Portal. One teacher may have
multiple teaching assignments.

## Classroom Attendance
Room attendance is based on Teaching Assignments. Uses `session_type`,
`in_start`, `late_threshold`, `out_end`.

## Assessments
Assessments belong to one Teaching Assignment. Categories: Written Works,
Performance Tasks, Quarterly Assessment.

## Student Assessment Scores
One student has one score per assessment. Score cannot exceed `total_items`.

## Quarterly Grades
Automatically computed. Teachers cannot manually encode final grades.
System computes Written Works Grade, Performance Tasks Grade, Quarterly
Assessment Grade, Initial Grade, Transmuted Grade.

## Reports
Consolidated grades are generated from Quarterly Grades. No duplicated
grade storage.

---

# Part 4: Known Model-Layer Issues

Pre-existing bugs in `app/Models`, unrelated to schema/migration work,
flagged here so future phases address them:

- `Guardian::student()` was written as `hasOne(Student::class)` — should be
  `belongsTo(Student::class, 'student_id')`.
- `Enrollment::adviser()` references `adviser_id`, a column already dropped
  from `enrollments`. Should be removed.

---

# Migration Notes / Implementation Rules

- Preserve existing data and relationships.
- Add only missing columns and tables.
- Do not rename tables unless explicitly instructed.
- Follow Laravel 13 migration conventions.
- Use proper foreign key constraints and indexes.
- Generate reversible migrations.
- Maintain backward compatibility with the existing gate and classroom attendance system — do not remove that functionality.
- Generate migrations in logical order: lookup tables → modify existing tables → grading tables → foreign keys → indexes.