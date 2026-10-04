# Directory Responsibilities

This guide documents the structural boundaries and architectural responsibilities of each directory in the `CIS_CAPSTONE` codebase.

---

## 1. Application Layer (`app/`)

| Directory | Responsibility | Key Classes / Patterns |
|---|---|---|
| `app/Console/Commands/` | Custom Artisan CLI utilities | `FixStudentNumbers` |
| `app/DTOs/` | Strongly typed Data Transfer Objects | `ImportRowData` (encapsulates parsed student spreadsheet rows) |
| `app/Enums/` | PHP 8.1+ Enums for domain state flags | `ImportStatus`, `ImportIssueSeverity` |
| `app/Events/` | Real-time broadcasting events | `TableUpdated` (broadcasts live table mutations via Reverb) |
| `app/Http/Controllers/` | HTTP request coordinators split strictly by role (POV) | `SuperAdmin/`, `SchoolAdmin/`, `Teacher/`, `ScannerOperator/`, `Shared/` |
| `app/Http/Middleware/` | Request filters and security barriers | `EnsureUserHasRole` (role validation) |
| `app/Http/Requests/` | Form validation rules | `Academic/`, `Grading/`, `Import/`, `SchoolAdmin/`, `Teacher/` |
| `app/Http/Resources/` | JSON API transformers | `BulkImportResource` |
| `app/Libraries/` | Third-party service wrapper adapters | `PDF/DomPdfWrapper`, `QRCode/SimpleQrCodeAdapter`, `Spreadsheet/ExcelSpreadsheetService` |
| `app/Mail/` | Mailable notifications | `GateScanMail`, `InvitationMail`, `SetupInvitationMail` |
| `app/Models/` | Shared Eloquent ORM models | `Student`, `User`, `AttendanceLog`, `TermGrade`, `Section`, `Subject`, `TeachingAssignment` |
| `app/Notifications/` | System notifications | `TeacherSystemNotification` |
| `app/Providers/` | Application bootstrap providers | `AppServiceProvider` |
| `app/Services/` | Core business logic and calculation engines | `Administration/`, `Grading/`, `Import/`, `Notification/`, `QrSystem/`, `SchoolAdmin/`, `AttendanceStateMachine.php` |

---

## 2. Views and Frontend (`resources/`)

| Directory | Responsibility | Description |
|---|---|---|
| `resources/views/auth/` | Authentication screens | Login form, setup screen, admin credential entry |
| `resources/views/components/` | Reusable Blade components | Modals, layout wrappers, headers, card UI elements |
| `resources/views/components/layouts/` | Master layouts | `admin.blade.php`, `teacher.blade.php`, and their respective sidebars |
| `resources/views/emails/` | Email templates | Markdown emails categorized under `attendance/`, `auth/`, and `notifications/` |
| `resources/views/pdf/` | Printable PDF templates | Printable reports categorized under `attendance/`, `audit/`, and `qr/` |
| `resources/views/school-admin/` | School Admin views | Academic setup, sections, subjects, teachers, attendance monitoring, utilities |
| `resources/views/super-admin/` | Super Admin views | User management, security audit log, recent activity |
| `resources/views/teacher/` | Teacher portal views | Room attendance, my classes, grade sheets, student management, analytics, settings |
| `resources/views/scanner-operator/` | Kiosk views | Fullscreen QR scan station, time-in/out logs |
| `resources/css/` | Feature stylesheets — **Bootstrap 5 + custom POV-isolated CSS only. Tailwind CSS is NOT used.** Modular CSS under `pov/<role>/<page>/`. Valid Bootstrap spacing: `p-0`–`p-5`, `m-0`–`m-5`, `gap-0`–`gap-5` (integer steps only). |
| `resources/js/` | Client-side logic | Reverb Echo listener (`echo.js`), camera scanner handler (`qr-station.js`), table handlers |

---

## 3. Routing Layer (`routes/`)

* **`routes/web.php`**: Global authentication (`/login`, `/logout`), password setup (`/setup/{token}`), and public layout previews.
* **`routes/api.php`**: RESTful API endpoints for QR scans (`/api/scan`), classroom roster verification (`/api/classroom/*`), and JSON CRUD endpoints.
* **`routes/super-admin.php`**: Super Admin route group guarded by `auth` and `role:super_admin`.
* **`routes/school-admin.php`**: School Admin route group guarded by `auth` and `role:admin`.
* **`routes/teacher.php`**: Teacher route group guarded by `auth` and `role:teacher`.
* **`routes/scanner-operator.php`**: Scanner Operator route group guarded by `auth` and `role:scanner_operator`.
* **`routes/channels.php`**: Private and presence broadcast channel authorization callbacks for Laravel Reverb.

---

## 4. Database Layer (`database/`)

* **`database/migrations/`**: Chronological database schema migrations (PostgreSQL compatible).
* **`database/seeders/`**: Initial seeds: `AdminSeeder`, `TeacherSeeder`, `AssessmentCategorySeeder`, `DatabaseSeeder`.
* **`database/factories/`**: Model factories for automated test suites.
