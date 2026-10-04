<?php

use App\Http\Controllers\Teacher\Analytics\AnalyticsController;
use App\Http\Controllers\Teacher\Analytics\AttendanceAnalyticsController;
use App\Http\Controllers\Teacher\Analytics\ByLevelController;
use App\Http\Controllers\Teacher\Analytics\SectionsOverviewController;
use App\Http\Controllers\Teacher\Analytics\SubjectsOverviewController;
use App\Http\Controllers\Teacher\AtRisk\AtRiskController;
use App\Http\Controllers\Teacher\Attendance\AttendanceLogController;
use App\Http\Controllers\Teacher\Attendance\TeacherRoomAttendanceController;
use App\Http\Controllers\Teacher\GradingRules\CompRulesController;
use App\Http\Controllers\Teacher\GradingRules\GradingRulesController;
use App\Http\Controllers\Teacher\GradingRules\GradingFormulaImportController;
use App\Http\Controllers\Teacher\ImportData\DepEdClassRecordImportController;
use App\Http\Controllers\Teacher\ImportData\ImportDataController;
use App\Http\Controllers\Teacher\MyClasses\GradeSheetController;
use App\Http\Controllers\Teacher\MyClasses\GradingDashboardController;
use App\Http\Controllers\Teacher\MyClasses\GradingSystemOverviewController;
use App\Http\Controllers\Teacher\Notifications\NotificationController;
use App\Http\Controllers\Teacher\Reports\ReportsController;
use App\Http\Controllers\Teacher\Settings\SettingsController;
use App\Http\Controllers\Teacher\StudentManagement\StudentManagementController;
use App\Http\Controllers\Teacher\StudentManagement\StudentProfileController;
use App\Http\Controllers\Teacher\Students\GradingLevelsController;
use App\Http\Controllers\Teacher\Students\StudentProfileSearchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:teacher'])->group(function () {

    // ----------------------------------------------------
    // Teacher Student Management
    // ----------------------------------------------------
    Route::get('/teacher/student-management', [StudentManagementController::class, 'index'])
        ->name('teacher.student-management');
    Route::get('/teacher/student-management/export', [StudentManagementController::class, 'export'])
        ->name('teacher.student-management.export');

    // General Teacher Student Profile
    Route::get('/teacher/student-profile/{student}/summary', [StudentProfileController::class, 'summary'])
        ->name('teacher.student-profile.summary');
    Route::get('/teacher/student-profile/{student}', [StudentProfileController::class, 'show'])
        ->name('teacher.student-profile');

    // ----------------------------------------------------
    // GRADING SYSTEM: My Classes
    // ----------------------------------------------------
    Route::get('/teacher/grading-system', [GradingSystemOverviewController::class, 'index'])
        ->name('teacher.grading-system');
    Route::get('/teacher/grading-system/dashboard', [GradingDashboardController::class, 'index'])
        ->name('teacher.grading-system.dashboard');
    Route::get('/teacher/grading-system/debug-risk-scores', [GradingDashboardController::class, 'debugRiskScores'])
        ->name('teacher.grading-system.debug-risk-scores');

    // Grade Sheet
    Route::get('/teacher/grading-system/grade-sheet/{teachingAssignmentId}', [GradeSheetController::class, 'show'])
        ->name('teacher.grading-system.grade-sheet');
    Route::post('/teacher/grading-system/grade-sheet/assessment', [GradeSheetController::class, 'storeAssessment'])
        ->name('teacher.grading-system.grade-sheet.assessment');
    Route::post('/teacher/grading-system/grade-sheet/score', [GradeSheetController::class, 'storeScore'])
        ->name('teacher.grading-system.grade-sheet.score');
    Route::get('/teacher/grading-system/assessments/log', [GradeSheetController::class, 'getAssessmentLog'])
        ->name('teacher.grading-system.assessments.log');
    Route::put('/teacher/grading-system/grade-sheet/assessment/{assessmentId}', [GradeSheetController::class, 'updateAssessment'])
        ->name('teacher.grading-system.grade-sheet.assessment.update');
    Route::delete('/teacher/grading-system/grade-sheet/assessment/{assessmentId}', [GradeSheetController::class, 'destroyAssessment'])
        ->name('teacher.grading-system.grade-sheet.assessment.destroy');
    Route::post('/teacher/grading-system/grade-sheet/assessment/{assessmentId}/scores', [GradeSheetController::class, 'storeExtraScores'])
        ->name('teacher.grading-system.grade-sheet.assessment.scores');

    // Import Data
    Route::get('/teacher/grading-system/import-data/{teachingAssignmentId?}', [ImportDataController::class, 'index'])
        ->name('teacher.grading-system.import-data');
    Route::get('/teacher/grading-system/import-data/download-template/{teachingAssignmentId}', [DepEdClassRecordImportController::class, 'downloadTemplate'])
        ->name('teacher.grading-system.import-data.download-template');
    Route::post('/teacher/grading-system/import-data/inspect', [DepEdClassRecordImportController::class, 'inspect'])
        ->name('teacher.grading-system.import-data.inspect');
    Route::post('/teacher/grading-system/import-data/process', [DepEdClassRecordImportController::class, 'process'])
        ->name('teacher.grading-system.import-data.process');

    // Reports
    Route::get('/teacher/grading-system/reports', [ReportsController::class, 'index'])
        ->name('teacher.grading-system.reports');
    Route::get('/teacher/grading-system/reports/class-record/{teachingAssignmentId}', [ReportsController::class, 'classRecordData'])
        ->name('teacher.grading-system.reports.class-record');
    Route::get('/teacher/grading-system/reports/student-academic-record/{teachingAssignmentId}/{enrollmentId}', [ReportsController::class, 'studentAcademicRecord'])
        ->name('teacher.grading-system.reports.student-academic-record');
    Route::get('/teacher/grading-system/reports/students/{teachingAssignmentId}', [ReportsController::class, 'studentsData'])
        ->name('teacher.grading-system.reports.students');
    Route::post('/teacher/grading-system/reports/{teachingAssignmentId}/export-excel', [ReportsController::class, 'exportExcel'])
        ->name('teacher.grading-system.reports.export-excel');
    Route::post('/teacher/grading-system/reports/{teachingAssignmentId}/export-pdf', [ReportsController::class, 'exportPdf'])
        ->name('teacher.grading-system.reports.export-pdf');

    // ----------------------------------------------------
    // GRADING SYSTEM: Students
    // ----------------------------------------------------
    Route::get('/teacher/grading-system/grades', [GradingLevelsController::class, 'index'])
        ->name('teacher.grading-system.grades');
    Route::get('/teacher/grading-system/grades/{gradeLevel}', [GradingLevelsController::class, 'show'])
        ->name('teacher.grading-system.grades.show');
    Route::get('/teacher/grading-system/sections/{sectionId}', [GradingLevelsController::class, 'students'])
        ->name('teacher.grading-system.sections.show');
    Route::get('/teacher/grading-system/students/{enrollmentId}', [GradingLevelsController::class, 'studentDetail'])
        ->name('teacher.grading-system.students.show');

    // Student Profile - Grading System
    Route::get('/teacher/grading-system/student-profile', [StudentProfileSearchController::class, 'index'])
        ->name('teacher.grading-system.student-profile');
    Route::get('/teacher/grading-system/student-profile/{enrollmentId}', [StudentProfileSearchController::class, 'show'])
        ->name('teacher.grading-system.student-profile.show');
    Route::post('/teacher/grading-system/student-profile/{enrollmentId}/notes', [StudentProfileSearchController::class, 'storeAcademicNote'])
        ->name('teacher.student-profile.academic-notes.store');
    Route::put('/teacher/grading-system/student-profile/{enrollmentId}/notes/{noteId}', [StudentProfileSearchController::class, 'updateAcademicNote'])
        ->name('teacher.student-profile.academic-notes.update');
    Route::delete('/teacher/grading-system/student-profile/{enrollmentId}/notes/{noteId}', [StudentProfileSearchController::class, 'destroyAcademicNote'])
        ->name('teacher.student-profile.academic-notes.destroy');

    // ----------------------------------------------------
    // GRADING SYSTEM: Analytics
    // ----------------------------------------------------
    Route::get('/teacher/grading-system/analytics', [AnalyticsController::class, 'index'])
        ->name('teacher.grading-system.analytics');
    Route::get('/teacher/grading-system/attendance', [AttendanceAnalyticsController::class, 'index'])
        ->name('teacher.grading-system.attendance');
    Route::get('/teacher/grading-system/correlation', [AttendanceAnalyticsController::class, 'correlation'])
        ->name('teacher.grading-system.correlation');
    Route::get('/teacher/grading-system/by-level', [ByLevelController::class, 'index'])
        ->name('teacher.grading-system.by-level');
    Route::get('/teacher/grading-system/sections', [SectionsOverviewController::class, 'index'])
        ->name('teacher.grading-system.sections');
    Route::get('/teacher/grading-system/subjects', [SubjectsOverviewController::class, 'index'])
        ->name('teacher.grading-system.subjects');

    // ----------------------------------------------------
    // GRADING SYSTEM: At-Risk
    // ----------------------------------------------------
    Route::get('/teacher/grading-system/at-risk', [AtRiskController::class, 'index'])
        ->name('teacher.grading-system.at-risk');
    Route::get('/teacher/grading-system/at-risk/intervention/prepare', [AtRiskController::class, 'prepareIntervention'])
        ->name('teacher.grading-system.at-risk.intervention.prepare');
    Route::post('/teacher/grading-system/at-risk/intervention/send', [AtRiskController::class, 'sendIntervention'])
        ->name('teacher.grading-system.at-risk.intervention.send');
    Route::get('/teacher/grading-system/at-risk/{enrollmentId}', [AtRiskController::class, 'show'])
        ->whereNumber('enrollmentId')
        ->name('teacher.grading-system.at-risk.show');
    Route::post('/teacher/grading-system/at-risk/{enrollmentId}/remarks', [AtRiskController::class, 'storeRemark'])
        ->name('teacher.grading-system.at-risk.remarks.store');
    Route::put('/teacher/grading-system/at-risk/{enrollmentId}/remarks/{remarkId}', [AtRiskController::class, 'updateRemark'])
        ->name('teacher.grading-system.at-risk.remarks.update');
    Route::delete('/teacher/grading-system/at-risk/{enrollmentId}/remarks/{remarkId}', [AtRiskController::class, 'destroyRemark'])
        ->name('teacher.grading-system.at-risk.remarks.destroy');
    Route::post('/teacher/grading-system/at-risk/{enrollmentId}/follow-ups', [AtRiskController::class, 'storeFollowUp'])
        ->name('teacher.grading-system.at-risk.follow-ups.store');

    // ----------------------------------------------------
    // GRADING SYSTEM: Grading Rules
    // ----------------------------------------------------
    Route::get('/teacher/grading-system/comp-rules', [CompRulesController::class, 'index'])
        ->name('teacher.grading-system.comp-rules');
    Route::put('/teacher/grading-system/comp-rules', [CompRulesController::class, 'update'])
        ->name('teacher.grading-system.comp-rules.update');
    Route::get('/teacher/grading-system/grading-rules', [GradingRulesController::class, 'index'])
        ->name('teacher.grading-system.grading-rules');
    Route::put('/teacher/grading-system/grading-rules', [GradingRulesController::class, 'update'])
        ->name('teacher.grading-system.grading-rules.update');
    Route::post('/teacher/grading-system/grading-rules/restore-defaults', [GradingRulesController::class, 'restoreDefaults'])
        ->name('teacher.grading-system.grading-rules.restore-defaults');

    // ----------------------------------------------------
    // GENERAL: Room Attendance & In/Out History
    // ----------------------------------------------------
    Route::redirect('/teacher/attendance', '/teacher/room-attendance')
        ->name('teacher.attendance');
    Route::redirect('/teacher/time-in-time-out-history', '/teacher/room-attendance')
        ->name('teacher.time-in-time-out-history.index');
    Route::get('/teacher/dashboard', [TeacherRoomAttendanceController::class, 'index'])
        ->name('teacher.dashboard');
    Route::get('/teacher/room-attendance', [TeacherRoomAttendanceController::class, 'index'])
        ->name('room-attendance.index');
    Route::get('/teacher/room-attendance/{section}', [TeacherRoomAttendanceController::class, 'show'])
        ->name('room-attendance.show');
    Route::post('/teacher/room-attendance/{section}/{enrollment}/verify', [TeacherRoomAttendanceController::class, 'verify'])
        ->name('room-attendance.verify');
    Route::post('/teacher/room-attendance/{section}/bulk-verify', [TeacherRoomAttendanceController::class, 'bulkVerify'])
        ->name('room-attendance.bulk-verify');
    Route::get('/teacher/room-attendance/{section}/{enrollment}/history', [TeacherRoomAttendanceController::class, 'history'])
        ->name('room-attendance.history');

    // ----------------------------------------------------
    // SETTINGS
    // ----------------------------------------------------
    Route::redirect('/teacher/settings', '/teacher/settings/profile');
    Route::get('/teacher/settings/profile', [SettingsController::class, 'profile'])
        ->name('teacher.settings.profile');
    Route::get('/teacher/settings/notifications', [SettingsController::class, 'notifications'])
        ->name('teacher.settings.notifications');
    Route::get('/teacher/settings/dashboard', [SettingsController::class, 'dashboard'])
        ->name('teacher.settings.dashboard');
    Route::get('/teacher/settings/security', [SettingsController::class, 'security'])
        ->name('teacher.settings.security');
    Route::put('/teacher/settings/password', [SettingsController::class, 'updatePassword'])
        ->name('teacher.settings.password.update');
    Route::put('/teacher/settings/notifications', [SettingsController::class, 'updateNotificationPreferences'])
        ->name('teacher.settings.notifications.update');
    Route::put('/teacher/settings/dashboard', [SettingsController::class, 'updateDashboardPreferences'])
        ->name('teacher.settings.dashboard.update');

    // ----------------------------------------------------
    // NOTIFICATIONS
    // ----------------------------------------------------
    Route::get('/teacher/notifications', [NotificationController::class, 'index'])
        ->name('teacher.notifications.index');
    Route::patch('/teacher/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('teacher.notifications.mark-as-read');
    Route::post('/teacher/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('teacher.notifications.mark-all-as-read');
    Route::delete('/teacher/notifications/{id}', [NotificationController::class, 'destroy'])
        ->name('teacher.notifications.destroy');
    Route::post('/teacher/notifications/bulk-delete', [NotificationController::class, 'bulkDestroy'])
        ->name('teacher.notifications.bulk-delete');
});

