# Backend Standards

This document defines Laravel conventions, folder structure, and the layered
backend architecture for the project. Applies to migrations, models,
controllers, form requests, services, Blade views, and routes.

---

# Part 1: Technology Stack

- Framework: Laravel 13
- Language: PHP 8+
- Database: MySQL
- Frontend: Blade, Bootstrap 5, Vanilla JavaScript
- Architecture: MVC, feature-first organization

---

# Part 2: Project Structure

The project follows a **feature-first architecture** — files grouped by
major system features rather than database tables.

## Controllers

```
app/Http/Controllers/
├── AcademicFeature/
│   ├── Enrollment/
│   ├── SchoolYear/
│   ├── Section/
│   └── Subject/
├── Student/
├── Teacher/
├── QrSystemFeature/
│   ├── Attendance/
│   ├── Scanner/
│   ├── QrCode/
│   ├── Configuration/
│   └── EmailLog/
├── GradingSystemFeature/
│   ├── Assessment/
│   ├── AssessmentCategory/
│   ├── StudentScore/
│   ├── QuarterlyGrade/
│   └── GradingPeriod/
├── AdministrationFeature/
│   ├── User/
│   ├── Authentication/
│   ├── Authorization/
│   └── Archive/
└── Reports/
```

### Folder Responsibilities

- **Academic** — Enrollment, School Year, Section, Subject
- **Student** — Student management, profile, search
- **Teacher** — Teacher management, Teaching Assignment, Teacher Dashboard, Master List
- **QrSystem** — Attendance (gate + room), Scanner (gate + classroom), QrCode generation/export, Configuration (schedule/scanner), EmailLog
- **Grading** — Assessment, AssessmentCategory, StudentScore, QuarterlyGrade, GradingPeriod
- **Administration** — User Management, Authentication, Authorization, Archive
- **Reports** — Attendance reports, Grade reports, Student reports

## Future Backend Layers

Follow the same feature-first organization for `Services/`, `Requests/`,
`Policies/`, `Resources/`, `Actions/`, `Events/`:

```
Services/
├── Academic/
├── Student/
├── Teacher/
├── QrSystem/
├── Grading/
├── Administration/
└── Reports/
```

## Foldering Rules

- Organize by feature first, then subfeature.
- Do not organize controllers solely by database table.
- Avoid controllers directly under root `Controllers/` unless framework base controllers.
- Singular controller names (`StudentController`, `SectionController`).
- Create subfolders only when they improve organization.
- New top-level feature folders only for major new modules.

---

# Part 3: Layered Architecture

```
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
```

## Controllers
Thin. Receive HTTP requests → call Form Requests for validation → delegate
to Services → return responses/views.

Must NOT contain: grade computations, attendance processing, QR generation
logic, database transactions, complex business rules.

## Validation Layer
Always use Laravel Form Requests. Never validate directly inside controllers.
Examples: `StoreStudentRequest`, `UpdateStudentRequest`, `StoreAssessmentRequest`.

## Service Layer
All business logic lives here.

- **AttendanceService** — QR validation, attendance processing, late detection, email trigger
- **GradingService** — assessment creation, grade computation, quarterly grade generation
- **StudentService** — student registration, updates
- **QRCodeService** — QR generation, QR validation

## Model Layer
Relationships, query scopes, accessors, mutators only. No business logic.
FK directions must follow `DATABASE.md` exactly — do not infer from naming.

## Database Layer
Schema defined in `CURRENT_DATABASE.json` / `FINAL_ERD.dbml`. Only modify
through Laravel migrations — never modify schema directly.

## Transactions
Use DB transactions whenever multiple related records are created/updated:
- Enrollment: create enrollment → generate QR
- Attendance: save attendance → save email log → send notification
- Grading: save assessment → save student scores → generate quarterly grade

## Authorization
Use Laravel Authorization (Policies/Gates). Access controlled by role
(Administrator: full access; Teacher: assigned classes only).

## Error Handling
Validate all input. User-friendly validation errors. Never expose SQL
errors or stack traces. Log unexpected exceptions.

## Soft Delete / Archive
Prefer archive functionality over permanent deletion — add a `status`
column, mark records archived, exclude archived records from default
queries. Avoid deleting academic records.

---

# Part 4: Naming Conventions

| Type | Convention | Examples |
|---|---|---|
| Tables | plural, snake_case | `students`, `teaching_assignments` |
| Models | singular | `Student`, `TeachingAssignment`, `QuarterlyGrade` |
| Controllers | singular + `Controller` | `StudentController`, `TeachingAssignmentController` |
| Form Requests | Feature + Action + `Request` | `StoreStudentRequest`, `UpdateAssessmentRequest` |
| Services | Feature + `Service` | `AttendanceService`, `GradingService`, `QRCodeService` |
| Migrations | one responsibility per file | `CreateAssessmentsTable` (not `CreateEverythingMigration`) |
| Routes | resource-style, dot notation | `students.index`, `assessments.store` |

## JavaScript
Vanilla JS, separated by feature: `student.js`, `attendance.js`, `grading.js`, `teacher.js`. Avoid inline JS.

## Blade
Presentation logic only — no DB queries or business computations in Blade. Use Blade components for reuse.

## Bootstrap
Bootstrap 5 components, consistent spacing, reusable components, avoid excessive nesting.

## Coding Style
PSR-12. Descriptive names, no abbreviations. Single-responsibility methods. Readability over cleverness.

## Security
Passwords always hashed. Never trust client-side validation alone. Use Middleware for protected routes, CSRF protection, validated file uploads, escaped output.

---

# Part 5: AI Development Rules

When generating backend code, apply this document together with `AI_GUIDE.md`
and `DATABASE.md`. In priority order for backend code specifically:

1. This document (`BACKEND_STANDARDS.md`) for architecture and conventions.
2. `DATABASE.md` for schema and business rules.
3. `AI_GUIDE.md` for implementation sequence and guardrails.