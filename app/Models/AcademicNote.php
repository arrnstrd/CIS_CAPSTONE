<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicNote extends Model
{
    protected $fillable = [
        'enrollment_id',
        'teacher_id',
        'note',
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

