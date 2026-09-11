<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'actor_id',
        'actor_email',
        'actor_name',
        'action',
        'target_type',
        'target_id',
        'target_identifier',
        'ip_address',
        'user_agent',
        'result',
        'details',
    ];

    public const UPDATED_AT = null;

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Record an administrative activity log entry.
     */
    public static function record(
        ?User $actor,
        string $action,
        ?string $targetIdentifier = null,
        string $result = 'success',
        ?string $details = null,
        string $targetType = 'User',
        ?int $targetId = null
    ): self {
        $ip = Request::ip();
        $userAgent = Request::header('User-Agent');

        return static::create([
            'actor_id' => $actor?->id,
            'actor_email' => $actor?->email ?? 'System / Guest',
            'actor_name' => $actor ? ($actor->first_name . ' ' . $actor->last_name) : 'System',
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'target_identifier' => $targetIdentifier,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'result' => $result,
            'details' => $details,
        ]);
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
