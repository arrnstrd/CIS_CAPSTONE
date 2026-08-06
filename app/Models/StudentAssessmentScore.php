<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAssessmentScore extends Model
{
    protected $fillable = [
        'assessment_id',
        'enrollment_id',
        'score',
        'remarks',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }
}
