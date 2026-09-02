<?php

use App\Http\Controllers\AdministrationFeature\Authentication\AuthController;
use App\Http\Controllers\AcademicFeature\AcademicController;
use App\Http\Controllers\AcademicFeature\SchoolYearController;
use App\Http\Controllers\AcademicFeature\SectionController;
use App\Http\Controllers\AcademicFeature\SubjectController;
use App\Http\Controllers\GradingSystemFeature\AssessmentController;
use App\Http\Controllers\GradingSystemFeature\AssessmentCategoryController;
use App\Http\Controllers\GradingSystemFeature\GradingPeriodController;
use App\Http\Controllers\AdministrationFeature\Dashboard\DashboardController;
use App\Http\Controllers\QrSystemFeature\GateScanSchedule\ScheduleConfigController;
use App\Http\Controllers\QrSystemFeature\Logs\AttendanceLogController;
use App\Http\Controllers\QrSystemFeature\Logs\EmailLogController;
use App\Http\Controllers\QrSystemFeature\Logs\QrStationController;
use App\Http\Controllers\QrSystemFeature\QrCode\QrCodeController;
use App\Http\Controllers\QrSystemFeature\Scanner\ScanController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\StudentProfileController;
use App\Http\Controllers\Teacher\StudentManagementController;
use App\Http\Controllers\Teacher\TeacherController;
use App\Http\Controllers\Teacher\TeachingAssignmentController;
use App\Http\Controllers\Teacher\TeacherRoomAttendanceController;
use App\Http\Controllers\AdministrationFeature\Setup\SetupController;
use App\Models\User;
use Illuminate\Support\Facades\Route;


// ============================================================
// SPEED TEST
// ============================================================

Route::get('/speed-test', function () {
    return 'OK';
});


// ============================================================
// AUTHENTICATION
// ============================================================

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.attempt')
    ->middleware('throttle:5,1');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::get('/role-selection', fn() => view('login.role_selection'));


// ============================================================
// ACCOUNT SETUP
// ============================================================

Route::get('/setup/{token}', [SetupController::class, 'show'])
    ->name('setup.show');

Route::post('/setup/{token}', [SetupController::class, 'submit'])
    ->name('setup.submit');

Route::post('/setup/complete', [SetupController::class, 'complete'])
    ->name('setup.complete');

Route::get('/user-login', fn() => view('login.admin-login'));


// ============================================================
// LAYOUT PREVIEW
// ============================================================

Route::get('/layout', fn() => view('components.layouts.admin', [
    'pageName' => 'Admin Layout Preview',
    'subtitle' => 'Head banner preview with live date + help button.',
    'slot' => '<div class="p-4"><h4 class="fw-bold">Slot Content</h4><p class="text-muted">This area renders the page content beneath the banner.</p></div>',
]));

Route::get('/layout/teacher', fn() => view('components.layouts.teacher', [
    'pageName' => 'Teacher Layout Preview',
    'subtitle' => 'Teacher head area preview.',
    'slot' => '<div class="p-4"><h4 class="fw-bold">Slot Content</h4><p class="text-muted">Teacher page content renders here.</p></div>',
]));


// ============================================================
// PROTECTED APPLICATION ROUTES
// ============================================================

Route::middleware(['auth'])->group(function () {


    // ========================================================
    // ADMIN FEATURE
    // ========================================================

    Route::middleware(['role:admin'])->group(function () {

        // Academic
        Route::get('/academic', [AcademicController::class, 'index'])
            ->name('academic.index');


        // Sections
        Route::get('/sections', [SectionController::class, 'index'])
            ->name('sections.index');

        Route::post('/sections', [SectionController::class, 'store'])
            ->name('sections.store');

        Route::put('/sections/{id}', [SectionController::class, 'update'])
            ->name('sections.update');

        Route::delete('/sections/{id}', [SectionController::class, 'destroy'])
            ->name('sections.destroy');

        Route::patch('/sections/{section}/restore', [SectionController::class, 'restore'])
            ->name('sections.restore');


        // Subjects
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


        // Teaching Assignments
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


        // Assessment Categories
        Route::get('/assessment-categories', [AssessmentCategoryController::class, 'index'])
            ->name('assessment-categories.index');

        Route::get('/assessment-categories/create', [AssessmentCategoryController::class, 'create'])
            ->name('assessment-categories.create');

        Route::post('/assessment-categories', [AssessmentCategoryController::class, 'store'])
            ->name('assessment-categories.store');

        Route::get('/assessment-categories/{assessmentCategory}', [AssessmentCategoryController::class, 'show'])
            ->name('assessment-categories.show');

        Route::get('/assessment-categories/{assessmentCategory}/edit', [AssessmentCategoryController::class, 'edit'])
            ->name('assessment-categories.edit');

        Route::put('/assessment-categories/{assessmentCategory}', [AssessmentCategoryController::class, 'update'])
            ->name('assessment-categories.update');

        Route::delete('/assessment-categories/{assessmentCategory}', [AssessmentCategoryController::class, 'destroy'])
            ->name('assessment-categories.destroy');


        // Grading Periods
        Route::get('/grading-periods', [GradingPeriodController::class, 'index'])
            ->name('grading-periods.index');

        Route::get('/grading-periods/create', [GradingPeriodController::class, 'create'])
            ->name('grading-periods.create');

        Route::post('/grading-periods', [GradingPeriodController::class, 'store'])
            ->name('grading-periods.store');

        Route::get('/grading-periods/{gradingPeriod}', [GradingPeriodController::class, 'show'])
            ->name('grading-periods.show');

        Route::get('/grading-periods/{gradingPeriod}/edit', [GradingPeriodController::class, 'edit'])
            ->name('grading-periods.edit');

        Route::put('/grading-periods/{gradingPeriod}', [GradingPeriodController::class, 'update'])
            ->name('grading-periods.update');

        Route::delete('/grading-periods/{gradingPeriod}', [GradingPeriodController::class, 'destroy'])
            ->name('grading-periods.destroy');


        // Assessments
        Route::get('/assessments', [AssessmentController::class, 'index'])
            ->name('assessments.index');

        Route::get('/assessments/create', [AssessmentController::class, 'create'])
            ->name('assessments.create');

        Route::post('/assessments', [AssessmentController::class, 'store'])
            ->name('assessments.store');

        Route::get('/assessments/{assessment}', [AssessmentController::class, 'show'])
            ->name('assessments.show');

        Route::get('/assessments/{assessment}/edit', [AssessmentController::class, 'edit'])
            ->name('assessments.edit');

        Route::put('/assessments/{assessment}', [AssessmentController::class, 'update'])
            ->name('assessments.update');

        Route::delete('/assessments/{assessment}', [AssessmentController::class, 'destroy'])
            ->name('assessments.destroy');

        Route::get(
            '/teaching-assignments/{teachingAssignment}/assessments',
            [AssessmentController::class, 'byTeachingAssignment']
        )->name('teaching-assignments.assessments');

        Route::get(
            '/teaching-assignments/{teachingAssignment}/grading-periods/{gradingPeriod}/assessments',
            [AssessmentController::class, 'byTeachingAssignmentAndGradingPeriod']
        )->name('teaching-assignments.grading-periods.assessments');
    });


    // ========================================================
    // DASHBOARD
    // ========================================================

    Route::middleware(['role:super_admin,admin,scanner_operator'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('admin.dashboard');
    });


    // ========================================================
    // SUPER ADMIN
    // ========================================================

    Route::middleware(['role:super_admin'])->group(function () {

        // User Management
        Route::get('/users', function (\Illuminate\Http\Request $request) {

            $query = User::query();

            if ($request->filled('search')) {

                $search = strtolower(trim($request->search));

                $query->where(function ($q) use ($search) {

                    $q->whereRaw(
                        'LOWER(first_name) LIKE ?',
                        ["%{$search}%"]
                    )
                    ->orWhereRaw(
                        'LOWER(last_name) LIKE ?',
                        ["%{$search}%"]
                    )
                    ->orWhereRaw(
                        'LOWER(email) LIKE ?',
                        ["%{$search}%"]
                    )
                    ->orWhereRaw(
                        'LOWER(COALESCE(employee_id, \'\')) LIKE ?',
                        ["%{$search}%"]
                    )
                    ->orWhereRaw(
                        "LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?",
                        ["%{$search}%"]
                    );
                });
            }

            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $users = $query
                ->latest('created_at')
                ->paginate(20)
                ->appends($request->query());

            return view(
                'admin-modules.management.users',
                compact('users')
            );

        })->name('users.index');


        // Security Audit Log
        Route::get('/security-audit-log', function (\Illuminate\Http\Request $request) {

            $activityLogs = \App\Models\LoginLog::with('user')
                ->latest('attempted_at')
                ->paginate(20)
                ->appends($request->query());

            return view(
                'admin-modules.management.security-audit-log',
                compact('activityLogs')
            );

        })->name('security-audit-log.index');


        // Recent Activity
        Route::get('/recent-activity', function (\Illuminate\Http\Request $request) {

            $recentActivities = \App\Models\AdminActivityLog::with('actor')
                ->latest('created_at')
                ->paginate(20)
                ->appends($request->query());

            return view(
                'admin-modules.management.recent-activity',
                compact('recentActivities')
            );

        })->name('recent-activity.index');
    });


    // ========================================================
    // MONITORING LOGS & STATION
    // ========================================================

    Route::middleware(['role:admin,scanner_operator'])->group(function () {

        Route::get('/qr-station', [QrStationController::class, 'index'])
            ->name('qr-station.index');

        Route::post('/qr-station/scan', [ScanController::class, 'scan'])
            ->name('qr-station.scan');

        Route::get('/entry-exit', [AttendanceLogController::class, 'index'])
            ->name('time-in-time-out.index');

        Route::get('/time-in-time-out-history', [AttendanceLogController::class, 'index'])
            ->name('time-in-time-out-history.index');

        Route::get(
            '/time-in-time-out-history/download',
            [AttendanceLogController::class, 'download']
        )->name('time-in-time-out-history.download');
    });


    // ========================================================
    // SCHOOL ADMIN
    // ========================================================

    Route::middleware(['role:admin'])->group(function () {

        // Academic
        Route::get('/academic', [AcademicController::class, 'index'])
            ->name('academic.index');


        // Sections
        Route::get('/sections', [SectionController::class, 'index'])
            ->name('sections.index');

        Route::post('/sections', [SectionController::class, 'store'])
            ->name('sections.store');

        Route::put('/sections/{id}', [SectionController::class, 'update'])
            ->name('sections.update');

        Route::delete('/sections/{id}', [SectionController::class, 'destroy'])
            ->name('sections.destroy');

        Route::patch('/sections/{section}/restore', [SectionController::class, 'restore'])
            ->name('sections.restore');


        // Subjects
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


        // Teaching Assignments
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


        // Assessment Categories
        Route::get('/assessment-categories', [AssessmentCategoryController::class, 'index'])
            ->name('assessment-categories.index');

        Route::get('/assessment-categories/create', [AssessmentCategoryController::class, 'create'])
            ->name('assessment-categories.create');

        Route::post('/assessment-categories', [AssessmentCategoryController::class, 'store'])
            ->name('assessment-categories.store');

        Route::get('/assessment-categories/{assessmentCategory}', [AssessmentCategoryController::class, 'show'])
            ->name('assessment-categories.show');

        Route::get('/assessment-categories/{assessmentCategory}/edit', [AssessmentCategoryController::class, 'edit'])
            ->name('assessment-categories.edit');

        Route::put('/assessment-categories/{assessmentCategory}', [AssessmentCategoryController::class, 'update'])
            ->name('assessment-categories.update');

        Route::delete('/assessment-categories/{assessmentCategory}', [AssessmentCategoryController::class, 'destroy'])
            ->name('assessment-categories.destroy');


        // Grading Periods
        Route::get('/grading-periods', [GradingPeriodController::class, 'index'])
            ->name('grading-periods.index');

        Route::get('/grading-periods/create', [GradingPeriodController::class, 'create'])
            ->name('grading-periods.create');

        Route::post('/grading-periods', [GradingPeriodController::class, 'store'])
            ->name('grading-periods.store');

        Route::get('/grading-periods/{gradingPeriod}', [GradingPeriodController::class, 'show'])
            ->name('grading-periods.show');

        Route::get('/grading-periods/{gradingPeriod}/edit', [GradingPeriodController::class, 'edit'])
            ->name('grading-periods.edit');

        Route::put('/grading-periods/{gradingPeriod}', [GradingPeriodController::class, 'update'])
            ->name('grading-periods.update');

        Route::delete('/grading-periods/{gradingPeriod}', [GradingPeriodController::class, 'destroy'])
            ->name('grading-periods.destroy');


        // Assessments
        Route::get('/assessments', [AssessmentController::class, 'index'])
            ->name('assessments.index');

        Route::get('/assessments/create', [AssessmentController::class, 'create'])
            ->name('assessments.create');

        Route::post('/assessments', [AssessmentController::class, 'store'])
            ->name('assessments.store');

        Route::get('/assessments/{assessment}', [AssessmentController::class, 'show'])
            ->name('assessments.show');

        Route::get('/assessments/{assessment}/edit', [AssessmentController::class, 'edit'])
            ->name('assessments.edit');

        Route::put('/assessments/{assessment}', [AssessmentController::class, 'update'])
            ->name('assessments.update');

        Route::delete('/assessments/{assessment}', [AssessmentController::class, 'destroy'])
            ->name('assessments.destroy');

        Route::get(
            '/teaching-assignments/{teachingAssignment}/assessments',
            [AssessmentController::class, 'byTeachingAssignment']
        )->name('teaching-assignments.assessments');

        Route::get(
            '/teaching-assignments/{teachingAssignment}/grading-periods/{gradingPeriod}/assessments',
            [AssessmentController::class, 'byTeachingAssignmentAndGradingPeriod']
        )->name('teaching-assignments.grading-periods.assessments');


        // Attendance
        Route::get('/attendance', fn() => view(
            'admin-modules.monitoring.class-attendance'
        ))->name('attendance');


        // Email Logs
        Route::get('/emails', [EmailLogController::class, 'index'])
            ->name('emails.index');

        Route::post('/emails/{id}/retry', [EmailLogController::class, 'retry'])
            ->name('retry.email');

        Route::post('/emails', [EmailLogController::class, 'retryAll'])
            ->name('retryAll.email');


        // QR Code Generation
        Route::get('/students/{id}/qr', [QrCodeController::class, 'show'])
            ->name('students.qr.show');

        Route::get('/students/{id}/qr/download', [QrCodeController::class, 'download'])
            ->name('students.qr.download');

        Route::get('/qr-generation', [QrCodeController::class, 'index'])
            ->name('qr.index');

        Route::get('/sections/{section}/qr/download', [QrCodeController::class, 'downloadSection'])
            ->name('sections.qr.download');


        // Teacher Management
        Route::prefix('teachers')->group(function () {

            Route::get('/', [TeacherController::class, 'index'])
                ->name('teachers.index');

            Route::post('/', [TeacherController::class, 'store'])
                ->name('teachers.store');

            Route::put('/{id}', [TeacherController::class, 'update'])
                ->name('teachers.update');

            Route::delete('/{id}', [TeacherController::class, 'destroy'])
                ->name('teachers.destroy');

            Route::patch('/{id}/restore', [TeacherController::class, 'restore'])
                ->name('teachers.restore');
        });


        // Student Management
        Route::get('/student-management', [StudentController::class, 'index'])
            ->name('student-management.index');

        Route::get('/student-management/grade/{grade}', [StudentController::class, 'byGrade'])
            ->name('student-management.grade');

        Route::post('/students', [StudentController::class, 'store'])
            ->name('student.store');

        Route::get('/students/search', [StudentController::class, 'search'])
            ->name('students.search');

        Route::put('/students/{id}', [StudentController::class, 'update'])
            ->name('students.update');

        Route::delete('/students/{id}', [StudentController::class, 'destroy'])
            ->name('students.destroy');


        // Admin Student Profile
        Route::get('/student-profile', fn() => view(
            'admin-modules.management.student-profile'
        ));

        Route::get('/student-profile/{student}', [StudentProfileController::class, 'show'])
            ->name('student.profile');

        Route::put('/student-profile/{student}/info', [StudentProfileController::class, 'updateInfo'])
            ->name('student.profile.update-info');

        Route::put('/student-profile/{student}/guardian', [StudentProfileController::class, 'updateGuardian'])
            ->name('student.profile.update-guardian');


        // Settings
        Route::get('/settings', fn() => view(
            'admin-modules.utilities.settings',
            [
                'schoolYears' => \App\Models\SchoolYear::orderByDesc('is_active')
                    ->orderBy('school_year', 'desc')
                    ->get(),
            ]
        ));


        // School Years
        Route::post('/school-years', [SchoolYearController::class, 'store'])
            ->name('school-years.store');

        Route::put('/school-years/{id}', [SchoolYearController::class, 'update'])
            ->name('school-years.update');

        Route::delete('/school-years/{id}', [SchoolYearController::class, 'destroy'])
            ->name('school-years.destroy');

        Route::patch('/school-years/{id}', [SchoolYearController::class, 'restore'])
            ->name('school-years.restore');


        Route::get('/grades', fn() => view(
            'admin-modules.management.grade.grades'
        ));


        // Schedule Configuration
        Route::get('/schedule-configuration', [ScheduleConfigController::class, 'index']);

        Route::post('/schedule-configuration', [ScheduleConfigController::class, 'store'])
            ->name('schedconfig.store');

        Route::put('/schedule-configuration/{id}', [ScheduleConfigController::class, 'update'])
            ->name('schedconfig.update');

        Route::delete('/schedule-configuration/{id}', [ScheduleConfigController::class, 'destroy'])
            ->name('schedconfig.destroy');


        // Bulk Import
        Route::get('/bulk-import', fn() => view(
            'admin-modules.management.bulk-import'
        ))->name('bulk-import');

        Route::prefix('import')->name('import.')->group(function () {

            Route::post('/upload', [\App\Http\Controllers\Import\BulkImportController::class, 'upload'])
                ->name('upload');

            Route::get('/active', [\App\Http\Controllers\Import\BulkImportController::class, 'active'])
                ->name('active');

            Route::get('/history/list', [\App\Http\Controllers\Import\BulkImportController::class, 'history'])
                ->name('history');

            Route::get('/template/download', [\App\Http\Controllers\Import\BulkImportController::class, 'downloadTemplate'])
                ->name('template');

            Route::get('/{import}', [\App\Http\Controllers\Import\BulkImportController::class, 'show'])
                ->name('show');

            Route::get('/{import}/status', [\App\Http\Controllers\Import\BulkImportController::class, 'status'])
                ->name('status');

            Route::post('/{import}/validate', [\App\Http\Controllers\Import\BulkImportController::class, 'validate'])
                ->name('validate');

            Route::post('/{import}/replace-file', [\App\Http\Controllers\Import\BulkImportController::class, 'replaceFile'])
                ->name('replace-file');

            Route::post('/{import}/confirm', [\App\Http\Controllers\Import\BulkImportController::class, 'confirm'])
                ->name('confirm');

            Route::post('/{import}/cancel', [\App\Http\Controllers\Import\BulkImportController::class, 'cancel'])
                ->name('cancel');

            Route::get('/{import}/issues', [\App\Http\Controllers\Import\BulkImportController::class, 'issues'])
                ->name('issues');

            Route::post('/{import}/issues/acknowledge-all', [\App\Http\Controllers\Import\BulkImportController::class, 'acknowledgeAll'])
                ->name('issues.acknowledge-all');

            Route::get('/{import}/export-errors', [\App\Http\Controllers\Import\BulkImportController::class, 'exportErrors'])
                ->name('export-errors');

            Route::post('/issues/{issue}/acknowledge', [\App\Http\Controllers\Import\BulkImportController::class, 'acknowledge'])
                ->name('issues.acknowledge');
        });
    });


    // ========================================================
    // TEACHER FEATURE
    // ========================================================

    Route::middleware(['role:teacher'])->group(function () {

        // ----------------------------------------------------
        // Teacher Student Management
        // ----------------------------------------------------

        Route::get(
            '/teacher/student-management',
            [StudentManagementController::class, 'index']
        )->name('teacher.student-management');


        // ----------------------------------------------------
        // General Teacher Student Profile
        // ----------------------------------------------------

        Route::get(
            '/teacher/student-profile/{student}/summary',
            [StudentProfileController::class, 'teacherSummary']
        )->name('teacher.student-profile.summary');

        Route::get(
            '/teacher/student-profile/{student}',
            [StudentProfileController::class, 'teacherShow']
        )->name('teacher.student-profile');


        // ----------------------------------------------------
        // GRADING SYSTEM
        // ----------------------------------------------------

        Route::get(
            '/teacher/grading-system',
            [App\Http\Controllers\Teacher\GradingSystemOverviewController::class, 'index']
        )->name('teacher.grading-system');

        Route::get(
            '/teacher/grading-system/grades',
            [App\Http\Controllers\Teacher\GradingLevelsController::class, 'index']
        )->name('teacher.grading-system.grades');

        Route::get(
            '/teacher/grading-system/dashboard',
            [App\Http\Controllers\Teacher\GradingDashboardController::class, 'index']
        )->name('teacher.grading-system.dashboard');

        Route::get(
            '/teacher/grading-system/debug-risk-scores',
            [App\Http\Controllers\Teacher\GradingDashboardController::class, 'debugRiskScores']
        )->name('teacher.grading-system.debug-risk-scores');


        // Grade Sheet
        Route::get(
            '/teacher/grading-system/grade-sheet/{teachingAssignmentId}',
            [App\Http\Controllers\Teacher\GradeSheetController::class, 'show']
        )->name('teacher.grading-system.grade-sheet');

        Route::post(
            '/teacher/grading-system/grade-sheet/assessment',
            [App\Http\Controllers\Teacher\GradeSheetController::class, 'storeAssessment']
        )->name('teacher.grading-system.grade-sheet.assessment');

        Route::post(
            '/teacher/grading-system/grade-sheet/score',
            [App\Http\Controllers\Teacher\GradeSheetController::class, 'storeScore']
        )->name('teacher.grading-system.grade-sheet.score');


        // Import Data
        Route::get(
            '/teacher/grading-system/import-data',
            [App\Http\Controllers\Teacher\ImportDataController::class, 'index']
        )->name('teacher.grading-system.import-data');


        // Reports
        Route::get(
            '/teacher/grading-system/reports',
            [App\Http\Controllers\Teacher\ReportsController::class, 'index']
        )->name('teacher.grading-system.reports');

        Route::get(
            '/teacher/grading-system/reports/class-record/{teachingAssignmentId}',
            [App\Http\Controllers\Teacher\ReportsController::class, 'classRecordData']
        )->name('teacher.grading-system.reports.class-record');

        Route::get(
            '/teacher/grading-system/reports/student-academic-record/{teachingAssignmentId}/{enrollmentId}',
            [App\Http\Controllers\Teacher\ReportsController::class, 'studentAcademicRecord']
        )->name('teacher.grading-system.reports.student-academic-record');

        Route::get(
            '/teacher/grading-system/reports/students/{teachingAssignmentId}',
            [App\Http\Controllers\Teacher\ReportsController::class, 'studentsData']
        )->name('teacher.grading-system.reports.students');


        // Grading Levels
        Route::get(
            '/teacher/grading-system/grades/{gradeLevel}',
            [App\Http\Controllers\Teacher\GradingLevelsController::class, 'show']
        )->name('teacher.grading-system.grades.show');

        Route::get(
            '/teacher/grading-system/sections/{sectionId}',
            [App\Http\Controllers\Teacher\GradingLevelsController::class, 'students']
        )->name('teacher.grading-system.sections.show');

        Route::get(
            '/teacher/grading-system/students/{enrollmentId}',
            [App\Http\Controllers\Teacher\GradingLevelsController::class, 'studentDetail']
        )->name('teacher.grading-system.students.show');


        // Analytics
        Route::get(
            '/teacher/grading-system/analytics',
            [App\Http\Controllers\Teacher\AnalyticsController::class, 'index']
        )->name('teacher.grading-system.analytics');


        // At-Risk
        Route::get(
            '/teacher/grading-system/at-risk',
            [App\Http\Controllers\Teacher\AtRiskController::class, 'index']
        )->name('teacher.grading-system.at-risk');

               Route::get(
            '/teacher/grading-system/at-risk/{enrollmentId}',
            [App\Http\Controllers\Teacher\AtRiskController::class, 'show']
        )->name('teacher.grading-system.at-risk.show');

        Route::post(
            '/teacher/grading-system/at-risk/{enrollmentId}/remarks',
            [App\Http\Controllers\Teacher\AtRiskController::class, 'storeRemark']
        )->name('teacher.grading-system.at-risk.remarks.store');
        
        // Attendance Analytics
        Route::get(
            '/teacher/grading-system/attendance',
            [App\Http\Controllers\Teacher\AttendanceAnalyticsController::class, 'index']
        )->name('teacher.grading-system.attendance');


        // By Level
        Route::get(
            '/teacher/grading-system/by-level',
            [App\Http\Controllers\Teacher\ByLevelController::class, 'index']
        )->name('teacher.grading-system.by-level');


        // Sections Overview
        Route::get(
            '/teacher/grading-system/sections',
            [App\Http\Controllers\Teacher\SectionsOverviewController::class, 'index']
        )->name('teacher.grading-system.sections');


        // Subjects Overview
        Route::get(
            '/teacher/grading-system/subjects',
            [App\Http\Controllers\Teacher\SubjectsOverviewController::class, 'index']
        )->name('teacher.grading-system.subjects');


        // ----------------------------------------------------
        // STUDENT PROFILE — GRADING SYSTEM
        // ----------------------------------------------------
        //
        // Normal Student Profile:
        // /teacher/grading-system/student-profile
        //
        // Student Detail:
        // /teacher/grading-system/student-profile/{enrollmentId}
        //
        // At-Risk View Profile now uses its own dedicated route:
        // /teacher/grading-system/at-risk/{enrollmentId}
        // ----------------------------------------------------

        Route::get(
            '/teacher/grading-system/student-profile',
            [App\Http\Controllers\Teacher\StudentProfileSearchController::class, 'index']
        )->name('teacher.grading-system.student-profile');

        Route::get(
            '/teacher/grading-system/student-profile/{enrollmentId}',
            [App\Http\Controllers\Teacher\StudentProfileSearchController::class, 'show']
        )->name('teacher.grading-system.student-profile.show');


        // Comp Rules
        Route::get(
            '/teacher/grading-system/comp-rules',
            [App\Http\Controllers\Teacher\CompRulesController::class, 'index']
        )->name('teacher.grading-system.comp-rules');

        Route::put(
            '/teacher/grading-system/comp-rules',
            [App\Http\Controllers\Teacher\CompRulesController::class, 'update']
        )->name('teacher.grading-system.comp-rules.update');


        // Grading Rules
        Route::get(
            '/teacher/grading-system/grading-rules',
            [App\Http\Controllers\Teacher\GradingRulesController::class, 'index']
        )->name('teacher.grading-system.grading-rules');


        // Assessment Log
        Route::get(
            '/teacher/grading-system/assessments/log',
            [App\Http\Controllers\Teacher\GradeSheetController::class, 'getAssessmentLog']
        )->name('teacher.grading-system.assessments.log');

        Route::put(
            '/teacher/grading-system/grade-sheet/assessment/{assessmentId}',
            [App\Http\Controllers\Teacher\GradeSheetController::class, 'updateAssessment']
        )->name('teacher.grading-system.grade-sheet.assessment.update');

        Route::delete(
            '/teacher/grading-system/grade-sheet/assessment/{assessmentId}',
            [App\Http\Controllers\Teacher\GradeSheetController::class, 'destroyAssessment']
        )->name('teacher.grading-system.grade-sheet.assessment.destroy');

        Route::post(
            '/teacher/grading-system/grade-sheet/assessment/{assessmentId}/scores',
            [App\Http\Controllers\Teacher\GradeSheetController::class, 'storeExtraScores']
        )->name('teacher.grading-system.grade-sheet.assessment.scores');


        // ----------------------------------------------------
        // TEACHER ATTENDANCE
        // ----------------------------------------------------

        Route::get(
            '/teacher/attendance',
            fn() => view('teacher-modules.monitoring.class-attendance')
        )->name('teacher.attendance');

        Route::get(
            '/teacher/time-in-time-out-history',
            [AttendanceLogController::class, 'teacherIndex']
        )->name('teacher.time-in-time-out-history.index');

        Route::get(
            '/teacher/dashboard',
            [TeacherRoomAttendanceController::class, 'index']
        )->name('teacher.dashboard');

        Route::get(
            '/teacher/room-attendance',
            [TeacherRoomAttendanceController::class, 'index']
        )->name('room-attendance.index');

        Route::get(
            '/teacher/room-attendance/{section}',
            [TeacherRoomAttendanceController::class, 'show']
        )->name('room-attendance.show');

        Route::post(
            '/teacher/room-attendance/{section}/{enrollment}/verify',
            [TeacherRoomAttendanceController::class, 'verify']
        )->name('room-attendance.verify');

        Route::get(
            '/teacher/room-attendance/{section}/{enrollment}/history',
            [TeacherRoomAttendanceController::class, 'history']
        )->name('room-attendance.history');
    });


    // ========================================================
    // SCANNER FEATURE
    // ========================================================

    Route::middleware(['role:scanner_operator'])->group(function () {

        Route::post('/scan', [ScanController::class, 'scan'])
            ->name('scan');
    });
});