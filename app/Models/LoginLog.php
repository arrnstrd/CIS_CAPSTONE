<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $fillable = [
        'user_id',
        'email_attempted',
        'status',
        'ip_address',
        'user_agent',
        'attempted_at',
    ];

    public const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Parse user agent string into readable Browser · OS representation.
     */
    public function getFormattedDeviceAttribute(): string
    {
        $ua = $this->user_agent;
        if (empty($ua)) {
            return 'Unknown Device';
        }

        // Browser Detection
        $browser = 'Unknown Browser';
        if (preg_match('/edg/i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome|crios/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox|fxios/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/opr|opera/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/trident|msie/i', $ua)) {
            $browser = 'Internet Explorer';
        }

        // OS Detection
        $os = 'Unknown OS';
        if (preg_match('/iphone/i', $ua)) {
            $os = 'iPhone';
        } elseif (preg_match('/ipad/i', $ua)) {
            $os = 'iPad';
        } elseif (preg_match('/android/i', $ua)) {
            $os = 'Android';
        } elseif (preg_match('/win/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/mac/i', $ua)) {
            $os = 'macOS';
        } elseif (preg_match('/linux/i', $ua)) {
            $os = 'Linux';
        }

        return "{$browser} · {$os}";
    }
}
