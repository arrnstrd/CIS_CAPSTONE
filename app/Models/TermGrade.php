<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermGrade extends Model
{
    protected $fillable = [
        'teaching_assignment_id',
        'enrollment_id',
        'grading_period_id',
        'written_work_grade',
        'performance_task_grade',
        'term_assessment_grade',
        'initial_grade',
        'transmuted_grade',
    ];

    protected $casts = [
        'written_work_grade' => 'decimal:2',
        'performance_task_grade' => 'decimal:2',
        'term_assessment_grade' => 'decimal:2',
        'initial_grade' => 'decimal:2',
        'transmuted_grade' => 'decimal:2',
    ];

    public function teachingAssignment()
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function gradingPeriod()
    {
        return $this->belongsTo(GradingPeriod::class);
    }
}
