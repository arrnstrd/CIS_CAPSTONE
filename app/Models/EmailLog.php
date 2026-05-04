<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\AttendanceLog;


class EmailLog extends Model
{
    //

    protected $fillable = [
        'attendance_log_id',
        'student_id',
        'email',
        'scan_type',
        'status',
        'attempt_count',
        'last_attempt_at',
        'sent_at'

    ];


    protected $casts = [
        'last_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class , 'student_id');
    }

    public function attendanceLog()
    {
        return $this->belongsTo(AttendanceLog::class , 'attendance_log_id');
    }
}
