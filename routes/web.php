<?php

use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// Webpage Showcase
Route::get('/layout', function () {
    return view('components.layouts.admin');
});

Route::get('/dashboard', function () {
    return view('adminModules.dashboard');
});

Route::get('/entry_exit', function () {
    return view('adminModules.monitoring.entry-exit');
})->name('entryExit');

Route::get('/attendance', function () {
    return view('adminModules.monitoring.attendance');
})->name('attendance');

Route::get('/role-selection', function () {
    return view('login.role_selection');
});


Route::get('/enrollment' , function(){
    return view('adminModules.management.enrollment');
});




// CRUD & Core Operations
Route::get('/student-management', function () {
    return view('adminModules.management.studentList');
})->name('addStudent');

Route::get('/student-management', [StudentController::class, 'index']);

Route::get('/entry-exit', [AttendanceLogController::class, 'index'])->name('attendance.log');

Route::post('/students', [StudentController::class, 'store'])->name('student.store');

Route::get('/students/{id}/qr-pdf', [QrCodeController::class, 'generate']);

Route::post('/scan', [ScanController::class, 'scan'])->name('scan');

//enrollment
Route::get('/enrollment' , [EnrollmentController::class , 'index']);






// Testing Phase
//web app scanner
Route::get('/scanner', function () {
    return view('scanner.index');
});