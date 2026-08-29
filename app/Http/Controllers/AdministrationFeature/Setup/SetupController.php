<?php

namespace App\Http\Controllers\AdministrationFeature\Setup;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        // In a real implementation, you would validate the token against a password reset tokens table
        // For now, we'll just show the form and validate the token exists in the URL
        
        return view('auth.setup', [
            'token' => $token,
            'email' => $request->input('email'),
        ]);
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

        // Find user by email (in a real implementation, you would validate the token)
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'No account found with this email address.']);
        }

        // Update the user's password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Log the user in
        auth()->login($user);

        return redirect()->route('admin.dashboard')->with('success', 'Account setup completed successfully!');
    }
}