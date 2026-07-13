<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomAttendance extends Model
{
    protected $table = 'room_attendance';

    public const UPDATED_AT = null;

    protected $fillable = [
        'teaching_assignment_id',
        'enrollment_id',
        'attendance_date',
        'time_in',
        'time_out',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    public function teachingAssignment()
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
