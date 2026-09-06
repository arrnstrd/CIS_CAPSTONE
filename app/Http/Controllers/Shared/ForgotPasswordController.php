<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Services\Administration\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(
        protected PasswordResetService $passwordResetService
    ) {
    }

    /**
     * Display the email request form for password recovery (State 1 & State 2).
     */
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle sending password reset link to user.
     * Always returns an identical generic status message (Account Enumeration Defense).
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $this->passwordResetService->sendResetLink($request->input('email'));

        return back()->with(
            'status',
            'If an account exists with this email address, you will receive an email with instructions to reset your password.'
        );
    }

    /**
     * Display the password reset form guarded by the token (State 3).
     */
    public function showResetForm(Request $request): View
    {
        $token = $request->query('token');
        $email = $request->query('email');

        $isValid = $this->passwordResetService->validateToken($token, $email);

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
            'isValid' => $isValid,
        ]);
    }

    /**
     * Reset the user's password with the given credentials.
     * Never automatically authenticates the user post-reset.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $result = $this->passwordResetService->resetPassword(
            $request->input('email'),
            $request->input('token'),
            $request->input('password')
        );

        if (!$result['success']) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['password' => $result['message']]);
        }

        // Redirect to login with confirmation; do NOT auto-login
        return redirect()
            ->route('login')
            ->with('success', $result['message']);
    }
}
