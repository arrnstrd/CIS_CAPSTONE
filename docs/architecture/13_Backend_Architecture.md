# Backend Architecture

## Purpose

This document defines the backend architecture of the project.

All generated backend code must follow this architecture.

The backend follows a layered architecture built on top of Laravel MVC.

Business logic must remain separated from controllers.

---

# Architecture Overview

Presentation Layer

↓

Controller Layer

↓

Validation Layer

↓

Service Layer

↓

Model Layer

↓

Database

---

# Controller Responsibilities

Controllers should remain thin.

Responsibilities include:

- Receive HTTP requests.
- Call Form Requests for validation.
- Delegate business logic to Services.
- Return responses or views.

Controllers must not contain:

- Grade computations
- Attendance processing
- QR generation logic
- Database transactions
- Complex business rules

---

# Validation Layer

All request validation must use Laravel Form Requests.

Never validate directly inside controllers.

Examples

- StoreStudentRequest
- UpdateStudentRequest
- StoreAssessmentRequest

---

# Service Layer

All business logic belongs inside Services.

Examples

AttendanceService

Responsibilities

- QR Validation
- Attendance Processing
- Late Detection
- Email Trigger

GradingService

Responsibilities

- Assessment Creation
- Grade Computation
- Quarterly Grade Generation

StudentService

Responsibilities

- Student Registration
- Student Updates

---

# Model Layer

Models are responsible for:

- Relationships
- Query scopes
- Accessors
- Mutators

Avoid placing business logic inside models.

---

# Database Layer

The database follows the schema defined in:

- CURRENT_DATABASE.json
- FINAL_ERD.dbml

Only modify the database through Laravel migrations.

Never perform schema modifications directly.

---

# Transactions

Database transactions should be used whenever multiple related records are created or updated.

Examples

Enrollment

- Create Enrollment
- Generate QR Code

Attendance

- Save Attendance
- Save Email Log

Grading

- Save Assessment
- Save Student Scores
- Generate Quarterly Grade

---

# Authorization

Authorization must be implemented using Laravel Authorization features.

Access should be controlled by user role.

Examples

Administrator

- Full system access

Teacher

- Assigned classes only

Future authorization should use Policies or Gates where appropriate.

---

# Error Handling

Handle exceptions gracefully.

Log unexpected errors.

Return meaningful validation messages.

Do not expose stack traces to users.

---

# AI Development Rules

When generating backend code, apply this architecture together with:

1. `10_Project_Structure.md`
2. `09_Laravel_Conventions.md`
3. `11_Implementation_Order.md`
4. `12_Development_Rules.md`
