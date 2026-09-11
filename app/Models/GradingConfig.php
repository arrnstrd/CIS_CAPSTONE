<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingConfig extends Model
{
    protected $fillable = [
        'teaching_assignment_id',
        'assessment_category_id',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class, 'teaching_assignment_id');
    }

    public function assessmentCategory(): BelongsTo
    {
        return $this->belongsTo(AssessmentCategory::class, 'assessment_category_id');
    }
}