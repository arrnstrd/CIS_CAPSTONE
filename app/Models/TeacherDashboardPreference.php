<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherDashboardPreference extends Model
{
    use HasFactory;

    protected $table = 'teacher_dashboard_preferences';

    protected $fillable = [
        'user_id',
        'default_view',
        'dashboard_density',
        'show_grading_progress',
        'show_class_health',
        'show_at_risk',
        'show_recent_activity',
        'show_summary_cards',
        'show_student_counts',
        'show_progress_indicators',
        'default_class_id',
        'default_term',
        'theme',
    ];

    protected $casts = [
        'show_grading_progress' => 'boolean',
        'show_class_health' => 'boolean',
        'show_at_risk' => 'boolean',
        'show_recent_activity' => 'boolean',
        'show_summary_cards' => 'boolean',
        'show_student_counts' => 'boolean',
        'show_progress_indicators' => 'boolean',
        'default_class_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function defaultClass(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class, 'default_class_id');
    }
}
