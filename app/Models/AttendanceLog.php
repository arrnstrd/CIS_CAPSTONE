<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = [
        'enrollment_id',
        'scan_type',
        'session_type',
        'scan_time',
        'scanned_by_user_id',
        'device_id'
    ];

    protected $casts = [
        'scan_time' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(
            Enrollment::class,
            'enrollment_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'scanned_by_user_id'
        );
    }

    public function emailLog()
    {
        return $this->hasOne(EmailLog::class, 'attendance_log_id');
    }

    public function flagged_scans()
    {
        return $this->hasMany(
            FlaggedScan::class,
            'attendance_log_id'
        );
    }

    public function scopeFilterByDateRange(
        Builder $query,
        $startDate,
        $endDate = null
    ) {
        $startDate = is_string($startDate)
            ? Carbon::parse($startDate)
            : $startDate;

        $endDate = is_string($endDate)
            ? Carbon::parse($endDate)
            : ($endDate ?? $startDate);

        return $query->whereBetween('scan_time', [
            $startDate->startOfDay(),
            $endDate->endOfDay()
        ]);
    }

    public function scopeTodayOnly(Builder $query)
    {
        return $query->filterByDateRange(
            Carbon::today(),
            Carbon::today()
        );
    }

    public function scopeYesterdayOnly(Builder $query)
    {
        return $query->filterByDateRange(
            Carbon::yesterday(),
            Carbon::yesterday()
        );
    }

    public function scopeLast7Days(Builder $query)
    {
        return $query->filterByDateRange(
            Carbon::today()->subDays(6),
            Carbon::today()
        );
    }

    public function scopeThisWeekOnly(Builder $query)
    {
        return $query->filterByDateRange(
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        );
    }

    public function scopeFilterByScanType(
        Builder $query,
        $scanType
    ) {
        if (!$scanType || $scanType === 'all') {
            return $query;
        }

        return $query->where('scan_type', $scanType);
    }

    public function scopeFilterBySessionType(
        Builder $query,
        $sessionType
    ) {
        if (!$sessionType || $sessionType === 'all') {
            return $query;
        }

        return $query->where('session_type', $sessionType);
    }

    public function scopeFilterByFlagType(
        Builder $query,
        $flagType
    ) {
        if (!$flagType || $flagType === 'all') {
            return $query;
        }

        return $query->whereHas('flagged_scans', function ($q) use ($flagType) {
            $q->where('flag_type', $flagType);
        });
    }

    public function scopeWithoutExcessScanFlags(Builder $query): Builder
    {
        return $query->whereDoesntHave('flagged_scans', function (Builder $flagQuery) {
            $flagQuery->where('flag_type', 'excess_scan');
        });
    }

    public function scopeSearch(
        Builder $query,
        $search
    ) {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($searchQuery) use ($search) {
            $searchQuery->whereHas('enrollment.student', function ($studentQuery) use ($search) {
                $studentQuery->where('student_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            })->orWhereHas('flagged_scans', function ($flagQuery) use ($search) {
                $flagQuery->where('flag_type', 'like', "%{$search}%");
            });
        });
    }

    public function scopeGetStatistics(Builder $query)
    {
        return [
            'TOTAL' => (clone $query)->count(),

            'IN' => (clone $query)
                ->where('scan_type', 'IN')
                ->count(),

            'OUT' => (clone $query)
                ->where('scan_type', 'OUT')
                ->count(),

            'FLAGGED' => (clone $query)
                ->whereHas('flagged_scans')
                ->count(),
        ];
    }
}
