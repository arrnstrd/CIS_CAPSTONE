<?php

use App\Http\Controllers\Enrollment\EnrollmentController;
use App\Http\Controllers\Enrollment\SchoolYearController;
use App\Http\Controllers\Enrollment\SectionController;
use App\Http\Controllers\Management\StudentController;
use App\Http\Controllers\Management\UserController;
use App\Http\Controllers\Scanner\ScanController;
use App\Http\Controllers\ScheduleConfigController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/scan', [ScanController::class, 'scan']);


//storing
Route::post('/students' , [StudentController::class, 'store'])->name('students.store');
Route::post('/students/{id}' , [StudentController::class, 'update']);
Route::get('/students/{id}' , [StudentController::class , 'show']);


Route::post('/enrollment' , [ EnrollmentController::class , 'store'])->name('enrollments.store');
Route::post('/enrollment/{id}' , [ EnrollmentController::class , 'update']);
Route::delete('/enrollment/{id}' , [EnrollmentController::class , 'destroy']);



// users
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'archive']);
Route::patch('/users/{id}/restore', [UserController::class, 'restore']);
//
Route::post('/schedule-configuration' , [ScheduleConfigController::class , 'store']);
Route::post('/schedule-configuration/{id}' , [ScheduleConfigController::class , 'update']);
Route::delete('/schedule-configuration/{id}' , [ScheduleConfigController::class , 'destroy']);



//Sections
Route::post('/sections', [SectionController::class, 'store']);
Route::post('/sections/{id}' , [SectionController::class, 'update']);
Route::delete('/sections/{id}' , [SectionController::class, 'destroy']);
Route::patch('/sections/{id}' , [SectionController::class, 'restore']);



//school year
Route::post('/school-year', [SchoolYearController::class, 'store']);
Route::post('/school-year/{id}', [SchoolYearController::class, 'update']);
Route::post('/school-year/{id}', [SchoolYearController::class, 'destroy']);
Route::post('/school-year/{id}', [SchoolYearController::class, 'restore']);
