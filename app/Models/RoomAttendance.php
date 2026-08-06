<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomAttendance extends Model
{
    use HasFactory;

    /**
     * Explicitly specify the table name.
     * Overrides Laravel's default pluralization ('room_attendances').
     */
    protected $table = 'room_attendance';

    protected $fillable = [
        'teaching_assignment_id',
        'enrollment_id',
        'attendance_date',
        'time_in',
        'time_out',
        'status',
        'remarks',
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