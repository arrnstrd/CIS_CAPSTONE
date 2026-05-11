<?php

namespace App\Services;

use App\Models\ScheduleConfig;

class ScheduleResolver
{
    public function resolve(string $level, string $time): ?ScheduleConfig
    {
        // Normalize time format (IMPORTANT)
        $time = date('H:i', strtotime($time));

        // Get all schedules for level
        $schedules = ScheduleConfig::where('level', $level)->get();

        if ($schedules->isEmpty()) {
            return null;
        }

        // Find matching schedule
        return $schedules->first(function ($schedule) use ($time) {

            $inStart = date('H:i', strtotime($schedule->in_start));
            $outEnd  = date('H:i', strtotime($schedule->out_end));

            return $time >= $inStart && $time <= $outEnd;
        });
    }
}