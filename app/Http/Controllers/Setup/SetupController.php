<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\InvitationToken;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SetupController extends Controller
{
    protected InvitationService $invitationService;

    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    /**
     * Display the password setup form for a valid invitation token.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function show(string $token)
    {
        $invitation = $this->invitationService->validate($token);

        if (! $invitation) {
            return redirect()->route('login')
                ->with('error', 'Invalid or expired invitation token.');
        }

        $user = $invitation->user;

        // Only PENDING users can complete the setup
        if ($user->status !== 'pending') {
            return redirect()->route('login')
                ->with('error', 'This invitation is no longer valid. Account status is not PENDING.');
        }

        return view('setup.password-setup', [
            'token' => $token,
            'userName' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->email,
        ]);
    }

    /**
     * Handle the password setup form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request, string $token)
    {
        $request->validate([
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $invitation = $this->invitationService->validate($token);

        if (! $invitation) {
            return redirect()->route('login')
                ->with('error', 'Invalid or expired invitation token.');
        }

        $user = $invitation->user;

        // Only PENDING users can be activated
        if ($user->status !== 'pending') {
            return redirect()->route('login')
                ->with('error', 'Account is no longer in PENDING status.');
        }

        // Hash the password and set it on the user
        $user->password = Hash::make($request->password);
        $user->status = 'active';
        $user->save();

        // Mark the invitation token as used
        $invitation->markUsed();

        // Log the activation
        // Auth::login($user); // Login handled separately

        return redirect()->route('login')
            ->with('success', 'Your account has been activated. You can now log in.');
    }
}