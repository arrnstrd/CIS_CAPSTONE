<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    //

    protected $fillable = [
        'scan_type',
        'session_type',
        'device_id'
    ];

    protected $casts = [
        'scan_time'
        
    ];
}
