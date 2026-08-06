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
        // Use saving so attributes are set for both create and update flows.
        static::saving(function (self $enrollment) {
            if ($enrollment->section_id) {
                $section = Section::find($enrollment->section_id);

                if ($section !== null) {
                    // Set raw attributes to avoid conflicting with the
                    // `section()` relation accessor.
                    $enrollment->attributes['grade_level'] = (string) $section->grade_level;
                    $enrollment->attributes['section'] = $section->name;
                }
            }

            // Ensure backwards-compatible `school_year` string is populated
            // for sqlite/in-memory tests where migrations that drop the
            // `school_year` column may be skipped.
            if (empty($enrollment->getAttribute('school_year')) && $enrollment->school_year_id) {
                $schoolYear = SchoolYear::find($enrollment->school_year_id);
                if ($schoolYear !== null) {
                    $enrollment->attributes['school_year'] = (string) $schoolYear->school_year;
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
