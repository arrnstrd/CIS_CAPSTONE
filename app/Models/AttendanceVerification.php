<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceVerification extends Model
{
    protected $fillable = [
        'enrollment_id',
        'teaching_assignment_id',
        'attendance_log_id',
        'attendance_date',
        'status',
        'remarks',
        'verified_at',
        'teacher_id',
        'resolved_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'verified_at' => 'datetime',
    ];

    // Allowed status values (plain varchar column, enforced in code only)
    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LATE = 'late';
    public const STATUS_EXCUSED = 'excused';
    public const STATUS_NOT_IN_CLASSROOM = 'not_in_classroom';
    public const STATUS_NO_DATA = 'no_data';

    public const STATUSES = [
        self::STATUS_PRESENT => 'Present',
        self::STATUS_ABSENT => 'Absent',
        self::STATUS_LATE => 'Late',
        self::STATUS_EXCUSED => 'Excused',
        self::STATUS_NOT_IN_CLASSROOM => 'Not in Classroom',
    ];

    public const STATUS_LABELS_EXTENDED = self::STATUSES + [
        self::STATUS_NO_DATA => 'No Data Yet',
    ];

    // 'resolved_by' values — who made this change
    public const RESOLVED_BY_TEACHER = 'teacher';
    public const RESOLVED_BY_SYSTEM = 'system';

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function teachingAssignment()
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function attendanceLog()
    {
        return $this->belongsTo(AttendanceLog::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }
}