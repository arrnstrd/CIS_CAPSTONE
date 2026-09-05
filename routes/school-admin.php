<?php

use App\Http\Controllers\SchoolAdmin\Academic\AcademicController;
use App\Http\Controllers\SchoolAdmin\Academic\SchoolYearController;
use App\Http\Controllers\SchoolAdmin\Academic\SectionController;
use App\Http\Controllers\SchoolAdmin\Academic\SubjectController;
use App\Http\Controllers\SchoolAdmin\Attendance\ClassAttendanceController;
use App\Http\Controllers\SchoolAdmin\BulkImport\BulkImportController;
use App\Http\Controllers\SchoolAdmin\Dashboard\DashboardController;
use App\Http\Controllers\SchoolAdmin\Emails\EmailLogController;
use App\Http\Controllers\SchoolAdmin\QrGeneration\QrCodeController;
use App\Http\Controllers\SchoolAdmin\QrStation\QrStationController;
use App\Http\Controllers\SchoolAdmin\QrStation\ScanController;
use App\Http\Controllers\SchoolAdmin\ScheduleConfiguration\ScheduleConfigController;
use App\Http\Controllers\SchoolAdmin\Settings\SettingsController;
use App\Http\Controllers\SchoolAdmin\Students\StudentManagementController;
use App\Http\Controllers\SchoolAdmin\Students\StudentProfileController;
use App\Http\Controllers\SchoolAdmin\Teachers\TeacherManagementController;
use App\Http\Controllers\SchoolAdmin\TeachingAssignments\TeachingAssignmentController;
use App\Http\Controllers\SchoolAdmin\TimeInTimeOutHistory\AttendanceLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,teacher'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    // Monitoring: QR Station
    Route::get('/school-admin/qr-station', [QrStationController::class, 'index'])
        ->name('school_admin.qr-station.index');
    Route::post('/school-admin/qr-station/scan', [ScanController::class, 'scan'])
        ->name('school_admin.qr-station.scan');

    // Monitoring: In/Out History
    Route::get('/school-admin/time-in-time-out-history', [AttendanceLogController::class, 'index'])
        ->name('school_admin.time-in-time-out-history.index');
    Route::get('/school-admin/time-in-time-out-history/analytics', [AttendanceLogController::class, 'analytics'])
        ->name('school_admin.time-in-time-out-history.analytics');
    Route::get('/school-admin/time-in-time-out-history/analytics/download-pdf', [AttendanceLogController::class, 'downloadAnalyticsPdf'])
        ->name('school_admin.time-in-time-out-history.analytics-pdf');
    Route::get('/school-admin/time-in-time-out-history/download', [AttendanceLogController::class, 'download'])
        ->name('school_admin.time-in-time-out-history.download');
    Route::get('/school-admin/time-in-time-out-history/download-pdf', [AttendanceLogController::class, 'downloadPdf'])
        ->name('school_admin.time-in-time-out-history.download-pdf');

    // Monitoring: Attendance
    Route::get('/attendance', [ClassAttendanceController::class, 'index'])
        ->name('attendance');
    Route::get('/school_admin/attendance/grade_level', [ClassAttendanceController::class, 'gradeLevel'])
        ->name('attendance.grade-level');
    Route::get('/school_admin/attendance/section/{grade}', [ClassAttendanceController::class, 'section'])
        ->name('attendance.section');

    // Monitoring: Email Logs
    Route::get('/emails', [EmailLogController::class, 'index'])
        ->name('emails.index');
    Route::post('/emails/{id}/retry', [EmailLogController::class, 'retry'])
        ->name('retry.email');
    Route::post('/emails', [EmailLogController::class, 'retryAll'])
        ->name('retryAll.email');

    // Management: Teachers
    Route::prefix('teachers')->group(function () {
        Route::get('/', [TeacherManagementController::class, 'index'])
            ->name('teachers.index');
        Route::get('/{teacher}', [TeacherManagementController::class, 'show'])
            ->name('teachers.show');
        Route::put('/{id}', [TeacherManagementController::class, 'update'])
            ->name('teachers.update');
        Route::delete('/{id}', [TeacherManagementController::class, 'destroy'])
            ->name('teachers.destroy');
        Route::patch('/{id}/restore', [TeacherManagementController::class, 'restore'])
            ->name('teachers.restore');
    });

    // Management: Students
    Route::get('/student-management', [StudentManagementController::class, 'index'])
        ->name('student-management.index');
    Route::get('/student-management/grade/{grade}', [StudentManagementController::class, 'byGrade'])
        ->name('student-management.grade');
    Route::get('/student-management/grade/{grade}/section/{section}', [StudentManagementController::class, 'bySection'])
        ->name('student-management.section');
    Route::get('/student-management/grade/{grade}/section/{section}/export', [StudentManagementController::class, 'export'])
        ->name('student-management.section.export');
    Route::post('/students', [StudentManagementController::class, 'store'])
        ->name('student.store');
    Route::get('/students/search', [StudentManagementController::class, 'search'])
        ->name('students.search');
    Route::put('/students/{id}', [StudentManagementController::class, 'update'])
        ->name('students.update');
    Route::delete('/students/{id}', [StudentManagementController::class, 'destroy'])
        ->name('students.destroy');

    Route::get('/student-profile', fn() => view('pov.school-admin.students.student-profile'));
    Route::get('/student-profile/{student}', [StudentProfileController::class, 'show'])
        ->name('student.profile');
    Route::put('/student-profile/{student}/info', [StudentProfileController::class, 'updateInfo'])
        ->name('student.profile.update-info');
    Route::put('/student-profile/{student}/guardian', [StudentProfileController::class, 'updateGuardian'])
        ->name('student.profile.update-guardian');

    // Management: Bulk Import
    Route::get('/bulk-import', fn() => view('pov.school-admin.bulk-import.bulk-import'))->name('bulk-import');
    Route::prefix('import')->name('import.')->group(function () {
        Route::post('/upload', [BulkImportController::class, 'upload'])->name('upload');
        Route::get('/active', [BulkImportController::class, 'active'])->name('active');
        Route::get('/history/list', [BulkImportController::class, 'history'])->name('history');
        Route::get('/template/download', [BulkImportController::class, 'downloadTemplate'])->name('template');
        Route::get('/{import}', [BulkImportController::class, 'show'])->name('show');
        Route::get('/{import}/status', [BulkImportController::class, 'status'])->name('status');
        Route::post('/{import}/validate', [BulkImportController::class, 'validate'])->name('validate');
        Route::post('/{import}/replace-file', [BulkImportController::class, 'replaceFile'])->name('replace-file');
        Route::post('/{import}/confirm', [BulkImportController::class, 'confirm'])->name('confirm');
        Route::post('/{import}/cancel', [BulkImportController::class, 'cancel'])->name('cancel');
        Route::get('/{import}/issues', [BulkImportController::class, 'issues'])->name('issues');
        Route::post('/{import}/issues/acknowledge-all', [BulkImportController::class, 'acknowledgeAll'])->name('issues.acknowledge-all');
        Route::get('/{import}/export-errors', [BulkImportController::class, 'exportErrors'])->name('export-errors');
        Route::post('/issues/{issue}/acknowledge', [BulkImportController::class, 'acknowledge'])->name('issues.acknowledge');
    });

    // Setup: Academic
    Route::get('/academic', [AcademicController::class, 'index'])
        ->name('academic.index');

    // Academic: Sections
    Route::get('/sections', [SectionController::class, 'index'])
        ->name('sections.index');
    Route::post('/sections', [SectionController::class, 'store'])
        ->name('sections.store');
    Route::put('/sections/{id}', [SectionController::class, 'update'])
        ->name('sections.update');
    Route::delete('/sections/{id}', [SectionController::class, 'destroy'])
        ->name('sections.destroy');
    Route::delete('/sections/{id}/force-delete', [SectionController::class, 'forceDelete'])
        ->name('sections.force-delete');
    Route::patch('/sections/{section}/restore', [SectionController::class, 'restore'])
        ->name('sections.restore');

    // Academic: Subjects
    Route::get('/subjects', [SubjectController::class, 'index'])
        ->name('subjects.index');
    Route::get('/subjects/create', [SubjectController::class, 'create'])
        ->name('subjects.create');
    Route::post('/subjects', [SubjectController::class, 'store'])
        ->name('subjects.store');
    Route::get('/subjects/{subject}', [SubjectController::class, 'show'])
        ->name('subjects.show');
    Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])
        ->name('subjects.edit');
    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])
        ->name('subjects.update');
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])
        ->name('subjects.destroy');

    // Academic: School Years
    Route::post('/school-years', [SchoolYearController::class, 'store'])
        ->name('school-years.store');
    Route::put('/school-years/{id}', [SchoolYearController::class, 'update'])
        ->name('school-years.update');
    Route::delete('/school-years/{id}', [SchoolYearController::class, 'destroy'])
        ->name('school-years.destroy');
    Route::patch('/school-years/{id}', [SchoolYearController::class, 'restore'])
        ->name('school-years.restore');

    // Setup: Teaching Assignments
    Route::get('/teaching-assignments', [TeachingAssignmentController::class, 'index'])
        ->name('teaching-assignments.index');
    Route::get('/teaching-assignments/create', [TeachingAssignmentController::class, 'create'])
        ->name('teaching-assignments.create');
    Route::post('/teaching-assignments', [TeachingAssignmentController::class, 'store'])
        ->name('teaching-assignments.store');
    Route::get('/teaching-assignments/{teachingAssignment}', [TeachingAssignmentController::class, 'show'])
        ->name('teaching-assignments.show');
    Route::get('/teaching-assignments/{teachingAssignment}/edit', [TeachingAssignmentController::class, 'edit'])
        ->name('teaching-assignments.edit');
    Route::put('/teaching-assignments/{teachingAssignment}', [TeachingAssignmentController::class, 'update'])
        ->name('teaching-assignments.update');
    Route::delete('/teaching-assignments/{teachingAssignment}', [TeachingAssignmentController::class, 'destroy'])
        ->name('teaching-assignments.destroy');
    Route::get('/teachers/{teacher}/teaching-assignments', [TeachingAssignmentController::class, 'byTeacher'])
        ->name('teachers.teaching-assignments');
    Route::get('/sections/{section}/teaching-assignments', [TeachingAssignmentController::class, 'bySection'])
        ->name('sections.teaching-assignments');

    // Setup: Schedule Configuration
    Route::get('/schedule-configuration', [ScheduleConfigController::class, 'index'])
        ->name('schedule-configuration.index');
    Route::post('/schedule-configuration', [ScheduleConfigController::class, 'store'])
        ->name('schedconfig.store');
    Route::put('/schedule-configuration/{id}', [ScheduleConfigController::class, 'update'])
        ->name('schedconfig.update');
    Route::delete('/schedule-configuration/{id}', [ScheduleConfigController::class, 'destroy'])
        ->name('schedconfig.destroy');

    // Setup: QR Generation
    Route::get('/qr-generation', [QrCodeController::class, 'index'])
        ->name('qr.index');
    Route::get('/students/{id}/qr', [QrCodeController::class, 'show'])
        ->name('students.qr.show');
    Route::get('/students/{id}/qr/download', [QrCodeController::class, 'download'])
        ->name('students.qr.download');
    Route::get('/sections/{section}/qr/download', [QrCodeController::class, 'downloadSection'])
        ->name('sections.qr.download');

    // System: Settings
    Route::get('/settings', [SettingsController::class, 'index'])
        ->name('settings.index');
});
