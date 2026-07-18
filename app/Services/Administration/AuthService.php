<?php

namespace App\Services\Administration;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthService
{
    public function attemptLogin(string $email, string $password, string $ip, ?string $userAgent): array
    {
        $key = 'login:' . $ip . ':' . strtolower($email);
        $limitKey = 'login-lock:' . $ip . ':' . strtolower($email);

        if (RateLimiter::tooManyAttempts($limitKey, 5)) {
            LoginLog::create([
                'user_id' => null,
                'email_attempted' => $email,
                'status' => 'locked_out',
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'attempted_at' => now(),
            ]);

            return [
                'success' => false,
                'message' => 'Too many failed attempts. Please try again later.',
            ];
        }

        RateLimiter::hit($key, 60);

        $user = User::where('email', $email)->first();

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

        if (!Hash::check($password, $user->password)) {
            RateLimiter::hit($limitKey, 300);

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

        RateLimiter::clear($key);
        RateLimiter::clear($limitKey);

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
