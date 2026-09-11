<?php

use App\Http\Controllers\Api\ClassroomVerificationController;
use App\Http\Controllers\ScannerOperator\QrStation\ScanController;
use App\Http\Controllers\SchoolAdmin\Academic\SchoolYearController;
use App\Http\Controllers\SchoolAdmin\Academic\SectionController;
use App\Http\Controllers\SchoolAdmin\ScheduleConfiguration\ScheduleConfigController;
use App\Http\Controllers\SchoolAdmin\Students\StudentManagementController;
use App\Http\Controllers\SchoolAdmin\Teachers\TeacherManagementController;
use App\Http\Controllers\SchoolAdmin\TeachingAssignments\TeachingAssignmentController;
use App\Http\Controllers\SuperAdmin\UserManagement\UserManagementController as SuperAdminUserManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// QR SCANNING
Route::post('/scan', [ScanController::class, 'scan']);

// CLASSROOM VERIFICATIONS
Route::prefix('classroom')->middleware('auth:sanctum')->group(function () {
    Route::get('/roster/{teaching_assignment_id}', [ClassroomVerificationController::class, 'roster']);
    Route::post('/verify', [ClassroomVerificationController::class, 'verify']);
});

// STUDENTS
Route::post('/students', [StudentManagementController::class, 'store'])
    ->name('api.students.store');

Route::post('/students/{id}', [StudentManagementController::class, 'update']);

Route::get('/students/{id}', [StudentManagementController::class, 'show']);

// USERS
Route::middleware(['web', 'auth', 'role:super_admin'])->group(function () {

    Route::post('/users', [SuperAdminUserManagementController::class, 'store']);

    Route::put('/users/{id}', [SuperAdminUserManagementController::class, 'update']);

    Route::delete('/users/{id}', [SuperAdminUserManagementController::class, 'archive']);

    Route::patch('/users/{id}/restore', [SuperAdminUserManagementController::class, 'restore']);

    Route::post('/users/{id}/resend-invitation', [SuperAdminUserManagementController::class, 'resendInvitation'])
        ->name('api.users.resend-invitation');

    Route::post('/users/{id}/deactivate', [SuperAdminUserManagementController::class, 'deactivate'])
        ->name('api.users.deactivate');

    Route::post('/users/{id}/reactivate', [SuperAdminUserManagementController::class, 'reactivate'])
        ->name('api.users.reactivate');

    Route::post('/users/bulk-deactivate', [SuperAdminUserManagementController::class, 'bulkDeactivate'])
        ->name('api.users.bulk-deactivate');

    Route::post('/users/bulk-reactivate', [SuperAdminUserManagementController::class, 'bulkReactivate'])
        ->name('api.users.bulk-reactivate');

    Route::post('/users/bulk-resend-invitation', [SuperAdminUserManagementController::class, 'bulkResendInvitation'])
        ->name('api.users.bulk-resend-invitation');
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

    Route::get('/', [TeacherManagementController::class, 'index'])
        ->name('api.teachers.index');

    Route::post('/', [TeacherManagementController::class, 'store'])
        ->name('api.teachers.store');

    Route::put('/{id}', [TeacherManagementController::class, 'update'])
        ->name('api.teachers.update');

    Route::delete('/{id}', [TeacherManagementController::class, 'destroy'])
        ->name('api.teachers.destroy');

    Route::patch('/{id}/restore', [TeacherManagementController::class, 'restore'])
        ->name('api.teachers.restore');
});
