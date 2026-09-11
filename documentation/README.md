# CIS Capstone — System Documentation Hub

Welcome to the internal engineering and architectural documentation hub for **Concepcion Integrated School (CIS) QR Station Attendance, Classroom Verification, and Student Information System (`CIS_CAPSTONE`)**.

---

## Source of Truth Declaration

> [!IMPORTANT]
> **The application codebase itself is the ultimate and absolute source of truth.**
> If any discrepancy is found between this documentation and the active codebase (`app/`, `routes/`, `resources/`, `database/`, `config/`), **the code wins**. Never implement or assume behavior based on outdated documentation. Update this documentation immediately when architectural, routing, or domain logic changes occur.

---

## Purpose & Target Audience

* **Audience:** Core developers, software architects, QA engineers, and automated AI coding assistants working on the CIS Capstone repository.
* **Objective:** Provide a clean, grounded, navigable reference explaining system boundaries, user roles (POVs), data pipelines (self-service QR station attendance, classroom verification, trimester grading), and development workflows without fluff or obsolete legacy paradigms.

---

## Category Index (01 — 07)

```
documentation/
├── 01_environment_setup/      # Local runtime prerequisites (PHP, Composer, Node, PostgreSQL), Docker, and seeding
├── 02_codebase_context/       # Architectural lifecycle (Request -> Route -> Controller -> Service -> Model -> View), core packages
├── 03_auth_demo_access/       # Role boundaries, dev authentication, seed accounts, and security access tiers
├── 04_business_logic/         # Core domain engines: self-service QR station state machine, attendance verification, grading rules
├── 05_point_of_views/         # Actor breakdowns mapped directly to UI sidebars: Super Admin, School Admin, Teacher, Scanner Operator
├── 06_session_logs/           # Chronological architectural refactor logs and task modification records
└── 07_technical_reference/    # Database schema rules, routing architecture, controllers/services contract, coding standards
```

### Folder Responsibilities

| Directory | Title | Core Scope |
|---|---|---|
| [`01_environment_setup/`](./01_environment_setup/) | **Environment Setup** | System dependencies (PHP 8.3+, Laravel 13.x, Node 20+, PostgreSQL), `.env` variable specifications, Docker configs, and setup commands. |
| [`02_codebase_context/`](./02_codebase_context/) | **Codebase Context** | Request lifecycles, directory responsibilities, and third-party dependencies (`laravel/reverb`, `simplesoftwareio/simple-qrcode`, `phpoffice/phpspreadsheet`, `barryvdh/laravel-dompdf`). |
| [`03_auth_demo_access/`](./03_auth_demo_access/) | **Auth & Demo Access** | Approved seed accounts (`AdminSeeder`, `TeacherSeeder`), role constants (`ROLE_SUPER_ADMIN`, `ROLE_ADMIN`, `ROLE_TEACHER`, `ROLE_SCANNER_OPERATOR`), and token invitation mechanics. |
| [`04_business_logic/`](./04_business_logic/) | **Business Logic** | Detailed module workflows: Self-Service QR Station State Machine (`AttendanceStateMachine`), Classroom Verification (`QrAttendance`), Student Enrollment, and Grading. |
| [`05_point_of_views/`](./05_point_of_views/) | **Point of Views (POVs)** | Detailed specifications for the 4 platform personas, perfectly mapped to their navigation sidebar items and route namespaces. |
| [`06_session_logs/`](./06_session_logs/) | **Session Logs** | Chronological record of refactor sessions, codebase modifications, verification outputs, and handover notes. |
| [`07_technical_reference/`](./07_technical_reference/) | **Technical Reference** | Database naming conventions, route registration via `bootstrap/app.php`, middleware stacks, and service-layer separation rules. |

---

## Documentation Maintenance Guidelines

1. **Reality Grounding:** Every route, controller, method, and relationship documented must exist in the active codebase. Use `php artisan route:list` and actual codebase search to confirm.
2. **Zero Secrets in Docs:** Never record production passwords, APP_KEY strings, private database connection credentials, or API secret keys in any markdown document. Always use placeholders.
3. **No Hallucinations / Mark Unverified:** If a workflow is incomplete or under development, explicitly mark it with `[Requires Verification]`.
4. **No Redundant Duplication:** Link between documents using cross-referencing rather than copying large architectural blocks.

---

## AI Assistant Initialization Protocol

When starting a new session with an AI coding partner (e.g., Antigravity, Claude, Cursor), paste the following directive:

> **System Prompt for AI Coding Partner:**
> "You are an Application Architect and Senior Laravel Engineer working on `CIS_CAPSTONE`.
> Before making changes or answering architectural questions:
> 1. Read `documentation/README.md` and relevant docs in `documentation/01_*` through `documentation/07_*`.
> 2. The codebase is the ultimate source of truth. Always check the active code (`app/`, `routes/`, `resources/views/`) before writing code.
> 3. Respect the Point-of-View (POV) structure (`SuperAdmin`, `SchoolAdmin`, `Teacher`, `ScannerOperator`) and strict service/controller separation.
> 4. Do not invent legacy non-trimester grading concepts or phantom routes.
> 5. Never commit or leak real secrets.
> 6. **SESSION LOGGING REQUIREMENT:** After reading documentation, create a session log in `documentation/06_session_logs/` following the format specified in `documentation/06_session_logs/README.md`.
> 7. Log every coding session with date/time, files modified, changes summary, and purpose.
> 8. Inform the user that all sessions are logged for transparency and merge conflict resolution.
> Acknowledge you have loaded the architecture context and are ready."

## Session Logging Requirement

**AI MUST create session logs when users ask to read documentation.** This ensures:

- Complete transparency into all code changes
- Historical record for merge conflict resolution
- Context for understanding codebase evolution
- Purpose documentation for all modifications

See [`06_session_logs/README.md`](./06_session_logs/README.md) for detailed logging instructions and format requirements.
