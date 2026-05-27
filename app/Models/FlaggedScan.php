<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AttendanceLog;

class FlaggedScan extends Model
{
    //
    protected $fillable = [
        'attendance_log_id',
        'flag_type',
        'description'
    ];


    public function attendanceLog()
    {
        return $this->belongsTo(AttendanceLog::class, 'attendance_log_id');
    }
}