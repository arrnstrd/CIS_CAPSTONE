<?php

use App\Http\Controllers\ScannerOperator\Dashboard\DashboardController;
use App\Http\Controllers\ScannerOperator\QrStation\QrStationController;
use App\Http\Controllers\ScannerOperator\QrStation\ScanController;
use App\Http\Controllers\ScannerOperator\TimeInTimeOutHistory\AttendanceLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:scanner_operator'])->group(function () {
    // Dashboard
    Route::get('/scanner/dashboard', [DashboardController::class, 'index'])
        ->name('scanner_operator.dashboard');

    // QR Station & Scanning
    Route::get('/qr-station', [QrStationController::class, 'index'])
        ->name('qr-station.index');
    Route::post('/qr-station/scan', [ScanController::class, 'scan'])
        ->name('qr-station.scan');
    Route::post('/scan', [ScanController::class, 'scan'])
        ->name('scan');

    // Attendance Log & In/Out History
    Route::get('/entry-exit', [AttendanceLogController::class, 'index'])
        ->name('time-in-time-out.index');
    Route::get('/time-in-time-out-history', [AttendanceLogController::class, 'index'])
        ->name('time-in-time-out-history.index');
    Route::get('/time-in-time-out-history/analytics', [AttendanceLogController::class, 'analytics'])
        ->name('time-in-time-out-history.analytics');
    Route::get('/time-in-time-out-history/analytics/download-pdf', [AttendanceLogController::class, 'downloadAnalyticsPdf'])
        ->name('time-in-time-out-history.analytics-pdf');
    Route::get('/time-in-time-out-history/download', [AttendanceLogController::class, 'download'])
        ->name('time-in-time-out-history.download');
    Route::get('/time-in-time-out-history/download-pdf', [AttendanceLogController::class, 'downloadPdf'])
        ->name('time-in-time-out-history.download-pdf');
});
