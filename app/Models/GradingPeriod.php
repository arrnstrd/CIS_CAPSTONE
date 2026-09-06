<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    /** Limit application queries to the configured trimester calendar. */
    public function scopeTrimester(Builder $query): Builder
    {
        return $query->where('period_type', 'trimester')->where('sequence', '<=', 3);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function termGrades()
    {
        return $this->hasMany(TermGrade::class);
    }
}
