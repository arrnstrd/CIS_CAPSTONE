<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskFollowUp extends Model
{
    protected $fillable = [
        'enrollment_id',
        'teacher_id',
        'follow_up_date',
        'intervention',
        'notes',
        'status',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}

