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

    public const SUBJECT_TYPE_LABELS = [
        'core' => 'Core Subject',
        'academic_elective_general' => 'Academic Elective (General)',
        'academic_elective_special' => 'Academic Elective (Special)',
        'techpro_elective' => 'TechPro Elective',
        'techpro_work_immersion' => 'TechPro Work Immersion',
    ];

    protected $fillable = [
        'code',
        'name',
        'level',
        'subject_type',
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

    public function getSubjectTypeLabelAttribute(): string
    {
        return self::SUBJECT_TYPE_LABELS[$this->subject_type] ?? $this->subject_type ?? 'Not Set';
    }

    public function teachingAssignments()
    {
        return $this->hasMany(TeachingAssignment::class);
    }
}
