<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrAttendance extends Model
{
    protected $fillable = [
        'enrollment_id',
        'attendance_date',
        'time_in_log_id',
        'time_out_log_id',
        'spam_offense_count',
        'cooldown_expires_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'spam_offense_count' => 'integer',
        'cooldown_expires_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function timeInLog(): BelongsTo
    {
        return $this->belongsTo(AttendanceLog::class, 'time_in_log_id');
    }

    public function timeOutLog(): BelongsTo
    {
        return $this->belongsTo(AttendanceLog::class, 'time_out_log_id');
    }
}