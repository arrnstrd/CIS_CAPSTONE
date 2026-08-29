<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingPeriod extends Model
{
    protected $fillable = [
        'name',
        'sequence',
        'period_type',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'period_type' => 'string',
        'is_active' => 'boolean',
    ];

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function quarterlyGrades()
    {
        return $this->hasMany(QuarterlyGrade::class);
    }
}
