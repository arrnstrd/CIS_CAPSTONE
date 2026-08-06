<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    public const LEVEL_LABELS = [
        'elementary' => 'Elementary',
        'hs' => 'High School',
        'shs' => 'Senior High School',
    ];

    protected $fillable = [
        'code',
        'name',
        'level',
    ];

    public static function normalizeLevel(?string $level): ?string
    {
        return match ($level) {
            'highschool' => 'hs',
            'senior_high_school' => 'shs',
            default => $level,
        };
    }

    public static function levelOptions(): array
    {
        return self::LEVEL_LABELS;
    }

    public function getLevelLabelAttribute(): string
    {
        return self::LEVEL_LABELS[$this->level] ?? $this->level;
    }

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }
}
