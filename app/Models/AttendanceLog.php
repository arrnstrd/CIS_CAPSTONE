<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Enrollment;


class AttendanceLog extends Model
{
    //

    protected $fillable = [
        'enrollment_id',
        'scan_type',
        'session_type',
        'scan_time',
        'scanned_by_user_id',
        'device_id'
    ];

    public function enrollment(){
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function user(){
        return $this->belongsTo(User::class , 'user_id');
    }


}
