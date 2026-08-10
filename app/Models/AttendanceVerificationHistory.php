<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AttendanceVerification;

class AttendanceVerificationHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendance_verification_histories';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'attendance_verification_id',
        'previous_status',
        'new_status',
        'changed_by',
        'remarks',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function attendanceVerification(): BelongsTo
    {
        return $this->belongsTo(AttendanceVerification::class, 'attendance_verification_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
