<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ScheduleConfig extends Model
{
    //
    protected $fillable = [
        'level',
        'session_type',
        'in_start',
        'in_end',
        'late_threshold',
        'out_start',
        'out_end'
    ];

    protected static function booted(): void
    {
        // Invalidate the cached schedule when it changes (Redis is only a cache;
        // PostgreSQL remains authoritative).
        static::saved(fn($config) => static::forgetCached($config));
        static::deleted(fn($config) => static::forgetCached($config));
    }

    protected static function forgetCached($config): void
    {
        try {
            Cache::store('redis')->forget("qr:schedule:{$config->level}:{$config->session_type}");
        } catch (\Throwable $e) {
            // Redis unavailable — the TTL will refresh the entry.
        }
    }
}
