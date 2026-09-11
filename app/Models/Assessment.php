<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = [
        'teaching_assignment_id',
        'assessment_category_id',
        'grading_period_id',
        'title',
        'total_items',
        'assessment_date',
        'description',
        'status',
        'slot_number',
        'slot_number',
    ];

    protected $casts = [
        'total_items' => 'integer',
        'assessment_date' => 'date',
    ];

    public function teachingAssignment()
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function assessmentCategory()
    {
        return $this->belongsTo(AssessmentCategory::class);
    }

    public function gradingPeriod()
    {
        return $this->belongsTo(GradingPeriod::class);
    }

    public function studentAssessmentScores()
    {
        return $this->hasMany(StudentAssessmentScore::class);
    }
}
