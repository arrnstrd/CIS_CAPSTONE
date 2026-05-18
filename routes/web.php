<?php

use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ScheduleConfigController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Webpage Showcase
Route::get('/layout', function () {
    return view('components.layouts.admin');
});

Route::get('/dashboard', function () {
    return view('admin-modules.dashboard');
});

Route::get('/role-selection', function () {
    return view('login.role_selection');
});


//monitoring----
Route::get('/entry_exit', function () {
    return view('admin-modules.monitoring.entry-exit');
})->name('entryExit');

Route::get('/attendance', function () {
    return view('admin-modules.monitoring.attendance');
})->name('attendance');

Route::get('/emails' , function(){
    return view('admin-modules.monitoring.emails');
});



//management-----
Route::get('/enrollment' , function(){
    return view('admin-modules.management.enrollment');
});



//utilities------

Route::get('/scanner-configuration', function(){
    return view('admin-modules.utilities.scanner-configuration');
});




// CRUD & Core Operations
Route::get('/student-management', function () {
    return view('admin-modules.management.studentList');
})->name('addStudent');

Route::get('/student-management', [StudentController::class, 'index']);

Route::get('/entry-exit', [AttendanceLogController::class, 'index'])->name('attendance.log');

Route::post('/students', [StudentController::class, 'store'])->name('student.store');

Route::get('/students/{id}/qr-pdf', [QrCodeController::class, 'generate']);

Route::post('/scan', [ScanController::class, 'scan'])->name('scan');

//enrollment
Route::get('/enrollment' , [EnrollmentController::class , 'index']);


//scheduleconfig
Route::get('/scanner-configuration' , [ScheduleConfigController::class,'index']);

//search controller
Route::get('/student-management/search' , [SearchController::class, 'searchStudent'])->name('search.students');



// Testing Phase
//web app scanner
Route::get('/scanner', function () {
    return view('scanner.index');
});