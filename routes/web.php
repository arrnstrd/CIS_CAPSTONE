<?php

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







//qr
Route::get('/qr_code' , function(){
    return view ('qr');
});