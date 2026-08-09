<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'email',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function advisedSections()
    {
        return $this->hasMany(Section::class, 'advisor_id');
    }

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when(
            filled($search),
            function (Builder $query) use ($search) {
                $query->whereHas('user', function (Builder $query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            }
        );
    }

    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        return $query->when(
            filled($status),
            fn(Builder $query) => $query->where('status', $status)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return trim(
            ($this->user?->first_name ?? '') . ' ' .
                ($this->user?->last_name ?? '')
        );
    }

    // NOTE: The `first_name`/`last_name` columns were removed from the
    // `teachers` table (see remove_name_fields_from_teachers_table migration).
    // Teacher names are stored on the related `users` record and exposed via
    // the `full_name` accessor, so no creating-hook is needed here.
}
