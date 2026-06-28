<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'name',
        'level',
        'grade_level',
        'advisor_id',
        'capacity',
        'status'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function advisor()
    {
        return $this->belongsTo(Teacher::class, 'advisor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeFilterGradeLevel(Builder $query, $gradeLevel): Builder
    {
        return $query->when(
            filled($gradeLevel),
            fn (Builder $query) => $query->where('grade_level', $gradeLevel)
        );
    }

    public function scopeFilterStatus(Builder $query, $status): Builder
    {
        return $query->when(
            filled($status),
            fn (Builder $query) => $query->where('status', $status)
        );
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when(
            filled($search),
            function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('grade_level', 'like', "%{$search}%");
                });
            }
        );
    }
}