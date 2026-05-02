<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleConfig extends Model
{
    //
    protected $fillable = [
        'level',
        'session_type',
        'in_start',
        'in_end', 
        'late_threshold',
        'out_start',
        'out_end'
    ];


}
