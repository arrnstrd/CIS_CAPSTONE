<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_enabled',
        'grading_enabled',
        'at_risk_enabled',
        'analytics_enabled',
        'import_enabled',
    ];

    protected $casts = [
        'attendance_enabled' => 'boolean',
        'grading_enabled' => 'boolean',
        'at_risk_enabled' => 'boolean',
        'analytics_enabled' => 'boolean',
        'import_enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check whether notifications for a specific category are enabled.
     */
    public function isEnabled(string $category): bool
    {
        return match ($category) {
            'attendance' => $this->attendance_enabled,
            'grading' => $this->grading_enabled,
            'at_risk' => $this->at_risk_enabled,
            'analytics' => $this->analytics_enabled,
            'import' => $this->import_enabled,
            default => true,
        };
    }
}
