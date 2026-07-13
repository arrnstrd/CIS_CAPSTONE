# Laravel Development Conventions

## Purpose

This document defines the Laravel development standards for the project.

All generated code must follow these conventions to maintain consistency, readability, and scalability.

This document applies to:

- Migrations
- Models
- Controllers
- Form Requests
- Services
- Blade Views
- Routes

---

# Technology Stack

Framework

- Laravel 13

Language

- PHP 8+

Database

- MySQL

Frontend

- Blade
- Bootstrap 5
- Vanilla JavaScript

Architecture

- MVC
- Feature-first organization

---

# Project Structure

Organize files by feature whenever possible.

Example

app/

    Http/

        Controllers/

            Student/

            Academic/

            Attendance/

            Teacher/

            Grading/

            Administration/

    Models/

    Services/

    Actions/

resources/

    views/

        students/

        academic/

        attendance/

        teacher/

        grading/

        administration/

routes/

    web.php

---

# Naming Conventions

## Tables

Plural

Example

students

teachers

teaching_assignments

---

## Models

Singular

Student

Teacher

TeachingAssignment

Assessment

QuarterlyGrade

---

## Controllers

Singular + Controller

Examples

StudentController

GuardianController

TeachingAssignmentController

AssessmentController

QuarterlyGradeController

---

## Form Requests

Feature + Action + Request

Examples

StoreStudentRequest

UpdateStudentRequest

StoreAssessmentRequest

UpdateAssessmentRequest

---

## Services

FeatureService

Examples

AttendanceService

GradingService

QRCodeService

QuarterlyGradeService

---

## Database Migrations

One responsibility per migration.

Good

CreateAssessmentsTable

Bad

CreateEverythingMigration

---

# Controllers

Controllers must remain thin.

Responsibilities

- Receive Request
- Validate using Form Request
- Call Service
- Return Response

Avoid

- Large business logic
- Complex calculations
- Multiple nested conditions

Business logic belongs inside Services.

---

# Services

All business logic belongs inside Services.

Examples

AttendanceService

Responsible for

- QR Validation
- Attendance Processing
- Late Detection

GradingService

Responsible for

- Grade Computation
- Grade Validation
- Quarterly Grade Generation

QRCodeService

Responsible for

- QR Generation
- QR Validation

---

# Models

Models should contain

- Relationships
- Accessors
- Mutators
- Query Scopes

Avoid placing large business logic inside Models.

---

# Validation

Always use Form Request validation.

Never validate directly inside controllers.

Example

StoreAssessmentRequest

UpdateAssessmentRequest

StoreStudentRequest

---

# Database

Always use

- Foreign Keys
- Indexes
- Constraints

Never duplicate information unnecessarily.

Maintain normalization.

---

# Relationships

Always define Eloquent relationships.

Examples

belongsTo()

hasMany()

hasOne()

belongsToMany() only when required.

Avoid raw joins when relationships are sufficient.

---

# Routes

Use resource routes whenever appropriate.

Examples

students

teachers

subjects

enrollments

assessments

quarterly-grades

Avoid unnecessarily deep nested routes.

---

# Route Naming

Examples

students.index

students.store

students.update

students.destroy

assessments.index

assessments.store

---

# Blade Views

Views should contain presentation logic only.

Avoid database queries inside Blade.

Avoid business computations inside Blade.

Keep templates reusable.

Use Blade components when appropriate.

---

# JavaScript

Use Vanilla JavaScript unless another library is introduced.

Separate JavaScript files by feature.

Example

student.js

attendance.js

grading.js

teacher.js

Avoid inline JavaScript whenever possible.

---

# Bootstrap

Use Bootstrap 5 components.

Maintain consistent spacing.

Prefer reusable components.

Avoid excessive nesting.

---

# Error Handling

Validate all user input.

Display user-friendly validation errors.

Never expose SQL errors to users.

Log unexpected exceptions.

---

# Transactions

Use database transactions whenever multiple related database operations occur.

Examples

Enrollment

- Create Enrollment
- Generate QR
- Create Initial Records

Attendance

- Save Attendance
- Save Email Log
- Send Notification

Grading

- Save Assessment
- Save Scores
- Generate Quarterly Grade

---

# Security

Passwords must always be hashed.

Never trust client-side validation.

Use Laravel Authorization.

Use Middleware for protected routes.

Use CSRF protection.

Validate uploaded files.

Escape user-generated output.

---

# Soft Delete / Archive

The project prefers archive functionality over permanent deletion.

When applicable

- Add status column
- Mark records as archived
- Exclude archived records from default queries

Avoid deleting academic records.

---

# Coding Style

Follow PSR-12.

Use descriptive variable names.

Avoid abbreviations.

Keep methods focused on a single responsibility.

Prefer readability over clever code.

---

# AI Development Rules

When generating Laravel code:

- Start with `06_AI_Implementation_Instructions.md`.
- Follow `CURRENT_DATABASE.json` as the current implementation.
- Follow `FINAL_ERD.dbml` as the target architecture.
- Follow `02_Database_Architecture.md` for migration planning.
- Follow `08_Database_Specification.md` for schema details.
- Follow `03_Business_Rules.md` for application behavior.
- Follow `04_Feature_Architecture.md` for module organization.
- Follow `11_Implementation_Order.md` for implementation sequence.
- Follow `12_Development_Rules.md` for project guardrails.
