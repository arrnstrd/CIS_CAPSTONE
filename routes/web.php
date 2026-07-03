<?php

use App\Http\Controllers\Enrollment\EnrollmentController;
use App\Http\Controllers\Enrollment\SectionController;
use App\Http\Controllers\Management\StudentController;
use App\Http\Controllers\Management\TeacherController;
use App\Http\Controllers\Monitoring\AttendanceLogController;
use App\Http\Controllers\Monitoring\EmailLogController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\Scanner\ScanController;
use App\Http\Controllers\ScheduleConfigController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StudentProfileController;
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
    

// ============================================================
// UTILITIES
// ============================================================

// schedule configuration
Route::get('/schedule-configuration', [ScheduleConfigController::class, 'index']);
Route::post('/schedule-configuration', [ScheduleConfigController::class, 'store'])->name('schedconfig.store');
Route::put('/schedule-configuration/{id}', [ScheduleConfigController::class, 'update'])->name('schedconfig.update');
Route::delete('/schedule-configuration/{id}', [ScheduleConfigController::class, 'destroy'])->name('schedconfig.destroy');

// qr generation
Route::get('/students/{id}/qr-pdf', [QrCodeController::class, 'generate']);

Route::post('/scan', [ScanController::class, 'scan'])->name('scan');


// ============================================================
// SETTINGS
// ============================================================

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