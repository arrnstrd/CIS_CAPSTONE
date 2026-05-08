<?php

use App\Http\Controllers\StudentController;
use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('adminModules.dashboard');
});


Route::get('/layout', function(){
    return view ('components.layouts.admin');
});



Route::get('/entry_exit' , function (){
    return view ('adminModules.monitoring.entry_exit_monitoring');
})->name('entryExit');



Route::get('/attendance' , function (){
    return view ('adminModules.monitoring.attendance');
})->name('attendance');








//role selection for login
Route::get('/role-selection' , function(){
    return view ('login.role_selection');
});



//testing phase - student management
Route::get('/student-management', function(){
    return view('adminModules.management.studentList');
})->name('addStudent');



Route::post('/students' , [StudentController::class, 'store'])->name('students.store');


Route::get('/students/{id}/qr-pdf', [QrCodeController::class, 'generate']);