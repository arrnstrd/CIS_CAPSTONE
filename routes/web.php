<?php

// use App\Events\TableUpdated;
use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ScheduleConfigController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentProfileController;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/' , fn() => view('admin-modules.monitoring.entry-exit'))


// misc
Route::get('/layout', fn() => view('components.layouts.admin'));
Route::get('/dashboard', fn() => view('admin-modules.dashboard'));
Route::get('/role-selection', fn() => view('login.role_selection'));

// monitoring
Route::get('/entry_exit', fn() => view('admin-modules.monitoring.entry-exit'))->name('entryExit');
Route::get('/attendance', fn() => view('admin-modules.monitoring.class-attendance'))->name('attendance');
Route::get('/entry-exit', [AttendanceLogController::class, 'index'])->name('attendance.index');

//emails
Route::get('/emails', [EmailLogController::class, 'index'])->name('emails.index');
Route::post('/emails/{id}/retry' , [EmailLogController::class, 'retry'])->name('retry.email');
Route::post('/emails' , [EmailLogController::class , 'retryAll'])->name('retryAll.email');

// students
Route::get('/student-management', [StudentController::class, 'index'])->name('addStudent');
Route::post('/students', [StudentController::class, 'store'])->name('student.store');
Route::get('/students/search', [StudentController::class, 'search'])->name('students.search');
Route::put('/students/{id}' , [StudentController::class, 'update'])->name('students.update');


Route::get('/students/{id}/qr-pdf', [QrCodeController::class, 'generate']);
Route::get('/student-management/search', [SearchController::class, 'searchStudent'])->name('search.students');

// enrollment
Route::get('/enrollment', [EnrollmentController::class, 'index'])->name('enrollment.index');
Route::post('/enrollment', [EnrollmentController::class, 'store'])->name('enrollment.store');
Route::put('/enrollment/{id}' ,[EnrollmentController::class, 'update'])->name('enrollment.update');


// schedule configuration
Route::get('/schedule-configuration', [ScheduleConfigController::class, 'index']);
Route::post('/schedule-configuration', [ScheduleConfigController::class, 'store'])->name('schedconfig.store');
Route::put('/schedule-configuration', [ScheduleConfigController::class, 'store'])->name('schedconfig.update');

// scanner
Route::post('/scan', [ScanController::class, 'scan'])->name('scan');

// management
Route::get('/users', fn() => view('admin-modules.management.users'));

// testing
Route::get('/scanner', fn() => view('scanner.index'));






Route::get('/student-profile' , function(){
    return view('admin-modules.management.student-profile');
});


Route::get('/student-profile/{student}' , [StudentProfileController::class, 'show'])->name('student.profile');


// Route::post('/items/store', function (Request $request) {
//     // Save to DB
//     Item::create($request->all());

//     // Broadcast update
//     $allItems = Item::all();
//     event(new TableUpdated($allItems));

//     return response()->json(['success' => true]);
// });
