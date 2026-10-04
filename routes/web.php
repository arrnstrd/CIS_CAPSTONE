<?php

use App\Http\Controllers\Shared\AuthController;
use App\Http\Controllers\Shared\ForgotPasswordController;
use App\Http\Controllers\Shared\SetupController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;



//smtp testing 
Route::get('/test-mail', function () {
    try {
        Mail::raw('SMTP is working perfectly!', function ($message) {
            $message->to('202312089@btech.ph.education') 
                    ->subject('Laravel SMTP Test');
        });
        
        return 'Email sent successfully! Check your inbox.';
    } catch (\Exception $e) {
        return 'Mail sending failed: ' . $e->getMessage();
    }
});





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
// PASSWORD RECOVERY (OPTION B - RESET LINK)
// ============================================================

Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request');

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email')
    ->middleware('throttle:password-reset');

Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])
    ->name('password.update')
    ->middleware('throttle:5,1');

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