<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AttendanceVerification extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendance_verifications';
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
    protected $fillable = [
        'attendance_log_id',
        'enrollment_id',
        'teaching_assignment_id',
        'attendance_date',
        'teacher_id',
        'resolved_by',
        'status',
        'remarks',
        'verified_at',
    ];
    protected $casts = [
        'attendance_date' => 'date',
        'verified_at' => 'datetime',
    ];
    // Allowed status values (plain varchar column, enforced in code only)
    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LATE = 'late';
    public const STATUS_EXCUSED = 'excused';
    public const STATUS_NOT_IN_CLASSROOM = 'not_in_classroom';
    public const STATUS_NO_DATA = 'no_data';
    public const STATUSES = [
        self::STATUS_PRESENT => 'Present',
        self::STATUS_ABSENT => 'Absent',
        self::STATUS_LATE => 'Late',
        self::STATUS_EXCUSED => 'Excused',
        self::STATUS_NOT_IN_CLASSROOM => 'Not in Classroom',
    ];
    public const STATUS_LABELS_EXTENDED = self::STATUSES + [
        self::STATUS_NO_DATA => 'No Data Yet',
    ];
    // 'resolved_by' values — who made this change
    public const RESOLVED_BY_TEACHER = 'teacher';
    public const RESOLVED_BY_SYSTEM = 'system';
    public function attendanceLog(): BelongsTo
    {
        return $this->belongsTo(AttendanceLog::class, 'attendance_log_id');
    }
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }
    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class, 'teaching_assignment_id');
    }
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }
}
