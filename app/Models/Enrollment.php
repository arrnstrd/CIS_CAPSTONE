<?php

namespace App\Models;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class Enrollment extends Model
{

    protected $fillable = [
        'student_id',
        'school_year_id',
        'level',
        'grade_level',
        'section',
        'session_type',
        'status'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function adviser()
    {
        return $this->belongsTo(Teacher::class, 'adviser_id');
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_years_id');
    }

    


    //for overview cards
    public function scopeGetEnrollmentStatistics(Builder $query)
    {
        return [
            'total' => (clone $query)->count(),

            'elementary' => (clone $query)
                ->where('level', 'elementary')
                ->count(),

            'hs' => (clone $query)
                ->where('level', 'hs')
                ->count(),

            'shs' => (clone $query)
                ->where('level', 'shs')
                ->count(),
        ];
    }




}
