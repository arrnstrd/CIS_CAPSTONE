# CIS Capstone — Database ERD Reference

This document describes the entity-relationship structure of the system. Intended as a reference for AI coding agents working on the codebase (models, migrations, controllers).

## Legend
- `[pk]` = primary key
- `[fk -> table.column]` = foreign key reference
- `null` = column is nullable

---

## Core / User Management

### users
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| teacher_id | bigint | [fk -> teachers.id], null |
| username | varchar | |
| email | varchar | |
| password | varchar | |
| role | enum | |
| status | enum | |
| last_login_at | timestamp | |
| created_at | timestamp | |
| updated_at | timestamp | |

### teachers
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| employee_no | varchar | |
| first_name | varchar | |
| middle_name | varchar | |
| last_name | varchar | |
| suffix | varchar | |
| sex | enum | |
| email | varchar | |
| phone | varchar | |
| position | varchar | |
| employment_status | enum | |
| created_at | timestamp | |
| updated_at | timestamp | |

### guardians
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| first_name | varchar | |
| middle_name | varchar | |
| last_name | varchar | |
| relationship | varchar | |
| phone | varchar | |
| email | varchar | |
| address | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

### students
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| student_no | varchar | |
| guardian_id | bigint | [fk -> guardians.id] |
| first_name | varchar | |
| middle_name | varchar | |
| last_name | varchar | |
| suffix | varchar | |
| sex | enum | |
| birth_date | date | |
| address | text | |
| status | enum | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Academic Structure

### school_years
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| school_year | varchar | |
| is_active | boolean | |
| created_at | timestamp | |
| updated_at | timestamp | |

### sections
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| school_year_id | bigint | [fk -> school_years.id] |
| advisor_id | bigint | [fk -> teachers.id] |
| grade_level | tinyint | |
| name | varchar | |
| capacity | int | |
| status | enum | |
| created_at | timestamp | |
| updated_at | timestamp | |

### subjects
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| code | varchar | |
| name | varchar | |
| level | enum | |
| created_at | timestamp | |
| updated_at | timestamp | |

### enrollments
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| student_id | bigint | [fk -> students.id] |
| section_id | bigint | [fk -> sections.id] |
| school_year_id | bigint | [fk -> school_years.id] |
| status | enum | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Teaching

### teaching_assignments
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| teacher_id | bigint | [fk -> teachers.id] |
| subject_id | bigint | [fk -> subjects.id] |
| section_id | bigint | [fk -> sections.id] |
| school_year_id | bigint | [fk -> school_years.id] |
| session_type | enum | |
| in_start | time | |
| late_threshold | time | |
| out_end | time | |
| status | enum | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## QR Attendance

### qr_codes
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| student_id | bigint | [fk -> students.id] |
| code | varchar | |
| image_path | varchar | |
| is_active | boolean | |
| created_at | timestamp | |
| updated_at | timestamp | |

### attendance_logs
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| enrollment_id | bigint | [fk -> enrollments.id] |
| qr_code_id | bigint | [fk -> qr_codes.id] |
| type | enum | |
| scanned_at | datetime | |
| remarks | varchar | |
| created_at | timestamp | |

### room_attendance
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| teaching_assignment_id | bigint | [fk -> teaching_assignments.id] |
| enrollment_id | bigint | [fk -> enrollments.id] |
| attendance_date | date | |
| time_in | datetime | |
| time_out | datetime | |
| remarks | varchar | |
| created_at | timestamp | |

### flagged_scans
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| qr_code_id | bigint | [fk -> qr_codes.id] |
| reason | varchar | |
| scanned_at | datetime | |

### email_logs
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| attendance_log_id | bigint | [fk -> attendance_logs.id] |
| recipient | varchar | |
| status | enum | |
| sent_at | datetime | |

---

## Grading

### grading_periods
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| name | varchar | |
| sequence | tinyint | |
| is_active | boolean | |

### assessment_categories
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| name | varchar | |

### grading_components
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| grade_level | tinyint | |
| assessment_category_id | bigint | [fk -> assessment_categories.id] |
| weight_percentage | decimal(5,2) | |
| created_at | timestamp | |
| updated_at | timestamp | |

### assessments
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| teaching_assignment_id | bigint | [fk -> teaching_assignments.id] |
| assessment_category_id | bigint | [fk -> assessment_categories.id] |
| grading_period_id | bigint | [fk -> grading_periods.id] |
| title | varchar | |
| description | text | |
| total_items | int | |
| assessment_date | date | |
| status | enum | |
| created_at | timestamp | |
| updated_at | timestamp | |

### student_assessment_scores
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| assessment_id | bigint | [fk -> assessments.id] |
| enrollment_id | bigint | [fk -> enrollments.id] |
| score | decimal(6,2) | |
| remarks | varchar | |
| created_at | timestamp | |
| updated_at | timestamp | |

### quarterly_grades
| Column | Type | Notes |
|---|---|---|
| id | bigint | [pk] |
| teaching_assignment_id | bigint | [fk -> teaching_assignments.id] |
| enrollment_id | bigint | [fk -> enrollments.id] |
| grading_period_id | bigint | [fk -> grading_periods.id] |
| written_work_grade | decimal(6,2) | |
| performance_task_grade | decimal(6,2) | |
| quarterly_assessment_grade | decimal(6,2) | |
| initial_grade | decimal(6,2) | |
| transmuted_grade | decimal(6,2) | |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## Relationship Summary

```
teachers ─┬─< users (teacher_id, nullable)
          ├─< sections (advisor_id)
          └─< teaching_assignments (teacher_id)

guardians ──< students (guardian_id)

students ─┬─< enrollments (student_id)
          └─< qr_codes (student_id)

school_years ─┬─< sections (school_year_id)
              ├─< enrollments (school_year_id)
              └─< teaching_assignments (school_year_id)

sections ─┬─< enrollments (section_id)
          └─< teaching_assignments (section_id)

subjects ──< teaching_assignments (subject_id)

enrollments ─┬─< attendance_logs (enrollment_id)
             ├─< room_attendance (enrollment_id)
             ├─< student_assessment_scores (enrollment_id)
             └─< quarterly_grades (enrollment_id)

qr_codes ─┬─< attendance_logs (qr_code_id)
          └─< flagged_scans (qr_code_id)

attendance_logs ──< email_logs (attendance_log_id)

teaching_assignments ─┬─< room_attendance (teaching_assignment_id)
                       ├─< assessments (teaching_assignment_id)
                       └─< quarterly_grades (teaching_assignment_id)

assessment_categories ─┬─< grading_components (assessment_category_id)
                        └─< assessments (assessment_category_id)

grading_periods ─┬─< assessments (grading_period_id)
                  └─< quarterly_grades (grading_period_id)

assessments ──< student_assessment_scores (assessment_id)
```

## Module Groupings
- **Core / User Management**: `users`, `teachers`, `guardians`, `students`
- **Academic Structure**: `school_years`, `sections`, `subjects`, `enrollments`
- **Teaching**: `teaching_assignments`
- **QR Attendance**: `qr_codes`, `attendance_logs`, `room_attendance`, `flagged_scans`, `email_logs`
- **Grading**: `grading_periods`, `assessment_categories`, `grading_components`, `assessments`, `student_assessment_scores`, `quarterly_grades`