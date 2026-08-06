<?php

namespace App\Models;

use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;


class Enrollment extends Model
{

    protected $fillable = [
        'student_id',
        'section_id',
        'school_year_id',
        'grade_level',
        'section',
        'level',
        'session_type',
        'status'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function sectionModel()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function roomAttendances()
    {
        return $this->hasMany(RoomAttendance::class);
    }

    public function studentAssessmentScores()
    {
        return $this->hasMany(StudentAssessmentScore::class);
    }

    public function quarterlyGrades()
    {
        return $this->hasMany(QuarterlyGrade::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $enrollment) {
            if (empty($enrollment->grade_level) && $enrollment->section_id) {
                $section = Section::find($enrollment->section_id);

                if ($section !== null) {
                    $enrollment->grade_level = (string) $section->grade_level;
                }
            }
        });
    }

    public static function getEnrollmentStatistics(int $schoolYearId): array
    {
        $baseQuery = static::query()->where('school_year_id', $schoolYearId);

        return [
            'total' => (clone $baseQuery)->count(),

            'elementary' => (clone $baseQuery)
                ->where(function ($query) {
                    $query->whereHas('section', function ($sectionQuery) {
                        $sectionQuery->where('level', 'elementary');
                    });
                })
                ->count(),

            'hs' => (clone $baseQuery)
                ->where(function ($query) {
                    $query->whereHas('section', function ($sectionQuery) {
                        $sectionQuery->where('level', 'highschool');
                    });
                })
                ->count(),

            'shs' => (clone $baseQuery)
                ->where(function ($query) {
                    $query->whereHas('section', function ($sectionQuery) {
                        $sectionQuery->where('level', 'senior_high_school');
                    });
                })
                ->count(),
        ];
    }




}
