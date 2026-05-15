<?php

use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;



// for the layouts viewing purposes
Route::get('/layout', function(){
    return view ('components.layouts.admin');
});


//webpages
Route::get('/dashboard', function () {
    return view('adminModules.dashboard');
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








//testing phase - student management==========================
Route::get('/student-management', function(){
    return view('adminModules.management.studentList');
})->name('addStudent');

Route::get('/student-management' , [StudentController::class , 'index']);
Route::get('/entry-exit' , [AttendanceLogController::class , 'index']);



//storing
Route::post('/students' , [StudentController::class, 'store'])->name('student.store');

//for showing/dl of qr code
Route::get('/students/{id}/qr-pdf', [QrCodeController::class, 'generate']);









//for testing only (can be remove soon)
Route::get('/test-email', function () {

    Mail::raw('Testing email', function ($message) {
        $message->to('arriane.dev@gmail.com')
                ->subject('Test');
    });

    return 'Enail Sent!!';
});


Route::post('/scan', [ScanController::class, 'scan'])->name('scan');




//scanner for testing
Route::get('/scanner', function () {
    return view('scanner.index');
});

