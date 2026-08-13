<?php

use App\Http\Controllers\AcademicFeature\EnrollmentController;
use App\Http\Controllers\AcademicFeature\SchoolYearController;
use App\Http\Controllers\AcademicFeature\SectionController;
use App\Http\Controllers\AdministrationFeature\User\UserController;
use App\Http\Controllers\Api\ClassroomVerificationController;
use App\Http\Controllers\QrSystemFeature\ClassroomScanner\ClassroomScanController;
use App\Http\Controllers\QrSystemFeature\GateScanSchedule\ScheduleConfigController;
use App\Http\Controllers\QrSystemFeature\Scanner\ScanController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Teacher\TeacherController;
use App\Http\Controllers\Teacher\TeachingAssignmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// QR SCANNING
Route::post('/scan', [ScanController::class, 'scan']);
Route::post('/classroom-scan', [ClassroomScanController::class, 'scan'])
    ->middleware('auth:sanctum');


// CLASSROOM VERIFICATIONS
Route::prefix('classroom')->middleware('auth:sanctum')->group(function () {
    Route::get('/roster/{teaching_assignment_id}', [ClassroomVerificationController::class, 'roster']);
    Route::post('/verify', [ClassroomVerificationController::class, 'verify']);
});


// STUDENTS
Route::post('/students', [StudentController::class, 'store'])
    ->name('api.students.store');

Route::post('/students/{id}', [StudentController::class, 'update']);

Route::get('/students/{id}', [StudentController::class, 'show']);


// ENROLLMENT
Route::post('/enrollment', [EnrollmentController::class, 'store'])
    ->name('api.enrollments.store');

Route::post('/enrollment/{id}', [EnrollmentController::class, 'update']);

Route::delete('/enrollment/{id}', [EnrollmentController::class, 'destroy']);


// USERS
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::post('/users', [UserController::class, 'store']);

    Route::put('/users/{id}', [UserController::class, 'update']);

    Route::delete('/users/{id}', [UserController::class, 'archive']);

    Route::patch('/users/{id}/restore', [UserController::class, 'restore']);
});


// SCHEDULE CONFIGURATION
Route::post('/schedule-configuration', [ScheduleConfigController::class, 'store']);

Route::post('/schedule-configuration/{id}', [ScheduleConfigController::class, 'update']);

Route::delete('/schedule-configuration/{id}', [ScheduleConfigController::class, 'destroy']);


// SECTIONS
Route::post('/sections', [SectionController::class, 'store']);

Route::post('/sections/{id}', [SectionController::class, 'update']);

Route::delete('/sections/{id}', [SectionController::class, 'destroy']);

Route::patch('/sections/{id}', [SectionController::class, 'restore']);


// SCHOOL YEAR
Route::post('/school-year', [SchoolYearController::class, 'store']);

Route::put('/school-year/{id}', [SchoolYearController::class, 'update']);

Route::delete('/school-year/{id}', [SchoolYearController::class, 'destroy']);

Route::patch('/school-year/{id}', [SchoolYearController::class, 'restore']);


// TEACHING ASSIGNMENTS
Route::apiResource('teaching-assignments', TeachingAssignmentController::class)
    ->names([
        'index' => 'api.teaching-assignments.index',
        'store' => 'api.teaching-assignments.store',
        'show' => 'api.teaching-assignments.show',
        'update' => 'api.teaching-assignments.update',
        'destroy' => 'api.teaching-assignments.destroy',
    ]);


// TEACHERS
Route::prefix('teachers')->group(function () {

    Route::get('/', [TeacherController::class, 'index'])
        ->name('api.teachers.index');

    Route::post('/', [TeacherController::class, 'store'])
        ->name('api.teachers.store');

    Route::put('/{id}', [TeacherController::class, 'update'])
        ->name('api.teachers.update');

    Route::delete('/{id}', [TeacherController::class, 'destroy'])
        ->name('api.teachers.destroy');

    Route::patch('/{id}/restore', [TeacherController::class, 'restore'])
        ->name('api.teachers.restore');
});
