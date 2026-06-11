<?php

namespace App\Models;

use App\Models\Student;
use App\Models\Teacher;
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
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    


    public static function getEnrollmentStatistics(int $schoolYearId): array
    {
        return [
            'total' => static::query()->where('school_year_id', $schoolYearId)->count(),

            'elementary' => static::query()
                ->where('school_year_id', $schoolYearId)
                ->where('level', 'elementary')
                ->count(),

            'hs' => static::query()
                ->where('school_year_id', $schoolYearId)
                ->where('level', 'hs')
                ->count(),

            'shs' => static::query()
                ->where('school_year_id', $schoolYearId)
                ->where('level', 'shs')
                ->count(),
        ];
    }




}
