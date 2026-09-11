# Point of View: Teacher

The **Teacher** persona covers classroom instructional duties: verifying period attendance, entering trimester assessment marks, monitoring academic risk indicators, and consulting student profiles.

---

## 1. Sidebar Navigation Mapping

The Teacher UI is rendered inside `resources/views/components/layouts/teacher.blade.php` with sidebar navigation defined in `resources/views/components/layouts/teacher/sidebar.blade.php`:

```text
TEACHER SIDEBAR NAVIGATION
├── General
│   ├── Room Attendance          ➔ route('room-attendance.index')                     ➔ /room-attendance
│   └── Student Management       ➔ route('teacher.student-management')                ➔ /teacher/student-management
├── Grading System
│   ├── My Classes               ➔ route('teacher.grading-system.dashboard')          ➔ /teacher/grading-system/dashboard
│   ├── Analytics                ➔ route('teacher.grading-system.analytics')          ➔ /teacher/grading-system/analytics
│   ├── At-Risk                  ➔ route('teacher.grading-system.at-risk')            ➔ /teacher/grading-system/at-risk
│   ├── Students                 ➔ route('teacher.grading-system.student-profile')    ➔ /teacher/grading-system/student-profile
│   ├── Reports                  ➔ route('teacher.grading-system.reports')            ➔ /teacher/grading-system/reports
│   └── Grading Rules            ➔ route('teacher.grading-system.grading-rules')      ➔ /teacher/grading-system/grading-rules
└── SETTINGS
    ├── Profile                  ➔ route('teacher.settings.profile')                  ➔ /teacher/settings/profile
    ├── Notifications            ➔ route('teacher.settings.notifications')            ➔ /teacher/settings/notifications
    ├── Appearance               ➔ route('teacher.settings.appearance')               ➔ /teacher/settings/appearance
    ├── Dashboard Preferences    ➔ route('teacher.settings.dashboard')                ➔ /teacher/settings/dashboard-preferences
    └── Account & Security       ➔ route('teacher.settings.security')                 ➔ /teacher/settings/security
```

---

## 2. Controller & Route Directory Structure

| Sidebar Item | HTTP Route | Controller Class | Active Blade View |
|---|---|---|---|
| **Room Attendance** | `GET /room-attendance` | `Teacher\Attendance\TeacherRoomAttendanceController@index` | `teacher/attendance/room-attendance-index.blade.php` |
| **Student Management** | `GET /teacher/student-management` | `Teacher\StudentManagement\StudentManagementController@index` | `teacher/student-management/student-management.blade.php` |
| **My Classes** | `GET /teacher/grading-system/dashboard` | `Teacher\MyClasses\GradingDashboardController@index` | `teacher/grading-system/grading-dashboard.blade.php` |
| **Grade Sheet** | `GET /teacher/grading-system/grade-sheet/{assignment}` | `Teacher\MyClasses\GradeSheetController@show` | `teacher/grading-system/grade-sheet.blade.php` |
| **Analytics** | `GET /teacher/grading-system/analytics` | `Teacher\Analytics\AnalyticsController@index` | `teacher/analytics/analytics-index.blade.php` |
| **At-Risk** | `GET /teacher/grading-system/at-risk` | `Teacher\AtRisk\AtRiskController@index` | `teacher/grading-system/at-risk-index.blade.php` |
| **Students** | `GET /teacher/grading-system/student-profile` | `Teacher\Students\StudentProfileSearchController@index` | `teacher/student-management/student-profile-search.blade.php` |
| **Reports** | `GET /teacher/grading-system/reports` | `Teacher\Reports\ReportsController@index` | `teacher/reports/reports.blade.php` |
| **Grading Rules** | `GET /teacher/grading-system/grading-rules` | `Teacher\GradingRules\GradingRulesController@index` | `teacher/grading-system/grading-rules.blade.php` |
| **Settings** | `GET /teacher/settings/*` | `Teacher\Settings\SettingsController` | `teacher/settings/*` |

---

## 3. Core Instructional Boundaries

1. **Scoped Cohort Visibility:**
   A teacher can only view and grade students enrolled in sections explicitly bound to them in `teaching_assignments`.
2. **Trimester Assessment Input:**
   Teachers input raw assessment scores across Written Works, Performance Tasks, and Term Exams. Grade transmutations are handled server-side to guarantee DepEd policy compliance.
3. **No Direct Student Enrollment or Section Deletion:**
   Teachers cannot alter student section enrollments or delete curriculum definitions.
