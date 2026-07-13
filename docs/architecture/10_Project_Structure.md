# Project Structure

## Purpose

This document defines the project's backend folder organization.

The project follows a **feature-first architecture**, where files are grouped by major system features rather than database tables.

This structure improves maintainability, readability, and scalability as new modules are introduced.

---

# General Principles

- Organize backend files by **feature**.
- Avoid placing unrelated controllers in the same folder.
- Use subfolders when a feature contains multiple related resources.
- Keep folder names consistent across the project.
- Follow Laravel naming conventions for all classes.

---

# Controller Structure

All controllers should be placed under:

```text
app/
└── Http/
    └── Controllers/
```

Controllers are organized using the following feature structure.

```text
Controllers/

├── Academic/
│   ├── Enrollment/
│   ├── SchoolYear/
│   ├── Section/
│   └── Subject/
│
├── Student/
│
├── Teacher/
│
├── QrSystem/
│   ├── Attendance/
│   ├── Scanner/
│   ├── QrCode/
│   ├── Configuration/
│   └── EmailLog/
│
├── Grading/
│   ├── Assessment/
│   ├── AssessmentCategory/
│   ├── StudentScore/
│   ├── QuarterlyGrade/
│   └── GradingPeriod/
│
├── Administration/
│   ├── User/
│   ├── Authentication/
│   ├── Authorization/
│   └── Archive/
│
└── Reports/
```

---

# Folder Responsibilities

## Academic

Contains controllers related to academic setup.

Examples

- Enrollment
- School Year
- Section
- Subject

---

## Student

Contains controllers responsible for student management.

Examples

- Student
- Student Profile
- Student Search

---

## Teacher

Contains controllers related to teacher management and teacher portal features.

Examples

- Teacher
- Teaching Assignment
- Teacher Dashboard
- Master List

---

## QrSystem

Contains all QR attendance-related functionality.

Subfolders include:

### Attendance

- Attendance Logs
- Room Attendance
- Attendance History

### Scanner

- Gate Scanner
- Classroom Scanner

### QrCode

- QR Generation
- QR Card Export

### Configuration

- Schedule Configuration
- Scanner Configuration

### EmailLog

- Guardian Email Logs
- Notification History

---

## Grading

Contains all grading system functionality.

Subfolders include:

- Assessment
- Assessment Category
- Student Scores
- Quarterly Grades
- Grading Periods

Future grading-related controllers must remain inside this feature.

---

## Administration

Contains system administration functionality.

Examples

- User Management
- Authentication
- Authorization
- Archive Management
- Security Features

---

## Reports

Contains report generation and printable documents.

Examples

- Attendance Reports
- Grade Reports
- Student Reports

---

# Future Backend Layers

The same feature-first organization should be followed whenever new backend layers are introduced.

Examples include:

```text
app/

Services/

Requests/

Policies/

Resources/

Actions/

Events/
```

Example:

```text
Services/

Academic/
Student/
Teacher/
QrSystem/
Grading/
Administration/
Reports/
```

---

# Foldering Rules

- Organize by **feature first**, then by **subfeature** when necessary.
- Do not organize controllers solely by database table.
- Avoid placing controllers directly under the root `Controllers` folder unless they are framework base controllers.
- Use singular controller names following Laravel conventions (e.g., `StudentController`, `SectionController`).
- Create subfolders only when they improve organization.
- Keep related controllers within the same feature.
- New project features should become new top-level feature folders only when they represent a major module of the system.

---

# Notes

This document defines the intended project structure.

Folders that are not yet implemented (such as Services, Policies, or Actions) should follow this organization once they are introduced during development.
