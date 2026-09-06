<?php

namespace App\Services\Administration;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService
{
    /**
     * Expiration time in minutes for password reset tokens.
     */
    public const TOKEN_EXPIRE_MINUTES = 60;

    /**
     * Send a password reset link to the given email if a matching account exists.
     * Enforces strict account enumeration defense by returning cleanly in all cases.
     */
    public function sendResetLink(string $email): void
    {
        $normalizedEmail = strtolower(trim($email));

        $user = User::where('email', $normalizedEmail)->first();

        if (!$user) {
            // Account enumeration defense: do not disclose that user does not exist
            return;
        }

        // Generate cryptographically secure reset token
        $rawToken = Str::random(64);

        // Invalidate any previous reset tokens for this email
        DB::table('password_reset_tokens')->where('email', $normalizedEmail)->delete();

        // Persist hashed token with creation timestamp
        DB::table('password_reset_tokens')->insert([
            'email' => $normalizedEmail,
            'token' => Hash::make($rawToken),
            'created_at' => Carbon::now(),
        ]);

        try {
            Mail::to($user->email)->send(new ResetPasswordMail(
                email: $user->email,
                token: $rawToken,
                name: trim($user->first_name . ' ' . $user->last_name)
            ));
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage(), [
                'email' => $normalizedEmail,
            ]);
        }
    }

    /**
     * Validate the provided reset token and email.
     */
    public function validateToken(?string $token, ?string $email): bool
    {
        if (empty($token) || empty($email)) {
            return false;
        }

        $normalizedEmail = strtolower(trim($email));

        $record = DB::table('password_reset_tokens')
            ->where('email', $normalizedEmail)
            ->first();

        if (!$record) {
            return false;
        }

        // Validate expiration window (60 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(self::TOKEN_EXPIRE_MINUTES)->isPast()) {
            return false;
        }

        // Validate token hash
        return Hash::check($token, $record->token);
    }

    /**
     * Reset the user password, flush the token, and invalidate existing sessions.
     *
     * @return array{success: bool, message: string}
     */
    public function resetPassword(string $email, string $token, string $password): array
    {
        $normalizedEmail = strtolower(trim($email));

        if (!$this->validateToken($token, $normalizedEmail)) {
            return [
                'success' => false,
                'message' => 'This password reset link is invalid or has expired.',
            ];
        }

        $user = User::where('email', $normalizedEmail)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => 'This password reset link is invalid or has expired.',
            ];
        }

        // Update password with secure hash
        $user->update([
            'password' => Hash::make($password),
        ]);

        // Permanently flush token immediately (single-use enforcement)
        DB::table('password_reset_tokens')
            ->where('email', $normalizedEmail)
            ->delete();

        // Invalidate existing active sessions for this user
        try {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();
        } catch (\Throwable $e) {
            // Silently handle if driver does not support database session purging
        }

        return [
            'success' => true,
            'message' => 'Your password has been successfully reset. Please sign in with your new password.',
        ];
    }
}
