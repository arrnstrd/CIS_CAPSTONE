# Point of View: School Admin

The **School Admin** manages day-to-day school operations: configuring academic terms, assigning faculty to subjects, monitoring station and room attendance, and managing student cohorts.

---

## 1. Sidebar Navigation Mapping

The School Admin UI shares the layout shell `resources/views/components/layouts/admin.blade.php`, with items filtered by `$isSchoolAdmin`:

```text
SCHOOL ADMIN SIDEBAR NAVIGATION
├── Dashboard                     ➔ route('admin.dashboard')                          ➔ /dashboard
├── MONITORING
│   ├── QR Station                ➔ route('school_admin.qr-station.index')             ➔ /school-admin/qr-station
│   ├── In/Out History            ➔ route('school_admin.time-in-time-out-history.index')➔ /school-admin/time-in-time-out-history
│   ├── Attendance                ➔ route('attendance')                                ➔ /attendance
│   └── Email Logs                ➔ route('emails.index')                              ➔ /emails
├── MANAGEMENT
│   ├── Teachers                  ➔ route('school_admin.teachers.index')               ➔ /teachers
│   ├── Students                  ➔ route('student-management')                        ➔ /student-management
│   └── Bulk Import               ➔ route('bulk-import')                               ➔ /bulk-import
├── SETUP & UTILITIES
│   ├── Academic                  ➔ route('academic.index')                            ➔ /academic
│   ├── Teaching Assignments      ➔ route('teaching-assignments.index')                ➔ /teaching-assignments
│   ├── Schedule Configuration    ➔ route('schedule-configuration.index')              ➔ /schedule-configuration
│   └── QR Generation             ➔ route('qr-generation.index')                       ➔ /qr-generation
└── SYSTEM
    └── Settings                  ➔ route('settings.index')                            ➔ /settings
```

---

## 2. Controller & Route Directory Structure

| Section | Route URL | Controller Class | Primary View |
|---|---|---|---|
| **Dashboard** | `GET /dashboard` | `SchoolAdmin\Dashboard\DashboardController@index` | `school-admin/dashboard/index.blade.php` |
| **Attendance Monitoring** | `GET /attendance` | `SchoolAdmin\Attendance\ClassAttendanceController@index` | `school-admin/attendance/class-attendance.blade.php` |
| **Time-In / Out History** | `GET /school-admin/time-in-time-out-history` | `SchoolAdmin\TimeInTimeOutHistory\AttendanceLogController@index` | `school-admin/monitoring/time-in-time-out-history.blade.php` |
| **Email Logs** | `GET /emails` | `SchoolAdmin\Emails\EmailLogController@index` | `school-admin/monitoring/emails.blade.php` |
| **Teachers** | `GET /teachers` | `SchoolAdmin\Teachers\TeacherManagementController@index` | `school-admin/teacher-management/index.blade.php` |
| **Students** | `GET /student-management` | `SchoolAdmin\Students\StudentManagementController@index` | `school-admin/student-management/index.blade.php` |
| **Bulk Import** | `GET /bulk-import` | `SchoolAdmin\BulkImport\BulkImportController@index` | `school-admin/student-management/bulk-import.blade.php` |
| **Academic** | `GET /academic` | `SchoolAdmin\Academic\AcademicController@index` | `school-admin/academic-setup/index.blade.php` |
| **Assignments** | `GET /teaching-assignments` | `SchoolAdmin\TeachingAssignments\TeachingAssignmentController@index` | `school-admin/academic-setup/teaching-assignments.blade.php` |
| **Schedules** | `GET /schedule-configuration` | `SchoolAdmin\ScheduleConfiguration\ScheduleConfigController@index` | `school-admin/utilities/schedule-configuration.blade.php` |
| **QR Generation** | `GET /qr-generation` | `SchoolAdmin\QrGeneration\QrCodeController@index` | `school-admin/utilities/qr-generation.blade.php` |
| **Settings** | `GET /settings` | `SchoolAdmin\Settings\SettingsController@index` | `school-admin/utilities/settings.blade.php` |

---

## 3. Operational Responsibilities & Boundaries

1. **Academic Scaffolding:**
   School Admins configure the core structure of the school year, define subject offerings, create sections, and link teachers via `teaching_assignments`.
2. **Student Onboarding:**
   Orchestrates student intake via single registration or bulk Excel upload (`BulkImportController`).
3. **No Direct Account Creation for Teachers:**
   School Admins manage existing teacher profile metadata (specializations, active assignments), but cannot create new user credentials.
