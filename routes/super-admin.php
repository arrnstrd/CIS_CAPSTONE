<?php

use App\Http\Controllers\SuperAdmin\Dashboard\DashboardController;
use App\Http\Controllers\SuperAdmin\RecentActivity\RecentActivityController;
use App\Http\Controllers\SuperAdmin\SecurityAuditLog\SecurityAuditLogController;
use App\Http\Controllers\SuperAdmin\UserManagement\TeacherProvisioningController;
use App\Http\Controllers\SuperAdmin\UserManagement\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:super_admin'])->group(function () {
    // Dashboard
    Route::get('/super-admin/dashboard', [DashboardController::class, 'index'])
        ->name('super_admin.dashboard');

    // Teacher account creation (Super Admin only — School Admin must not create teacher accounts)
    Route::post('/teachers', [TeacherProvisioningController::class, 'store'])
        ->name('teachers.store');

    // User Management
    Route::get('/users', [UserManagementController::class, 'index'])
        ->name('users.index');

    // Security Audit Log Page & Downloads
    Route::get('/security-audit-log', [SecurityAuditLogController::class, 'index'])
        ->name('security-audit-log.index');
    Route::get('/security-audit-log/download', [SecurityAuditLogController::class, 'downloadExcel'])
        ->name('security-audit-log.download');
    Route::get('/security-audit-log/download-pdf', [SecurityAuditLogController::class, 'downloadPdf'])
        ->name('security-audit-log.download-pdf');

    // Recent Activity Page & Downloads
    Route::get('/recent-activity', [RecentActivityController::class, 'index'])
        ->name('recent-activity.index');
    Route::get('/recent-activity/download', [RecentActivityController::class, 'downloadExcel'])
        ->name('recent-activity.download');
    Route::get('/recent-activity/download-pdf', [RecentActivityController::class, 'downloadPdf'])
        ->name('recent-activity.download-pdf');
});
