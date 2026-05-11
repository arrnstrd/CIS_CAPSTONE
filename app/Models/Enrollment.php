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
        'level',
        'grade_level',
        'section',
        'session_type',
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
