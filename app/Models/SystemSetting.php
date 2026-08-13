<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    protected static function booted(): void
    {
        // Invalidate the cached scan settings (QR Station) when a setting changes.
        static::saved(fn() => static::forgetScanSettingsCache());
        static::deleted(fn() => static::forgetScanSettingsCache());
    }

    protected static function forgetScanSettingsCache(): void
    {
        try {
            Cache::store('redis')->forget('qr:scan-settings');
        } catch (\Throwable $e) {
            // Redis unavailable — the TTL will refresh the entry.
        }
    }

    /**
     * Get the setting value.
     *
     * @param  string  $key
     * @return mixed
     */
    public static function getValue(string $key)
    {
        return static::where('key', $key)->value('value');
    }

    /**
     * Set the setting value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public static function setValue(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
    }
}
