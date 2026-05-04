<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\Teacher;


class Enrollment extends Model
{
    //

    protected $fillable = [
        'student_id',
        'school_year',
        'grade_level',
        'section',
        'level',
        'session_type',
        'adviser_id',
        'status'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class , 'student_id');
    }

    public function adviser(){
        return $this->belongsTo(Teacher::class , 'adviser_id');
    }




}
