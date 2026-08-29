<?php

namespace App\Services\Administration;

use App\Mail\SetupInvitationMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class SetupService
{
    /**
     * Send a setup invitation to a user.
     */
    public function sendSetupInvitation(User $user): void
    {
        // Generate a simple token (in production, use a more secure method)
        $token = \Illuminate\Support\Str::random(60);
        
        // Store the token (in production, you'd store this in a password_resets table)
        // For now, we'll just pass it in the email URL
        
        Mail::to($user->email)->send(new SetupInvitationMail(
            email: $user->email,
            token: $token,
            name: $user->first_name . ' ' . $user->last_name
        ));
    }

    /**
     * Check if a user has completed setup.
     */
    public function isSetupCompleted(User $user): bool
    {
        // In a real implementation, you might check if the user has set a password
        // or completed other setup steps
        return !empty($user->password);
    }
}