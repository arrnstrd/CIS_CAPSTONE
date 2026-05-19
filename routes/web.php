<?php

use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ScheduleConfigController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// misc
Route::get('/layout', fn() => view('components.layouts.admin'));
Route::get('/dashboard', fn() => view('admin-modules.dashboard'));
Route::get('/role-selection', fn() => view('login.role_selection'));

// monitoring
Route::get('/entry_exit', fn() => view('admin-modules.monitoring.entry-exit'))->name('entryExit');
Route::get('/attendance', fn() => view('admin-modules.monitoring.class-attendance'))->name('attendance');
Route::get('/entry-exit', [AttendanceLogController::class, 'index'])->name('attendance.log');
Route::get('/emails', [EmailLogController::class, 'index']);

// students
Route::get('/student-management', [StudentController::class, 'index'])->name('addStudent');
Route::post('/students', [StudentController::class, 'store'])->name('student.store');
Route::get('/students/search', [StudentController::class, 'search'])->name('students.search');
Route::get('/students/{id}/qr-pdf', [QrCodeController::class, 'generate']);
Route::get('/student-management/search', [SearchController::class, 'searchStudent'])->name('search.students');

// enrollment
Route::get('/enrollment', [EnrollmentController::class, 'index'])->name('enrollment.index');
Route::post('/enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');

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