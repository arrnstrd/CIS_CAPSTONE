<?php

namespace App\Models;

use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;


class Enrollment extends Model
{

    protected $fillable = [
        'student_id',
        'section_id',
        'school_year_id',
        'grade_level',
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



    public function studentAssessmentScores()
    {
        return $this->hasMany(StudentAssessmentScore::class);
    }

    public function termGrades()
    {
        return $this->hasMany(TermGrade::class);
    }

    public function academicNotes()
    {
        return $this->hasMany(AcademicNote::class);
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
                }
            }

            // The `school_year` column was dropped on non-sqlite drivers
            // (see drop_school_year_from_enrollments_table migration), so only
            // populate it where the column still exists (e.g. sqlite tests).
            if (
                Schema::hasColumn('enrollments', 'school_year')
                && empty($enrollment->getAttribute('school_year'))
                && $enrollment->school_year_id
            ) {
                $schoolYear = SchoolYear::find($enrollment->school_year_id);
                if ($schoolYear !== null) {
                    $enrollment->attributes['school_year'] = (string) $schoolYear->school_year;
                }
            }
        });
    }
}
