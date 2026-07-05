<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Student;
use App\Models\AttendanceLog;
use Carbon\Carbon;


class EmailLog extends Model
{
    //

    protected $fillable = [
        'attendance_log_id',
        'student_id',
        'email',
        'scan_type',
        'status',
        'attempt_count',
        'last_attempt_at',
        'sent_at'

    ];


    protected $casts = [
        'last_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class , 'student_id');
    }

    public function attendanceLog()
    {
        return $this->belongsTo(AttendanceLog::class , 'attendance_log_id');
    }

    /**
     * Scope: Filter logs by date range
     * @param Builder $query
     * @param Carbon|string $startDate
     * @param Carbon|string $endDate
     * @return Builder
     */
    public function scopeFilterByDateRange(Builder $query, $startDate, $endDate = null)
    {
        $startDate = is_string($startDate) ? Carbon::parse($startDate) : $startDate;
        $endDate = is_string($endDate) ? Carbon::parse($endDate) : ($endDate ?? $startDate);

        return $query->whereBetween('last_attempt_at', [
            $startDate->startOfDay(),
            $endDate->endOfDay()
        ]);
    }

    /**
     * Scope: Filter logs for today only
     * @param Builder $query
     * @return Builder
     */
    public function scopeTodayOnly(Builder $query)
    {
        return $query->filterByDateRange(Carbon::today(), Carbon::today());
    }

    /**
     * Scope: Filter logs for yesterday only
     * @param Builder $query
     * @return Builder
     */
    public function scopeYesterdayOnly(Builder $query)
    {
        $yesterday = Carbon::yesterday();
        return $query->filterByDateRange($yesterday, $yesterday);
    }

    /**
     * Scope: Filter logs for last 7 days
     * @param Builder $query
     * @return Builder
     */
    public function scopeLast7Days(Builder $query)
    {
        $startDate = Carbon::today()->subDays(6);
        return $query->filterByDateRange($startDate, Carbon::today());
    }

    public function scopeThisWeekOnly(Builder $query)
    {
        return $query->filterByDateRange(
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        );
    }

    /**
     * Scope: Filter logs by status
     * @param Builder $query
     * @param string|array $status
     * @return Builder
     */
    public function scopeFilterByStatus(Builder $query, $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }

        return $query->where('status', $status);
    }

    /**
     * Scope: Get grouped statistics for filtered logs
     * @param Builder $query
     * @return array
     */
    public function scopeGetStatistics(Builder $query)
    {
        return [
            'total' => $query->count(),
            'sent' => (clone $query)->where('status', 'sent')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
        ];
    }
}
