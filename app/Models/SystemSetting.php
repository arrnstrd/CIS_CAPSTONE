<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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