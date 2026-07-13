# Project Context

## Project Title

Web-Based QR Student Attendance Monitoring with Centralized Academic Management System for Concepcion Integrated School (CIS)

## Technology Stack

- Laravel 13
- PHP 8+
- MySQL
- Bootstrap 5
- Vanilla JavaScript
- MVC Architecture, feature-first organization

## Current Project Status

The project already contains a working implementation for:

- User Management
- Student Management
- Guardian Management
- Academic Setup (School Years, Sections, Subjects, Enrollments)
- QR Code Generation
- Gate Attendance
- Teacher Management
- Teaching Assignments (schema-level; scanner fields already used by `schedule_configs` / `teaching_assignments`)

The current database is represented by `CURRENT_DATABASE.json`.
The target architecture is represented by `FINAL_ERD.dbml` (corrected version).

The goal is to migrate the current implementation into the final architecture while preserving existing functionality — additive, non-destructive changes only, aligned with the Database Migration Rules in `DATABASE.md`.

---

## Feature Architecture

**Dashboard** — planned, not implemented yet.

**Student Management**
- Students
- Guardians

**Academic Setup**
- School Years
- Sections
- Subjects
- Enrollments

**QR Attendance**
- QR Generation
- Gate Attendance
- Classroom Attendance
- Scanner Configuration

**Teacher Portal** (flow)

```
Teaching Assignment
      ↓
Student Master List
      ↓
Attendance
      ↓
Grading
  - Assessments
  - Student Scores
  - Quarterly Grades
      ↓
Reports
```

**Administration**
- Users
- Teachers
- Roles
- Security