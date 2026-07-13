<?php

use App\Http\Controllers\Academic\SubjectController;
use App\Http\Controllers\Enrollment\EnrollmentController;
use App\Http\Controllers\Enrollment\SectionController;
use App\Http\Controllers\Grading\AssessmentController;
use App\Http\Controllers\Grading\AssessmentCategoryController;
use App\Http\Controllers\Grading\GradingPeriodController;
use App\Http\Controllers\Management\StudentController;
use App\Http\Controllers\Management\TeacherController;
use App\Http\Controllers\Monitoring\AttendanceLogController;
use App\Http\Controllers\Monitoring\EmailLogController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\QrSystem\RoomAttendanceController;
use App\Http\Controllers\Scanner\ScanController;
use App\Http\Controllers\ScheduleConfigController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\Teacher\TeachingAssignmentController;
use Illuminate\Support\Facades\Route;


// ============================================================
// MONITORING
// ============================================================

// entry-exit
Route::get('/entry_exit', fn() => view('admin-modules.monitoring.entry-exit'))->name('entryExit');
Route::get('/entry-exit', [AttendanceLogController::class, 'index'])->name('attendance.index');

// class attendance
Route::get('/attendance', fn() => view('admin-modules.monitoring.class-attendance'))->name('attendance');

// email
Route::get('/emails', [EmailLogController::class, 'index'])->name('emails.index');
Route::post('/emails/{id}/retry', [EmailLogController::class, 'retry'])->name('retry.email');
Route::post('/emails', [EmailLogController::class, 'retryAll'])->name('retryAll.email');


// ============================================================
// MANAGEMENT
// ============================================================

// teacher
Route::prefix('teachers')->group(function () {

    // Display teacher list
    Route::get('/', [TeacherController::class, 'index'])
        ->name('teachers.index');

    // Create teacher
    Route::post('/', [TeacherController::class, 'store'])
        ->name('teachers.store');

    // Update teacher
    Route::put('/{id}', [TeacherController::class, 'update'])
        ->name('teachers.update');

    // Archive teacher
    Route::delete('/{id}', [TeacherController::class, 'destroy'])
        ->name('teachers.destroy');

    // Restore teacher
    Route::patch('/{id}/restore', [TeacherController::class, 'restore'])
        ->name('teachers.restore');

});

// users
Route::get('/users', fn() => view('admin-modules.management.users'));

// grade
// Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');                // TODO


// ============================================================
// ENROLLMENT MANAGEMENT
// ============================================================

// enrollment
Route::get('/enrollment', [EnrollmentController::class, 'index'])->name('enrollment.index');
Route::post('/enrollment', [EnrollmentController::class, 'store'])->name('enrollment.store');
Route::put('/enrollment/{id}', [EnrollmentController::class, 'update'])->name('enrollment.update');
Route::delete('/enrollment/{id}', [EnrollmentController::class, 'destroy'])->name('enrollment.destroy');

// student
Route::get('/student-management', [StudentController::class, 'index'])->name('addStudent');
Route::post('/students', [StudentController::class, 'store'])->name('student.store');
Route::get('/students/search', [StudentController::class, 'search'])->name('students.search');
Route::put('/students/{id}', [StudentController::class, 'update'])->name('students.update');
Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
Route::get('/student-management/search', [SearchController::class, 'searchStudent'])->name('search.students');
Route::get('/student-profile', fn() => view('admin-modules.management.student-profile'));
Route::get('/student-profile/{student}', [StudentProfileController::class, 'show'])->name('student.profile');

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

// ============================================================
// UTILITIES
// ============================================================

// schedule configuration
Route::get('/schedule-configuration', [ScheduleConfigController::class, 'index']);
Route::post('/schedule-configuration', [ScheduleConfigController::class, 'store'])->name('schedconfig.store');
Route::put('/schedule-configuration/{id}', [ScheduleConfigController::class, 'update'])->name('schedconfig.update');
Route::delete('/schedule-configuration/{id}', [ScheduleConfigController::class, 'destroy'])->name('schedconfig.destroy');

//==================================================
// scanner
//===========================================
Route::post('/scan', [ScanController::class, 'scan'])->name('scan');



// qr generation ==============================================================
// Route::get('/students/{id}/qr-pdf', [QrCodeController::class, 'generate']);
Route::get('/students/{id}/qr', [QrCodeController::class, 'show'])->name('students.qr.show');


//download per student
Route::get( '/students/{id}/qr/download', [QrCodeController::class, 'download'])->name('students.qr.download');

//show for UI
Route::get(
    '/qr-generation',
    [QrCodeController::class, 'index']
)->name('qr.index');

Route::get(
    '/sections/{section}/qr/download',
    [QrCodeController::class, 'downloadSection']
)->name('sections.qr.download');

// ============================================================
// SETTINGS
// ============================================================
Route::get('/settings', function(){
    return view('admin-modules.utilities.settings');
});
// school year
// Route::get('/school-year', [SchoolYearController::class, 'index'])->name('schoolyear.index');  // TODO


// ============================================================
// OTHERS
// ============================================================

// dashboard
Route::get('/dashboard', fn() => view('admin-modules.dashboard'));

// auth / login
Route::get('/role-selection', fn() => view('login.role_selection'));
Route::get('/admin-login', fn() => view('login.admin-login'));

// layout preview
Route::get('/layout', fn() => view('components.layouts.admin'));

// scanner UI (testing)
Route::get('/scanner', fn() => view('scanner.index'));

// ============================================================
// GRADING
// ============================================================

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

// ============================================================
// QR SYSTEM - ROOM ATTENDANCE
// ============================================================

// room attendance
Route::get('/room-attendance', [RoomAttendanceController::class, 'index'])->name('room-attendance.index');
Route::get('/room-attendance/create', [RoomAttendanceController::class, 'create'])->name('room-attendance.create');
Route::post('/room-attendance', [RoomAttendanceController::class, 'store'])->name('room-attendance.store');
Route::get('/room-attendance/{roomAttendance}', [RoomAttendanceController::class, 'show'])->name('room-attendance.show');
Route::get('/room-attendance/{roomAttendance}/edit', [RoomAttendanceController::class, 'edit'])->name('room-attendance.edit');
Route::put('/room-attendance/{roomAttendance}', [RoomAttendanceController::class, 'update'])->name('room-attendance.update');
Route::delete('/room-attendance/{roomAttendance}', [RoomAttendanceController::class, 'destroy'])->name('room-attendance.destroy');
Route::get('/teaching-assignments/{teachingAssignment}/room-attendance', [RoomAttendanceController::class, 'byTeachingAssignmentAndDate'])->name('teaching-assignments.room-attendance');
Route::get('/teaching-assignments/{teachingAssignment}/room-attendance/bulk-create', [RoomAttendanceController::class, 'bulkCreateForm'])->name('teaching-assignments.room-attendance.bulk-create');
Route::post('/teaching-assignments/{teachingAssignment}/room-attendance/bulk', [RoomAttendanceController::class, 'bulkStore'])->name('teaching-assignments.room-attendance.bulk-store');






// Route::post('/items/store', function (Request $request) {
//     // Save to DB
//     Item::create($request->all());

//     // Broadcast update
//     $allItems = Item::all();
//     event(new TableUpdated($allItems));

//     return response()->json(['success' => true]);
// });









//
Route::get('/grades', function(){
    return view('admin-modules.management.grade.grades');
});










//==============================================
//            TEACHER SIDE
//==============================================

