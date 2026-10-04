<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;

class SetupController extends Controller
{
    /**
     * Show the setup form for the given token.
     */
    public function show(Request $request, string $token)
    {
        $invitationService = app(InvitationService::class);
        $invitation = $invitationService->validate($token);
        
        $email = $request->input('email');
        if ($invitation && $invitation->user) {
            $email = $email ?: $invitation->user->email;
        }

        $isValid = (bool) ($invitation && $invitation->user && !empty($email));

        return view('auth.setup', [
            'token' => $token,
            'email' => $email,
            'isValid' => $isValid,
        ]);
    }

    /**
     * Handle form submission for setup via token URL.
     */
    public function submit(Request $request, ?string $token = null)
    {
        if ($token && !$request->has('token')) {
            $request->merge(['token' => $token]);
        }

        return $this->complete($request);
    }

    /**
     * Complete the account setup process.
     */
    public function complete(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::min(8)],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'No account found with this email address.']);
        }

        // Validate token using InvitationService
        $invitationService = app(InvitationService::class);
        $invitation = $invitationService->validate($request->token);

        // If invitation records exist for this user, require a valid invitation token
        if (!$invitation && $user->invitationTokens()->exists()) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['token' => 'This account setup link is invalid, has expired, or has already been used.']);
        }

        // Update the user's password and status
        $user->update([
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        // Invalidate the invitation token if valid record was found
        if ($invitation) {
            $invitationService->invalidate($invitation);
        }

        // Log the user in
        auth()->login($user);

        $route = match (true) {
            $user->isSuperAdmin() => 'super_admin.dashboard',
            $user->isAdmin() => 'admin.dashboard',
            $user->isTeacher() => 'teacher.dashboard',
            $user->isScannerOperator() => 'qr-station.index',
            default => 'login',
        };

        return redirect()->route($route)->with('success', 'Account setup completed successfully!');
    }
}