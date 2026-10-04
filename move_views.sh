#!/bin/bash
set -e

# Super Admin
mv resources/views/super-admin/dashboard/overview.blade.php resources/views/pov/super-admin/dashboard/
mv resources/views/super-admin/user-management/users.blade.php resources/views/pov/super-admin/user-management/
mv resources/views/super-admin/security/audit-log.blade.php resources/views/pov/super-admin/security-audit-log/
mv resources/views/super-admin/security/recent-activity.blade.php resources/views/pov/super-admin/recent-activity/

# Scanner Operator
mv resources/views/scanner-operator/dashboard/overview.blade.php resources/views/pov/scanner-operator/dashboard/
mv resources/views/scanner-operator/scan-station/station.blade.php resources/views/pov/scanner-operator/qr-station/
mv resources/views/scanner-operator/scan-station/time-in-time-out-history.blade.php resources/views/pov/scanner-operator/time-in-time-out-history/
mv resources/views/scanner-operator/scan-station/time-in-time-out-analytics.blade.php resources/views/pov/scanner-operator/time-in-time-out-history/

# School Admin
mv resources/views/school-admin/dashboard/overview.blade.php resources/views/pov/school-admin/dashboard/
mv resources/views/school-admin/monitoring/qr-station.blade.php resources/views/pov/school-admin/qr-station/
mv resources/views/school-admin/monitoring/time-in-time-out-history.blade.php resources/views/pov/school-admin/time-in-time-out-history/
mv resources/views/school-admin/monitoring/time-in-time-out-analytics.blade.php resources/views/pov/school-admin/time-in-time-out-history/
mv resources/views/school-admin/monitoring/partials/attendance-analytics.blade.php resources/views/pov/school-admin/time-in-time-out-history/partials/
mv resources/views/school-admin/monitoring/class-attendance.blade.php resources/views/pov/school-admin/attendance/
mv resources/views/school-admin/attendance/grade-level.blade.php resources/views/pov/school-admin/attendance/
mv resources/views/school-admin/attendance/section-selection.blade.php resources/views/pov/school-admin/attendance/
mv resources/views/school-admin/monitoring/emails.blade.php resources/views/pov/school-admin/emails/
mv resources/views/school-admin/teacher-management/teachers.blade.php resources/views/pov/school-admin/teachers/
mv resources/views/school-admin/teacher-management/show.blade.php resources/views/pov/school-admin/teachers/
mv resources/views/school-admin/student-management/students.blade.php resources/views/pov/school-admin/students/
mv resources/views/school-admin/student-management/section-selection.blade.php resources/views/pov/school-admin/students/
mv resources/views/school-admin/student-management/show.blade.php resources/views/pov/school-admin/students/
mv resources/views/school-admin/student-management/student-profile.blade.php resources/views/pov/school-admin/students/
mv resources/views/school-admin/student-management/student-profile-content.blade.php resources/views/pov/school-admin/students/
mv resources/views/school-admin/student-management/components/* resources/views/pov/school-admin/students/components/
mv resources/views/school-admin/student-management/partials/* resources/views/pov/school-admin/students/partials/
mv resources/views/school-admin/utilities/bulk-import.blade.php resources/views/pov/school-admin/bulk-import/
mv resources/views/school-admin/academic-setup/academic.blade.php resources/views/pov/school-admin/academic/
mv resources/views/school-admin/academic-setup/sections.blade.php resources/views/pov/school-admin/academic/
mv resources/views/school-admin/academic-setup/subjects.blade.php resources/views/pov/school-admin/academic/
mv resources/views/school-admin/academic-setup/teaching-assignments.blade.php resources/views/pov/school-admin/teaching-assignments/
mv resources/views/school-admin/academic-setup/partials/teaching-assignment-tab.blade.php resources/views/pov/school-admin/teaching-assignments/partials/
mv resources/views/school-admin/utilities/schedule-configuration.blade.php resources/views/pov/school-admin/schedule-configuration/
mv resources/views/school-admin/utilities/qr-generation.blade.php resources/views/pov/school-admin/qr-generation/
mv resources/views/school-admin/utilities/qr.blade.php resources/views/pov/school-admin/qr-generation/
mv resources/views/school-admin/utilities/qr-show.blade.php resources/views/pov/school-admin/qr-generation/
mv resources/views/school-admin/utilities/settings.blade.php resources/views/pov/school-admin/settings/
mv resources/views/school-admin/grading-setup/grades.blade.php resources/views/pov/school-admin/grades/

# Teacher
mv resources/views/teacher/dashboard.blade.php resources/views/pov/teacher/dashboard/
mv resources/views/teacher/attendance/class-attendance.blade.php resources/views/pov/teacher/attendance/
mv resources/views/teacher/attendance/room-attendance-index.blade.php resources/views/pov/teacher/attendance/
mv resources/views/teacher/attendance/room-attendance-show.blade.php resources/views/pov/teacher/attendance/
mv resources/views/teacher/attendance/time-in-time-out-history.blade.php resources/views/pov/teacher/attendance/

mv resources/views/teacher/student-management/student-management.blade.php resources/views/pov/teacher/student-management/
mv resources/views/teacher/student-management/student-profile.blade.php resources/views/pov/teacher/student-management/
mv resources/views/teacher/student-management/student-profile-content.blade.php resources/views/pov/teacher/student-management/
mv resources/views/teacher/student-management/student-profile-detail.blade.php resources/views/pov/teacher/student-management/
mv resources/views/teacher/student-management/student-profile-search.blade.php resources/views/pov/teacher/student-management/
mv resources/views/teacher/student-management/components/* resources/views/pov/teacher/student-management/components/

mv resources/views/teacher/grading-system/grading-dashboard.blade.php resources/views/pov/teacher/my-classes/
mv resources/views/teacher/grading-system/grade-sheet.blade.php resources/views/pov/teacher/my-classes/
mv resources/views/teacher/grading-system/grading-system.blade.php resources/views/pov/teacher/my-classes/
mv resources/views/teacher/grading-system/partials/common/* resources/views/pov/teacher/my-classes/partials/common/
mv resources/views/teacher/grading-system/partials/grade-sheet/* resources/views/pov/teacher/my-classes/partials/grade-sheet/
mv resources/views/teacher/grading-system/partials/system/* resources/views/pov/teacher/my-classes/partials/system/
mv resources/views/teacher/grading-system/partials/grading-breadcrumb.blade.php resources/views/pov/teacher/my-classes/partials/
mv resources/views/teacher/grading-system/partials/grading-tabs.blade.php resources/views/pov/teacher/my-classes/partials/
mv resources/views/teacher/grading-system/partials/chart-defaults.blade.php resources/views/pov/teacher/my-classes/partials/

mv resources/views/teacher/analytics/analytics-index.blade.php resources/views/pov/teacher/analytics/
mv resources/views/teacher/analytics/attendance-analytics-index.blade.php resources/views/pov/teacher/analytics/
mv resources/views/teacher/analytics/by-level-index.blade.php resources/views/pov/teacher/analytics/
mv resources/views/teacher/grading-system/sections-overview-index.blade.php resources/views/pov/teacher/analytics/
mv resources/views/teacher/grading-system/subjects-overview-index.blade.php resources/views/pov/teacher/analytics/

mv resources/views/teacher/grading-system/at-risk-index.blade.php resources/views/pov/teacher/at-risk/
mv resources/views/teacher/grading-system/at-risk-student-detail.blade.php resources/views/pov/teacher/at-risk/
mv resources/views/teacher/grading-system/partials/at-risk/* resources/views/pov/teacher/at-risk/partials/

mv resources/views/teacher/grading-system/grading-levels-index.blade.php resources/views/pov/teacher/students/
mv resources/views/teacher/grading-system/grading-sections-index.blade.php resources/views/pov/teacher/students/
mv resources/views/teacher/grading-system/grading-students-index.blade.php resources/views/pov/teacher/students/
mv resources/views/teacher/grading-system/grading-student-detail.blade.php resources/views/pov/teacher/students/

mv resources/views/teacher/reports/reports.blade.php resources/views/pov/teacher/reports/

mv resources/views/teacher/grading-system/grading-rules.blade.php resources/views/pov/teacher/grading-rules/
mv resources/views/teacher/grading-system/comp-rules.blade.php resources/views/pov/teacher/grading-rules/

mv resources/views/teacher/settings/appearance.blade.php resources/views/pov/teacher/settings/
mv resources/views/teacher/settings/dashboard-preferences.blade.php resources/views/pov/teacher/settings/
mv resources/views/teacher/settings/notifications.blade.php resources/views/pov/teacher/settings/
mv resources/views/teacher/settings/profile.blade.php resources/views/pov/teacher/settings/
mv resources/views/teacher/settings/security.blade.php resources/views/pov/teacher/settings/

mv resources/views/teacher/notifications/notifications-index.blade.php resources/views/pov/teacher/notifications/

mv resources/views/teacher/import-data/import-data.blade.php resources/views/pov/teacher/import-data/

