# Security Audit Report — CIS Gate Attendance & Student Management System

**Audit date:** 2026-07-31
**Branch audited:** `refactor/student-enrollment-workflow`
**Scope:** Full application codebase (Laravel 13 / PHP 8.3)
**Method:** Manual source-code review. Every claim below was verified directly in the code — nothing is assumed because "Laravel supports it."

> **IMPORTANT:** This is an analysis-only audit. No code was modified, refactored, or fixed.

---

## 1. Executive Summary

The application is a Laravel-based school management system (student management, enrollment, teaching assignments, schedule configuration, QR gate attendance, classroom attendance, bulk import, email monitoring, and grading). Its overall security posture is **moderate**: the primary **web application** is protected by a solid, defensible set of controls — custom authentication with bcrypt hashing, session regeneration/invalidation, role-based middleware, full CSRF coverage on web routes, parameterized Eloquent queries, Blade auto-escaping, explicit `$fillable` on all models, strict bulk-import file/row validation, and a well-designed QR classroom-scan service with ownership checks.

However, there are **critical gaps on the API surface**: several `/api/*` routes (`/api/scan`, `/api/students`, `/api/enrollment`, `/api/sections`, `/api/schedule-configuration`, `/api/school-year`, `/api/teaching-assignments`, `/api/teachers`) are **not protected by any authentication or CSRF middleware**, directly mirroring admin/scanner functionality that is properly protected on the web side. Additional weaknesses include a hardcoded default teacher password, no authorization Policies/Gates, incomplete ownership checks in the teacher room-attendance module, debug mode enabled, no security headers, and no general CRUD audit log.

The system has many genuinely implemented security mechanisms worth presenting in the capstone defense, but the API exposure must be acknowledged honestly and hardened before production.

---

## 2. Security Measures Currently Implemented

| Security Measure | Status | Location | How It Works |
|---|---|---|---|
| Custom login with validation | Implemented | `app/Services/Administration/AuthService.php`; `app/Http/Requests/.../LoginRequest.php` | Validates `email` (required/format/max) + `password`, checks account `status === 'active'`, `Hash::check()` before `Auth::login()`. |
| Password hashing (bcrypt) | Implemented | `app/Models/User.php` (`'password' => 'hashed'` cast); `.env` `BCRYPT_ROUNDS=12` | Plaintext never stored; hashed on write, verified with `Hash::check()` on login. |
| Strong password policy | Implemented | `app/Http/Controllers/AdministrationFeature/User/UserController.php` | `Password::min(8)->letters()->mixedCase()->numbers()->symbols()` + `confirmed` for user accounts. |
| Session regeneration on login | Implemented | `AuthService::attemptLogin()` → `request()->session()->regenerate()` | Mitigates session fixation. |
| Session invalidation on logout | Implemented | `AuthController::logout()` — `Auth::logout()`, `session()->invalidate()`, `session()->regenerateToken()` | Destroys session and rotates CSRF token. |
| Account-status lockout | Implemented | `AuthService::attemptLogin()` | Non-`active` users cannot log in. |
| Login rate limiting | Implemented | `routes/web.php` `throttle:5,1`; `AuthService` `RateLimiter` (5 tries → 300s lockout) | Brute-force protection on login. |
| Login audit logging | Implemented | `app/Models/LoginLog.php` + writes in `AuthService` | Records success/failure/lockout with user, email, IP, user-agent. |
| Session stored server-side | Implemented | `config/session.php` (`driver = database`) | Session data not in client cookie. |
| Session cookie hardening | Implemented | `config/session.php` (`http_only = true`, `same_site = lax`) | Blocks JS cookie reads; limits cross-site cookie sending. |
| RBAC middleware | Implemented | `app/Http/Middleware/EnsureUserHasRole.php` (aliased `role`) | `Auth::check()` then `hasRole()`, abort 403 on mismatch. |
| Route-level role groups | Implemented | `routes/web.php` — `role:admin`, `role:teacher`, `role:scanner_operator` under `auth` | Role-gated access to admin/teacher/scanner features. |
| Ownership check — classroom scan | Implemented | `app/Services/ClassroomScanService.php` → `resolveOwnedAssignment()` | Rejects scans against teaching assignments not owned by the authenticated teacher (IDOR prevention). |
| Ownership check — bulk import | Implemented | `app/Http/Controllers/Import/BulkImportController.php` → `authorizeAccess()` | 403 unless `created_by === user id` for validate/confirm/replace/cancel. |
| Self-account protection | Implemented | `UserController::update()/archive()` | Cannot modify/archive your own account. |
| Current-password confirmation | Implemented | `UserController::sharedValidationRules()` | Admin user-management requires `current_password`. |
| CSRF on web routes | Implemented | Default Laravel `web` group (`ValidateCsrfToken`) active in `bootstrap/app.php` | All web POST/PUT/PATCH/DELETE validated against session token. |
| `@csrf` on forms | Implemented | All form views (login, student, section, teacher, enrollment, emails, schedule-config, logout modal, etc.) | Hidden `_token` fields submitted with forms. |
| CSRF on AJAX | Implemented | `resources/js/ajax-crud.js`, `import.js`, `enrollment.js` — `X-CSRF-TOKEN` header + meta tag | All AJAX state changes carry the CSRF token. |
| Form Request validation | Implemented | `app/Http/Requests/` (ClassroomScan, TeachingAssignment, RoomAttendance, Grading, Import, Login) | Centralized validation rules. |
| Inline validation on writes | Implemented | `StudentController`, `EnrollmentController`, `SectionController`, `SubjectController`, `ScheduleConfigController`, `UserController`, `TeacherController`, `SchoolYearController` | Whitelisted fields, types, enums, uniques, cross-field rules. |
| SQL injection resistance | Implemented | Entire `app/` — Eloquent/Query Builder only; **no** `DB::raw`/`whereRaw`/`selectRaw`/`DB::select` | All queries parameterized via prepared statements. |
| XSS resistance (output) | Implemented | All views use `{{ }}`; **zero** `{!! !!}` in `resources/views` | Blade auto-escapes all output. |
| XSS resistance (stored) | Implemented | `strip_tags()` on names/address/email in `StudentController`/`StudentService` | Markup stripped at write time. |
| XSS resistance (DOM) | Implemented | `resources/js/enrollment.js` uses `textContent` for student data | No `innerHTML` with user data in search rendering. |
| Mass-assignment protection | Implemented | All 21 models define explicit `$fillable` | Unlisted attributes ignored on `create()/update()`. |
| File type/MIME/size validation | Implemented | `app/Http/Requests/Import/UploadImportRequest.php` — `extensions:xlsx`, `mimetypes:...spreadsheetml.sheet`, `max:10240` | Only real `.xlsx` files ≤ 10 MB accepted. |
| Non-public upload storage | Implemented | `BulkImportService::upload()` → `local` disk (`storage/app/imports/`) | Files not web-accessible. |
| Randomized upload filenames | Implemented | `Str::uuid() . '.' . ext` | Prevents path traversal / user-controlled filenames. |
| File integrity hash | Implemented | `hash_file('sha256', ...)` stored as `file_hash` | Tamper detection. |
| Import re-validation on confirm | Implemented | `BulkImportService::confirm()` re-parses/re-validates | File can't be swapped after validation. |
| Import lifecycle state machine | Implemented | `app/Enums/ImportStatus.php`; `confirm()` requires `Validated` | Prevents status manipulation / duplicate processing. |
| Import row validation + issue tracking | Implemented | `app/Services/Import/ImportRowValidator.php`; `BulkImportIssue` | Per-row validation with error/warning severities; invalid rows rejected. |
| Transactional writes | Implemented | `DB::transaction` in student creation, enrollment, import, room attendance, grading | Atomic; failures roll back. |
| DB unique constraints | Implemented | `students.student_number` unique; `enrollments (student_id, school_year_id)` unique | Enforces one-enrollment-per-student-per-year at DB level. |
| DB enums + strict mode | Implemented | Migrations (`sex`, `status`, `session_type`); `config/database.php` `'strict' => true` | Invalid values rejected; no silent truncation. |
| Section capacity enforcement | Implemented | `EnrollmentService` / `EnrollmentController` `sectionHasCapacity()` | Prevents over-enrollment server-side. |
| Sanctum token auth (classroom scan API) | Implemented | `routes/api.php` — `POST /api/classroom-scan` with `auth:sanctum` | Classroom scan requires a personal-access token. |
| Minimal data exposure in search | Implemented | `StudentController::search()` returns only `id, student_number, first_name, last_name` | Avoids returning full PII for autocomplete. |
| QR code entropy & uniqueness | Implemented | `Str::upper(Str::random(32))` in `StudentService`/`StudentController`; unique DB column | ~190-bit random tokens; no PII encoded. |
| QR server-side validation | Implemented | `ScanController::scan()`; `ClassroomScanService::resolveStudentFromQrCode()` (`is_active = true`) | Inactive/stale codes rejected. |
| Scan window enforcement | Implemented | `ScheduleResolver` + `ScanController`; `ClassroomScanService::resolveAttendanceStatus()` | IN/OUT accepted only inside valid time windows. |
| Scan flagging | Implemented | `FlaggedScan` (`late_arrival`, `invalid_checkout`) | Exceptions flagged for review. |
| Password hidden from responses | Implemented | `User::$hidden = ['password', 'remember_token']` | Passwords never in JSON output. |
| Secrets excluded from git | Implemented | `.gitignore` includes `.env*`; only `.env.example` tracked (empty key, no real SMTP creds) | Live secrets not committed. |
| Escaped email templates | Implemented | `resources/views/emails/gate-scan.blade.php` (`{{ }}`) | No HTML injection in mail. |
| No user-controlled mail headers | Implemented | Recipient from DB, subject fixed in `GateScanMail::envelope()` | Header injection closed. |
| Email log + retry limits | Implemented | `EmailLog` model; `EmailLogController::retryAll()` caps at 4 attempts | Delivery audited; retry storms bounded. |
| Error containment | Implemented | `ClassroomScanRejectedException` (explicit status codes); `ImportProcessor` per-row try/catch | No stack traces leak to clients. |
| Default API throttling | Implemented | Laravel default `throttle:api` group | Baseline abuse protection on `/api/*`. |

---

## 3. Partially Implemented Security Measures

| Measure | What Exists | What Is Missing | Why the Gap Matters |
|---|---|---|---|
| Authentication hardening | Session regeneration, invalidation, bcrypt, throttling, lockout, login audit | `SESSION_SECURE_COOKIE` not set; `SESSION_ENCRYPT=false`; no password reset; no remember-me; no 2FA | Session cookie can be sent over plain HTTP; lost passwords can't be recovered; brute-force on other endpoints is less protected. |
| Authorization | Route-level `role` middleware works and is tested | **No Policies or Gates**; all Form Request `authorize()` return `true`; several controllers lack per-record ownership checks | Authorization exists only at the route layer; object-level access (IDOR) depends on each controller/service remembering to check ownership — and some don't. |
| Teacher room-attendance authorization | Routes gated to `role:teacher`; `index()` attempts to scope by teacher | `show/edit/update/destroy`, `byTeachingAssignmentAndDate`, `bulkCreateForm`, `bulkStore` have **no ownership check** on `RoomAttendance`/`TeachingAssignment`; `index()` filters `teacher_id = auth()->id()` (User id vs Teacher id mismatch) | A teacher can view/edit/delete another teacher's room-attendance records by ID, or bulk-record attendance against another teacher's assignment. |
| Gate scan attribution | `AttendanceLog` has `scanned_by_user_id`, `device_id` columns in `$fillable` | `ScanController` never populates them; no scanner identity captured | No audit trail of who performed a gate scan. |
| Bulk import authorization | Upload/validate/confirm/replace/cancel ownership-checked | `issues()`, `exportErrors()`, `acknowledge()`, `acknowledgeAll()` do **not** run `authorizeAccess()` | Any admin can read/acknowledge another admin's import issues (contains student PII in `raw_data`). |
| Bulk import resource limits | 10 MB file cap; parser stops after 10 consecutive blank rows | **No maximum row count**; processing runs synchronously in the request | A 10 MB spreadsheet with tens of thousands of rows can exhaust memory/CPU (admin-triggered DoS). |
| Bulk import formula safety | Strong field-level validation; `strip_tags` on stored text | **No CSV/Excel formula-injection protection** (values starting with `=`, `+`, `-`, `@` are stored as-is) | If data is later exported to Excel/CSV, formulas could execute on the admin's machine. |
| Error handling | Most write paths return generic messages and log details | `APP_DEBUG=true` in `.env`; several `catch` blocks return `$e->getMessage()` (StudentController::destroy, UserController, TeacherController::store, EnrollmentController::destroy) | SQL/PDO internals can leak to clients while debug is on. |
| Auditability | LoginLog, EmailLog, import sessions/issues, app error log | **No general activity/audit log** for CRUD/admin actions (create/update/delete of students, sections, enrollments, schedules, users) | Cannot answer "who changed what and when" for most administrative actions. |
| Grading system | Services + Form Requests + validation exist (`GradingService`, `AssessmentService`, score/quarterly requests) | **No controller routes wired** to record scores/quarterly grades on this branch | Grade-recording backend logic is written but not reachable — partial implementation. |

---

## 4. Security Gaps / Not Implemented

| Security Area | Status | Risk Level | Finding | Recommendation |
|---|---|---|---|---|
| API authentication | Not Implemented | **Critical** | `/api/scan`, `/api/students`, `/api/students/{id}`, `/api/enrollment...`, `/api/sections...`, `/api/schedule-configuration...`, `/api/school-year...`, `/api/teaching-assignments`, `/api/teachers` have **no auth middleware** (and API routes are CSRF-exempt) — direct mirrors of admin/scanner functionality. | Require `auth`/`auth:sanctum` + role middleware on every state-changing API route; verify before deployment. |
| Gate scan endpoint authentication | Not Implemented | **Critical** | `POST /api/scan` accepts any valid QR code with no authentication; scanner UI posts to it without a token. Anyone with a student's QR (printed card) can create attendance. | Add auth (scanner operator token/session) + CSRF + rate limit on the scan endpoint; bind scans to the operator identity. |
| Hardcoded default teacher password | Not Implemented (safe default) | **High** | `TeacherController::store()` creates users with password `Password123`; no forced password change on first login. | Generate a random initial password, force reset on first login, or reuse the strong password policy. |
| Object-level authorization | Not Implemented | **High** | No Policies/Gates; teacher room-attendance CRUD lacks ownership checks; import `issues`/`export` lack ownership checks. | Add Policies or explicit ownership checks in every controller/service touching per-user resources. |
| Security headers | Not Implemented | **Medium** | No CSP, X-Frame-Options, X-Content-Type-Options, HSTS, Referrer-Policy, Permissions-Policy anywhere. | Add a middleware emitting security headers; set CSP for the inline scripts/third-party QR CDN. |
| Third-party script SRI | Not Implemented | **Medium** | `scanner/index.blade.php` loads `https://unpkg.com/html5-qrcode` with no Subresource Integrity. | Self-host the library or add `integrity=`/`crossorigin` attributes. |
| Debug mode in production config | Not Implemented (local only) | **Medium** | `.env` has `APP_DEBUG=true`, `APP_ENV=local`, `APP_URL=http://localhost:8000`. | Ensure `APP_DEBUG=false`, `APP_ENV=production`, HTTPS URL in deployed env. |
| General audit log | Not Implemented | **Medium** | No CRUD/administrative activity log; scans not attributed to an operator. | Introduce an activity-log model/observer for admin actions; populate `scanned_by_user_id`. |
| Password reset | Not Implemented | **Medium** | "Forgot Password?" link is `href="#"`; no reset routes/notifications. | Implement Laravel password reset with email verification. |
| Remember-me | Not Implemented | **Low** | No `remember` token flow. | Optional; only add if required. |
| Secure/HTTPS cookie flag | Not Implemented | **Medium** | `SESSION_SECURE_COOKIE` unset; cookie not forced to HTTPS. | Set `SESSION_SECURE_COOKIE=true` behind HTTPS. |
| Formula injection protection | Not Implemented | **Medium** | Import stores spreadsheet cell values as-is; export endpoints write messages/raw data back into xlsx. | Strip/reject leading `= + - @` in text fields or prefix with a quote on export. |
| Rate limiting on import/scan endpoints | Partially Implemented | **Medium** | Only default API throttle; no scan-specific limit. | Add `throttle:` on scan and import routes. |
| Duplicate school-year API routes | Not Implemented (bug) | **Low** | `api.php` registers three `POST /school-year/{id}` routes (update/destroy/restore) that shadow each other. | Register distinct methods (PUT/DELETE/PATCH). |

---

## 5. Feature-Specific Security Analysis

### Authentication
Protected by bcrypt hashing, account-status check, session regeneration, logout invalidation, login throttling + RateLimiter lockout, generic error messages, and LoginLog audit. Missing: password reset, remember-me, 2FA, forced HTTPS cookie.

### Student Management
Controllers validate LRN (12 digits, unique), sex/status enums, birthdate, guardian email; `strip_tags` on free text; `$fillable` protects mass assignment; DB unique on student_number. Concern: the **API equivalents are unauthenticated** (`/api/students`), and `destroy()` echoes `$e->getMessage()`.

### Enrollment
Strong server-side checks: active school year required, section active, capacity, duplicate (student+school year) via validation + DB unique; `DB::transaction`; service layer (`EnrollmentService`) re-checks rules for the import path. Concern: **API enrollment routes unauthenticated**.

### Teaching Assignments
Composite-duplicate check + transactional create/update (`TeachingAssignmentService`). Concern: on the teacher route group, teachers can now `store/update/destroy` teaching assignments (`teacher.teaching-assignments.*`), which duplicates admin functionality with no assignment-level ownership check.

### Schedule Configuration
Validation of level/session enum and time windows (`date_format:H:i`, `after:`); duplicate-level/session detection. Concern: **API schedule routes unauthenticated**.

### QR Attendance
Strong on the classroom side: Sanctum auth, teacher-ownership of assignment, active-enrollment section+school-year match, time-window enforcement, max two scans per day, transactional write, friendly rejection codes. Gate side: high-entropy QR, active-enrollment and window checks, state machine, flagging — **but the gate `/api/scan` endpoint is unauthenticated and unbounded** (a QR can be replayed many times, toggling IN/OUT/RE_ENTRY/RE_EXIT).

### Bulk Import
Strong: strict xlsx MIME/extension + 10 MB cap, non-public storage, UUID filenames, SHA-256 hash, required-header validation, per-row validation with cross-field rules, intra-file duplicate-LRN detection, state machine, re-validation before confirm, transactional processing, ownership check on lifecycle actions. Gaps: no row-count cap, no formula-injection sanitization, `issues/export/acknowledge` lack ownership check.

### Email Monitoring
Admin-only routes, escaped templates, no header injection, validated recipient emails, EmailLog with retry cap. Concern: synchronous sending can slow scans; email addresses (PII) visible only to admins (acceptable).

### Grade Management
Backend services and validation exist but **no wired routes** on this branch — functionally incomplete rather than insecure.

### Administrative Functions
User management is API-only and admin-gated with current-password confirmation and self-account protection; sections/subjects/school-years validated. Concern: **the same admin endpoints are exposed unauthenticated via `/api`** (sections, school-year, schedule, teaching-assignments, teachers).

---

## 6. Attack Scenarios (supported by the actual code)

> These scenarios are realistic only because of the findings above; they are the ones to be ready to discuss.

1. **Unauthenticated attendance manipulation (gate).** `routes/api.php` registers `POST /scan` with no middleware. An attacker who knows (or photographs) a printed 32-char QR code can `curl -X POST https://host/api/scan -d '{"code":"<valid qr>"}'` and create IN/OUT/RE_ENTRY/RE_EXIT records for that student, repeatedly, toggling the state machine — no login, no CSRF, no operator attribution.
2. **Unauthenticated student/enrollment creation.** `POST /api/students` calls `StudentController::store` (validates only) — anyone can insert a student + guardian + QR into the database; `POST /api/enrollment` can enroll them. Same for `/api/sections`, `/api/schedule-configuration`, `/api/school-year`, `/api/teaching-assignments`, `/api/teachers`.
3. **Cross-teacher room attendance (IDOR).** `RoomAttendanceController::update/destroy` use route-model binding with no ownership check. If a teacher changes the record ID in the URL from their own `RoomAttendance` to another teacher's, the backend updates/deletes it. Similarly `POST /teacher/room-attendance` accepts an arbitrary `teaching_assignment_id` (with a valid enrollment from that section) without verifying the assignment belongs to the teacher — the `RoomAttendanceService::create` only checks section membership, not teacher ownership.
4. **Cross-admin bulk-import data exposure.** `BulkImportController::issues()/exportErrors()/acknowledge()` don't run `authorizeAccess()`. An admin changing the import ID in the request can view (including `raw_data` PII) or acknowledge another admin's import issues.
5. **Default teacher credential.** `TeacherController::store` sets every new teacher's password to `Password123`. If a teacher hasn't changed it (no forced reset), anyone knowing the teacher's email can sign in as that teacher and reach teacher features.
6. **Excel formula injection via import.** A spreadsheet cell in e.g. "Complete Address" containing `=HYPERLINK("http://evil","Click")` is stored verbatim (no `=`, `+`, `-`, `@` sanitization). If that data is later exported to Excel/CSV (e.g., `exportErrors`), the formula can execute on the admin's machine.
7. **Debug information disclosure.** With `APP_DEBUG=true`, unhandled exceptions render stack traces; several `catch` blocks already return `$e->getMessage()` (e.g., `StudentController::destroy`, `UserController::store`) which can expose SQL/PDO internals.

---

## 7. Existing Security Strengths

1. **Consistent Eloquent-only data access** — zero raw SQL, so SQL injection is structurally prevented.
2. **Full CSRF coverage on the web application** — middleware + `@csrf` + `X-CSRF-TOKEN` on AJAX.
3. **Role-based route protection** with a custom middleware, enforced and covered by automated feature tests.
4. **Explicit `$fillable` on every model** — mass assignment is controlled everywhere.
5. **Server-side business rules** for the most important academic flows: enrollment capacity, duplicate enrollment, section status, grade-level validity.
6. **Well-designed classroom-scan service** — ownership check, active-enrollment/section/school-year match, time-window enforcement, transaction, controlled rejections.
7. **Thoughtful bulk-import design** — strict file validation, non-public storage, randomized names, integrity hash, row-level validation with issue tracking, state machine, re-validation before confirm.
8. **QR tokens are high-entropy random values** with no embedded PII, and scanning is server-side validated.
9. **Sensitive data handling** — passwords hashed + hidden, secrets gitignored, generic login errors.
10. **Login hardening** — throttling, lockout, and a LoginLog audit trail.

---

## 8. Recommended Improvements (prioritized — not implemented)

**Critical**
1. Require authentication (+ role) and CSRF/token handling on **all `/api/*` state-changing routes**; verify no route mirrors an admin/scanner action without auth.
2. Protect the gate **`/api/scan`** endpoint: authenticate the scanner operator, record `scanned_by_user_id`/`device_id`, add rate limiting, and consider per-student scan caps.

**High**
3. Remove the hardcoded `Password123` default; generate a random password and force a change on first login.
4. Add object-level authorization (Policies or explicit ownership checks) to room-attendance CRUD/bulk endpoints and to bulk-import `issues`/`export`/`acknowledge`.

**Medium**
5. Set `APP_DEBUG=false` / `APP_ENV=production` in deployment; stop echoing `$e->getMessage()`.
6. Add security headers (CSP, X-Frame-Options, X-Content-Type-Options, HSTS, Referrer-Policy) and self-host / add SRI to the QR library.
7. Add a general activity/audit log for administrative CRUD actions.
8. Implement password reset; set `SESSION_SECURE_COOKIE=true` behind HTTPS.
9. Add a bulk-import row-count cap and formula-injection sanitization.
10. Fix the duplicated `/api/school-year/{id}` routes.

**Low**
11. Consider remember-me only if required; otherwise leave out.

---

## 9. Defense-Ready Summary

### "What security measures does our system implement?"
- **Authentication:** custom login service, bcrypt hashing (12 rounds), account-status lockout, session regeneration on login, full invalidation + token rotation on logout, login throttling with a 5-attempt/300-second lockout, and a LoginLog audit of every attempt.
- **Authorization:** a custom `role` middleware (admin / teacher / scanner_operator) applied at the route-group level, with server-side 403 enforcement verified by automated tests; ownership checks in the classroom-scan and bulk-import services; self-account and current-password protections on user management.
- **CSRF:** global middleware on all web routes, `@csrf` on every form, and `X-CSRF-TOKEN` headers on all AJAX calls.
- **Input validation:** Form Requests plus rich inline rules (formats, enums, uniques, cross-field grade-level checks, existence checks).
- **Data integrity:** parameterized Eloquent queries (no raw SQL), DB unique constraints and foreign keys, DB enums, MySQL strict mode, and transactions around critical writes.
- **Bulk import:** strict `.xlsx` MIME/extension + 10 MB cap, non-public storage, randomized filenames, SHA-256 hash, per-row validation with issue tracking, re-validation before confirm, and a lifecycle state machine.
- **QR attendance:** 32-char random tokens with no PII, server-side resolution, active-enrollment checks, schedule-window enforcement, flagging of late/invalid scans, and a state machine.
- **Data protection:** passwords hashed and hidden, `.env` secrets excluded from version control.

### "What are the limitations of our security?"
- The **API routes are not yet protected** the way the web routes are — `/api/scan`, `/api/students`, `/api/enrollment`, and related endpoints currently lack authentication. This is our top production-hardening item.
- Debug mode is currently enabled (development configuration) and must be disabled in production.
- We do not yet emit advanced security headers (CSP/HSTS), have a general CRUD audit log, or force the session cookie to HTTPS-only.
- Teacher accounts are created with a known default password until changed.
- There is no password-reset flow.

### "How does the system protect against unauthorized access?"
Every application route sits behind `auth`; role groups are enforced by the custom `role` middleware; state changes require a valid CSRF token; write endpoints validate and whitelist input; and sensitive operations (classroom scans, bulk imports, user management) include backend ownership checks. The web surface is fully authenticated — the noted gap is the API surface, which we are hardening.

### "How does the system protect data integrity?"
Through layered controls: server-side validation of formats/enums/uniques; database constraints (unique student number, unique enrollment per student per school year, foreign keys, enums, strict mode); business-rule enforcement (section capacity, active-section/active-school-year, duplicate detection); and transactions around student creation, enrollment, imports, attendance, and grading. Bulk import additionally re-validates before applying changes and records issues for every rejected row.

---

## 10. Evidence / Code References

| Claim | File / Location | Evidence |
|---|---|---|
| Unauthenticated API routes | `routes/api.php` | `Route::post('/scan', [ScanController::class, 'scan']);` and the `/students`, `/enrollment`, `/sections`, `/schedule-configuration`, `/school-year`, `apiResource('teaching-assignments')`, `/teachers` routes have **no** `middleware([...])` (only `/user` and `/classroom-scan` use `auth:sanctum`; `/users` uses `auth, role:admin`). |
| Login hardening | `app/Services/Administration/AuthService.php` | `RateLimiter::tooManyAttempts($limitKey, 5)`, `RateLimiter::hit($limitKey, 300)`, `Hash::check`, status check, `session()->regenerate()`, `LoginLog::create` in every branch. |
| Logout invalidation | `app/Http/Controllers/AdministrationFeature/Authentication/AuthController.php` | `Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();` |
| Strong password policy | `app/Http/Controllers/AdministrationFeature/User/UserController.php` | `Password::min(8)->letters()->mixedCase()->numbers()->symbols()`, `confirmed`. |
| RBAC middleware | `app/Http/Middleware/EnsureUserHasRole.php`; `bootstrap/app.php` | `abort(403, ...)` on role mismatch; alias `'role' => EnsureUserHasRole::class`. |
| Classroom scan ownership | `app/Services/ClassroomScanService.php` | `resolveOwnedAssignment()` — `if ($assignment->teacher_id !== $teacherId) throw ... 403`; enrollment matched to assignment section + school year. |
| Bulk import ownership | `app/Http/Controllers/Import/BulkImportController.php` | `authorizeAccess()` — `if ($import->created_by !== request()->user()->id) abort(403)`. |
| Room-attendance IDOR gap | `app/Http/Controllers/QrSystemFeature/Logs/RoomAttendanceController.php`; `app/Services/QrSystem/RoomAttendanceService.php` | `update()/destroy()` have no ownership check; `create()` validates section membership only (not teacher ownership); `index()` filters `teacher_id = auth()->id()`. |
| CSRF on web + AJAX | `bootstrap/app.php` (web group default); views with `@csrf`; `resources/js/ajax-crud.js` | `X-CSRF-TOKEN` header sent on every fetch; CSRF middleware not disabled. |
| SQL injection resistance | `grep` over `app/**` | No `DB::raw`, `whereRaw`, `selectRaw`, `DB::select`, `DB::statement` found; all queries via Eloquent. |
| XSS resistance | `resources/views/**` | No `{!! !!}`; `{{ }}` everywhere; `enrollment.js` uses `textContent` for student data. |
| Mass assignment | `app/Models/*.php` | All 21 models define `$fillable`; none use `$guarded = []`. |
| File upload validation | `app/Http/Requests/Import/UploadImportRequest.php` | `file`, `extensions:xlsx`, `mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`, `max:10240`. |
| Non-public storage + UUID names + hash | `app/Services/Import/BulkImportService.php` | `$file->storeAs('imports', Str::uuid().'.'.ext, 'local')`; `hash_file('sha256', ...)`. |
| Import state machine | `app/Enums/ImportStatus.php`; `BulkImportService::confirm()` | `confirm()` throws unless `status === Validated`; `allowedTransitions()` map. |
| Row validation | `app/Services/Import/ImportRowValidator.php` | LRN `^\d{12}$`, `FILTER_VALIDATE_EMAIL`, date format, grade/department cross-checks, relationship enum. |
| DB constraints | `database/migrations/2026_06_11_000000_add_unique_student_school_year_to_enrollments_table.php`; students migration | Unique `(student_id, school_year_id)`; unique `student_number`. |
| Strict mode | `config/database.php` | `'strict' => true` (mysql). |
| Hardcoded default password | `app/Http/Controllers/Teacher/TeacherController.php` | `'password' => 'Password123'` on teacher user creation. |
| Debug mode | `.env` | `APP_DEBUG=true`, `APP_ENV=local` (development only). |
| Secrets in git | `.gitignore`; `.env.example` | `.env*` ignored; only `.env.example` tracked with empty `APP_KEY` and no SMTP credentials. |
| No security headers | `bootstrap/app.php`; `app/**` | No header-emitting middleware; only the `role` alias is registered. |
| Third-party script without SRI | `resources/views/scanner/index.blade.php` | `<script src="https://unpkg.com/html5-qrcode"></script>` (no `integrity`). |
| Gate scan no attribution | `app/Http/Controllers/QrSystemFeature/Scanner/ScanController.php`; `app/Models/AttendanceLog.php` | `AttendanceLog::create([...])` omits `scanned_by_user_id`/`device_id` (though fillable). |
| Email security | `app/Mail/GateScanMail.php`; `resources/views/emails/gate-scan.blade.php` | Escaped template (`{{ }}`), fixed subject, recipient from DB. |
| Login/import/email audit | `LoginLog`, `BulkImport`/`BulkImportIssue`, `EmailLog`, `Log::error` in `ScanController`/`ImportProcessor` | Activity recorded for login, import, and email; no general CRUD log. |

---

## Security Posture Score: **6 / 10**

**Scoring criteria (explicit, to avoid inflating the score):**
- The score reflects **what the code actually implements**, not what Laravel offers by default.
- Strong web-application baseline (auth, RBAC, CSRF, validation, parameterized queries, mass-assignment control, strong import/file handling) earns the core points.
- Score is capped at 6 because of the **critical, verified API authentication gap** (unauthenticated state-changing `/api/*` routes including gate attendance), the hardcoded default teacher password, missing object-level authorization in the room-attendance module, and the absence of security headers, debug-mode misconfiguration for production, and a general audit log.
- Items that are absent entirely (Policies/Gates, security headers, password reset, CRUD audit log, formula-injection protection) further prevent a higher score.
- After hardening the API surface, disabling debug mode, adding ownership checks, and emitting security headers, the system would justify a score of 8–9.
