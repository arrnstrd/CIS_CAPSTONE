<?php

use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ScheduleConfigController;
use App\Http\Controllers\StudentController;
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


Route::post('/enrollment' , [ EnrollmentController::class , 'store'])->name('enrollment.store');
Route::post('/enrollment/{id}' , [ EnrollmentController::class , 'update']);
Route::delete('/enrollment/{id}' , [EnrollmentController::class , 'destroy']);


//
Route::post('/schedule-configuration' , [ScheduleConfigController::class , 'store']);
Route::post('/schedule-configuration/{id}' , [ScheduleConfigController::class , 'update']);
Route::delete('/schedule-configuration/{id}' , [ScheduleConfigController::class , 'destroy']);
