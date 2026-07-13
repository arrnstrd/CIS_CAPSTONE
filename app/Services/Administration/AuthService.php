<?php

namespace App\Services\Administration;

use App\Models\User;
use App\Models\LoginLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function attemptLogin(string $email, string $password, string $ip, ?string $userAgent): array
    {
        $user = User::where('email', $email)->first();

        // If no user found, log failed attempt with null user_id
        if (!$user) {
            LoginLog::create([
                'user_id' => null,
                'email_attempted' => $email,
                'status' => 'failed',
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'attempted_at' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'Invalid credentials.',
            ];
        }

        // If user found but status is not active, log locked_out
        if ($user->status !== 'active') {
            LoginLog::create([
                'user_id' => $user->id,
                'email_attempted' => $email,
                'status' => 'locked_out',
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'attempted_at' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'Account is not available. Contact an administrator.',
            ];
        }

        // If user is active, verify password
        if (!Hash::check($password, $user->password)) {
            LoginLog::create([
                'user_id' => $user->id,
                'email_attempted' => $email,
                'status' => 'failed',
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'attempted_at' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'Invalid credentials.',
            ];
        }

        // Password is correct - log success and login
        LoginLog::create([
            'user_id' => $user->id,
            'email_attempted' => $email,
            'status' => 'success',
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'attempted_at' => now(),
        ]);

        Auth::login($user);
        request()->session()->regenerate();

        return [
            'success' => true,
            'message' => 'Login successful.',
            'user' => $user,
        ];
    }
}
