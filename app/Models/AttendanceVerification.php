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

    public function histories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttendanceVerificationHistory::class, 'attendance_verification_id')->orderBy('created_at', 'asc');
    }

    // 1-hour grace period for 'not_in_classroom' status before auto-transitioning to 'absent'
    public const NOT_IN_CLASSROOM_GRACE_MINUTES = 60;

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Check and expire 'not_in_classroom' records that have exceeded the 1-hour grace period.
     * Automatically transitions them to 'absent', stamps the audit history, and returns count.
     */
    public static function expireNotInClassroomRecords(?int $sectionId = null, ?\Carbon\Carbon $attendanceDate = null): int
    {
        $cutoff = now()->subMinutes(self::NOT_IN_CLASSROOM_GRACE_MINUTES);
        $todayStr = now()->toDateString();

        $query = self::where('status', self::STATUS_NOT_IN_CLASSROOM)
            ->where(function ($q) use ($cutoff, $todayStr) {
                $q->where(function ($sub) use ($cutoff) {
                    $sub->whereNotNull('verified_at')->where('verified_at', '<=', $cutoff)
                        ->orWhere(function ($s2) use ($cutoff) {
                            $s2->whereNull('verified_at')->where('updated_at', '<=', $cutoff);
                        });
                })->orWhereDate('attendance_date', '<', $todayStr);
            });

        if ($sectionId) {
            $query->whereHas('enrollment', function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId);
            });
        }

        if ($attendanceDate) {
            $query->whereDate('attendance_date', $attendanceDate->toDateString());
        }

        $expired = $query->get();
        $count = 0;

        foreach ($expired as $verification) {
            $previousStatus = $verification->status;

            $verification->status = self::STATUS_ABSENT;
            $verification->resolved_by = self::RESOLVED_BY_SYSTEM;
            $verification->remarks = 'Grace period expired (1 hour exceeded without room verification). Automatically transitioned from Not in Classroom to Absent.';
            $verification->verified_at = now();
            $verification->save();

            AttendanceVerificationHistory::create([
                'attendance_verification_id' => $verification->id,
                'previous_status' => $previousStatus,
                'new_status' => self::STATUS_ABSENT,
                'changed_by' => null, // Automated System Action
                'remarks' => 'Grace period expired (1 hour exceeded without room verification). Automatically transitioned from Not in Classroom to Absent.',
                'created_at' => now(),
            ]);

            $count++;
        }

        return $count;
    }
}

