<?php

use App\Http\Controllers\AdministrationFeature\Authentication\AuthController;
use App\Http\Controllers\AcademicFeature\AcademicController;
use App\Http\Controllers\AcademicFeature\EnrollmentController;
use App\Http\Controllers\AcademicFeature\SectionController;
use App\Http\Controllers\AcademicFeature\SubjectController;
use App\Http\Controllers\GradingSystemFeature\AssessmentController;
use App\Http\Controllers\GradingSystemFeature\AssessmentCategoryController;
use App\Http\Controllers\GradingSystemFeature\GradingPeriodController;
use App\Http\Controllers\QrSystemFeature\GateScanSchedule\ScheduleConfigController;
use App\Http\Controllers\QrSystemFeature\Logs\AttendanceLogController;
use App\Http\Controllers\QrSystemFeature\Logs\EmailLogController;
use App\Http\Controllers\QrSystemFeature\Logs\RoomAttendanceController;
use App\Http\Controllers\QrSystemFeature\QrCode\QrCodeController;
use App\Http\Controllers\QrSystemFeature\Scanner\ScanController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\StudentProfileController;
use App\Http\Controllers\Teacher\StudentManagementController;
use App\Http\Controllers\Teacher\TeacherController;
use App\Http\Controllers\Teacher\TeachingAssignmentController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;


// ============================================================
// AUTHENTICATION
// ============================================================

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->name('login.attempt')
    ->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// auth / login
Route::get('/role-selection', fn() => view('login.role_selection'));
Route::get('/user-login', fn() => view('login.admin-login'));

// layout preview
Route::get('/layout', fn() => view('components.layouts.admin'));
Route::get('/layout/teacher', function () {
    return view('components.layouts.teacher');
});


// ============================================================
// PROTECTED APPLICATION ROUTES
// ============================================================

Route::middleware(['auth'])->group(function () {

    // ------------------------------------------------------------
    // SHARED / ACCESSIBLE TEACHER DASHBOARD FOR TESTING
    // (Bypasses strict role check so Admin can view teacher logs)
    // ------------------------------------------------------------
    Route::get('/teacher/dashboard', [RoomAttendanceController::class, 'index'])->name('teacher.dashboard');
    Route::get('/teacher/room-attendance', [RoomAttendanceController::class, 'index'])->name('room-attendance.index');

    // ============================================================
    // ADMIN FEATURE
    // ============================================================

    Route::middleware(['role:admin'])->group(function () {
        // academic
        Route::get('/academic', [AcademicController::class, 'index'])->name('academic.index');

        // enrollment
        Route::get('/enrollment', [EnrollmentController::class, 'index'])->name('enrollment.index');
        Route::post('/enrollment', [EnrollmentController::class, 'store'])->name('enrollment.store');
        Route::put('/enrollment/{id}', [EnrollmentController::class, 'update'])->name('enrollment.update');
        Route::delete('/enrollment/{id}', [EnrollmentController::class, 'destroy'])->name('enrollment.destroy');

        // section
        Route::get('/sections', [SectionController::class, 'index'])->name('sections.index');
        Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
        Route::put('/sections/{id}', [SectionController::class, 'update'])->name('sections.update');
        Route::delete('/sections/{id}', [SectionController::class, 'destroy'])->name('sections.destroy');
        Route::patch('/sections/{section}/restore', [SectionController::class, 'restore'])->name('sections.restore');

        // subjects
        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('/subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
        Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
        Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
        Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

        // teaching assignments
        Route::get('/teaching-assignments', [TeachingAssignmentController::class, 'index'])->name('teaching-assignments.index');
        Route::get('/teaching-assignments/create', [TeachingAssignmentController::class, 'create'])->name('teaching-assignments.create');
        Route::post('/teaching-assignments', [TeachingAssignmentController::class, 'store'])->name('teaching-assignments.store');
        Route::get('/teaching-assignments/{teachingAssignment}', [TeachingAssignmentController::class, 'show'])->name('teaching-assignments.show');
        Route::get('/teaching-assignments/{teachingAssignment}/edit', [TeachingAssignmentController::class, 'edit'])->name('teaching-assignments.edit');
        Route::put('/teaching-assignments/{teachingAssignment}', [TeachingAssignmentController::class, 'update'])->name('teaching-assignments.update');
        Route::delete('/teaching-assignments/{teachingAssignment}', [TeachingAssignmentController::class, 'destroy'])->name('teaching-assignments.destroy');
        Route::get('/teachers/{teacher}/teaching-assignments', [TeachingAssignmentController::class, 'byTeacher'])->name('teachers.teaching-assignments');
        Route::get('/sections/{section}/teaching-assignments', [TeachingAssignmentController::class, 'bySection'])->name('sections.teaching-assignments');

        // assessment categories
        Route::get('/assessment-categories', [AssessmentCategoryController::class, 'index'])->name('assessment-categories.index');
        Route::get('/assessment-categories/create', [AssessmentCategoryController::class, 'create'])->name('assessment-categories.create');
        Route::post('/assessment-categories', [AssessmentCategoryController::class, 'store'])->name('assessment-categories.store');
        Route::get('/assessment-categories/{assessmentCategory}', [AssessmentCategoryController::class, 'show'])->name('assessment-categories.show');
        Route::get('/assessment-categories/{assessmentCategory}/edit', [AssessmentCategoryController::class, 'edit'])->name('assessment-categories.edit');
        Route::put('/assessment-categories/{assessmentCategory}', [AssessmentCategoryController::class, 'update'])->name('assessment-categories.update');
        Route::delete('/assessment-categories/{assessmentCategory}', [AssessmentCategoryController::class, 'destroy'])->name('assessment-categories.destroy');

        // grading periods
        Route::get('/grading-periods', [GradingPeriodController::class, 'index'])->name('grading-periods.index');
        Route::get('/grading-periods/create', [GradingPeriodController::class, 'create'])->name('grading-periods.create');
        Route::post('/grading-periods', [GradingPeriodController::class, 'store'])->name('grading-periods.store');
        Route::get('/grading-periods/{gradingPeriod}', [GradingPeriodController::class, 'show'])->name('grading-periods.show');
        Route::get('/grading-periods/{gradingPeriod}/edit', [GradingPeriodController::class, 'edit'])->name('grading-periods.edit');
        Route::put('/grading-periods/{gradingPeriod}', [GradingPeriodController::class, 'update'])->name('grading-periods.update');
        Route::delete('/grading-periods/{gradingPeriod}', [GradingPeriodController::class, 'destroy'])->name('grading-periods.destroy');

        // assessments
        Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');
        Route::get('/assessments/create', [AssessmentController::class, 'create'])->name('assessments.create');
        Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store');
        Route::get('/assessments/{assessment}', [AssessmentController::class, 'show'])->name('assessments.show');
        Route::get('/assessments/{assessment}/edit', [AssessmentController::class, 'edit'])->name('assessments.edit');
        Route::put('/assessments/{assessment}', [AssessmentController::class, 'update'])->name('assessments.update');
        Route::delete('/assessments/{assessment}', [AssessmentController::class, 'destroy'])->name('assessments.destroy');
        Route::get('/teaching-assignments/{teachingAssignment}/assessments', [AssessmentController::class, 'byTeachingAssignment'])->name('teaching-assignments.assessments');
        Route::get('/teaching-assignments/{teachingAssignment}/grading-periods/{gradingPeriod}/assessments', [AssessmentController::class, 'byTeachingAssignmentAndGradingPeriod'])->name('teaching-assignments.grading-periods.assessments');

        // monitoring logs
        Route::get('/entry-exit', [AttendanceLogController::class, 'index'])->name('attendance.index');
        Route::get('/attendance', fn() => view('admin-modules.monitoring.class-attendance'))->name('attendance');
        Route::get('/emails', [EmailLogController::class, 'index'])->name('emails.index');
        Route::post('/emails/{id}/retry', [EmailLogController::class, 'retry'])->name('retry.email');
        Route::post('/emails', [EmailLogController::class, 'retryAll'])->name('retryAll.email');

        // qr code generation
        Route::get('/students/{id}/qr', [QrCodeController::class, 'show'])->name('students.qr.show');
        Route::get('/students/{id}/qr/download', [QrCodeController::class, 'download'])->name('students.qr.download');
        Route::get('/qr-generation', [QrCodeController::class, 'index'])->name('qr.index');
        Route::get('/sections/{section}/qr/download', [QrCodeController::class, 'downloadSection'])->name('sections.qr.download');

        // teacher feature
        Route::prefix('teachers')->group(function () {
            Route::get('/', [TeacherController::class, 'index'])->name('teachers.index');
            Route::post('/', [TeacherController::class, 'store'])->name('teachers.store');
            Route::put('/{id}', [TeacherController::class, 'update'])->name('teachers.update');
            Route::delete('/{id}', [TeacherController::class, 'destroy'])->name('teachers.destroy');
            Route::patch('/{id}/restore', [TeacherController::class, 'restore'])->name('teachers.restore');
        });

        // student feature
        Route::get('/student-management', [StudentController::class, 'index'])->name('addStudent');
        Route::post('/students', [StudentController::class, 'store'])->name('student.store');
        Route::get('/students/search', [StudentController::class, 'search'])->name('students.search');
        Route::put('/students/{id}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::get('/student-management/search', [SearchController::class, 'searchStudent'])->name('search.students');
        Route::get('/student-profile', fn() => view('admin-modules.management.student-profile'));
        Route::get('/student-profile/{student}', [StudentProfileController::class, 'show'])->name('student.profile');

        // dashboard and settings
        Route::get('/dashboard', function () {
            return view('admin-modules.monitoring.entry-exit', [
                'attendance_logs' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1),
                'scan_type' => null,
                'session_type' => null,
                'flag_type' => null,
                'statusCounts' => [
                    'TOTAL' => 0,
                    'IN' => 0,
                    'OUT' => 0,
                    'RE_ENTRY' => 0,
                    'RE_EXIT' => 0,
                    'FLAGGED' => 0,
                ],
                'dateFilter' => 'today',
                'customStartDate' => null,
                'customEndDate' => null,
                'query' => '',
            ]);
        })->name('admin.dashboard');
        Route::get('/settings', fn() => view('admin-modules.utilities.settings'));
        Route::get('/users', fn() => view('admin-modules.management.users'));
        Route::get('/grades', fn() => view('admin-modules.management.grade.grades'));

        // schedule configuration
        Route::get('/schedule-configuration', [ScheduleConfigController::class, 'index']);
        Route::post('/schedule-configuration', [ScheduleConfigController::class, 'store'])->name('schedconfig.store');
        Route::put('/schedule-configuration/{id}', [ScheduleConfigController::class, 'update'])->name('schedconfig.update');
        Route::delete('/schedule-configuration/{id}', [ScheduleConfigController::class, 'destroy'])->name('schedconfig.destroy');

        // bulk import
        Route::get('/bulk-import', fn() => view('admin-modules.management.bulk-import'))->name('bulk-import');

        Route::prefix('import')->name('import.')->group(function () {
            Route::post('/upload', [\App\Http\Controllers\Import\BulkImportController::class, 'upload'])->name('upload');
            Route::get('/active', [\App\Http\Controllers\Import\BulkImportController::class, 'active'])->name('active');
            Route::get('/history/list', [\App\Http\Controllers\Import\BulkImportController::class, 'history'])->name('history');
            Route::get('/template/download', [\App\Http\Controllers\Import\BulkImportController::class, 'downloadTemplate'])->name('template');

            Route::get('/{import}', [\App\Http\Controllers\Import\BulkImportController::class, 'show'])->name('show');
            Route::get('/{import}/status', [\App\Http\Controllers\Import\BulkImportController::class, 'status'])->name('status');
            Route::post('/{import}/validate', [\App\Http\Controllers\Import\BulkImportController::class, 'validate'])->name('validate');
            Route::post('/{import}/replace-file', [\App\Http\Controllers\Import\BulkImportController::class, 'replaceFile'])->name('replace-file');
            Route::post('/{import}/confirm', [\App\Http\Controllers\Import\BulkImportController::class, 'confirm'])->name('confirm');
            Route::post('/{import}/cancel', [\App\Http\Controllers\Import\BulkImportController::class, 'cancel'])->name('cancel');
            Route::get('/{import}/issues', [\App\Http\Controllers\Import\BulkImportController::class, 'issues'])->name('issues');
            Route::post('/{import}/issues/acknowledge-all', [\App\Http\Controllers\Import\BulkImportController::class, 'acknowledgeAll'])->name('issues.acknowledge-all');
            Route::get('/{import}/export-errors', [\App\Http\Controllers\Import\BulkImportController::class, 'exportErrors'])->name('export-errors');

            Route::post('/issues/{issue}/acknowledge', [\App\Http\Controllers\Import\BulkImportController::class, 'acknowledge'])->name('issues.acknowledge');
        });
    });

    // ============================================================
    // TEACHER FEATURE
    // ============================================================

    Route::middleware(['role:teacher'])->group(function () {
        Route::get('/teacher/room-attendance/create', [RoomAttendanceController::class, 'create'])->name('room-attendance.create');
        Route::post('/teacher/room-attendance', [RoomAttendanceController::class, 'store'])->name('room-attendance.store');
        Route::get('/teacher/room-attendance/{roomAttendance}', [RoomAttendanceController::class, 'show'])->name('room-attendance.show');
        Route::get('/teacher/room-attendance/{roomAttendance}/edit', [RoomAttendanceController::class, 'edit'])->name('room-attendance.edit');
        Route::put('/teacher/room-attendance/{roomAttendance}', [RoomAttendanceController::class, 'update'])->name('room-attendance.update');
        Route::delete('/teacher/room-attendance/{roomAttendance}', [RoomAttendanceController::class, 'destroy'])->name('room-attendance.destroy');

        Route::get('/teacher/teaching-assignments/{teachingAssignment}/room-attendance', [RoomAttendanceController::class, 'byTeachingAssignmentAndDate'])->name('teaching-assignments.room-attendance');
        Route::get('/teacher/teaching-assignments/{teachingAssignment}/room-attendance/bulk-create', [RoomAttendanceController::class, 'bulkCreateForm'])->name('teaching-assignments.room-attendance.bulk-create');
        Route::post('/teacher/teaching-assignments/{teachingAssignment}/room-attendance/bulk', [RoomAttendanceController::class, 'bulkStore'])->name('teaching-assignments.room-attendance.bulk-store');

        Route::get('/teacher/student-management', [StudentManagementController::class, 'index'])->name('teacher.student-management');
        Route::get('/teacher/student-profile/{student}', [StudentProfileController::class, 'teacherShow'])->name('teacher.student-profile');
        Route::get('/teacher/grading-system', fn() => view('teacher-modules.grading-system'))->name('teacher.grading-system');
    });

    // ============================================================
    // SCANNER FEATURE
    // ============================================================

    Route::middleware(['role:scanner_operator'])->group(function () {
        Route::post('/scan', [ScanController::class, 'scan'])->name('scan');
        Route::get('/scanner', fn() => view('scanner.index'))->name('scanner.dashboard');
    });
});
