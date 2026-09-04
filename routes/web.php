<?php

use App\Http\Controllers\Shared\AuthController;
use App\Http\Controllers\Shared\SetupController;
use Illuminate\Support\Facades\Route;

// ============================================================
// SPEED TEST
// ============================================================

Route::get('/speed-test', function () {
    return 'OK';
});

// ============================================================
// AUTHENTICATION
// ============================================================

Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.attempt')
    ->middleware('throttle:5,1');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

Route::get('/role-selection', fn() => view('login.role_selection'));

// ============================================================
// ACCOUNT SETUP
// ============================================================

Route::get('/setup/{token}', [SetupController::class, 'show'])
    ->name('setup.show');

Route::post('/setup/{token}', [SetupController::class, 'submit'])
    ->name('setup.submit');

Route::post('/setup/complete', [SetupController::class, 'complete'])
    ->name('setup.complete');

Route::get('/user-login', fn() => view('login.admin-login'));

// ============================================================
// LAYOUT PREVIEW
// ============================================================

Route::get('/layout', fn() => view('components.layouts.admin', [
    'pageName' => 'Admin Layout Preview',
    'subtitle' => 'Head banner preview with live date + help button.',
    'slot' => '<div class="p-4"><h4 class="fw-bold">Slot Content</h4><p class="text-muted">This area renders the page content beneath the banner.</p></div>',
]));

Route::get('/layout/teacher', fn() => view('components.layouts.teacher', [
    'pageName' => 'Teacher Layout Preview',
    'subtitle' => 'Teacher head area preview.',
    'slot' => '<div class="p-4"><h4 class="fw-bold">Slot Content</h4><p class="text-muted">Teacher page content renders here.</p></div>',
]));