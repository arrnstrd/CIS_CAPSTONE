<?php

namespace App\Services;

use App\Models\ScheduleConfig;

class ScheduleResolver
{
    public function resolve(
        string $level,
        string $sessionType,
        string $time
    ): ?ScheduleConfig {

        $level = $this->normalizeLevel($level);

        // Normalize current time
        $time = date('H:i', strtotime($time));

        // Resolve schedule by level and session
        $schedule = ScheduleConfig::where('level', $level)
            ->where('session_type', $sessionType)
            ->first();

        if (!$schedule) {
            return null;
        }

        // Normalize schedule boundaries
        $inStart = date('H:i', strtotime($schedule->in_start));
        $outEnd  = date('H:i', strtotime($schedule->out_end));

        // Validate active schedule window
        if ($time < $inStart || $time > $outEnd) {
            return null;
        }

        return $schedule;
    }

    private function normalizeLevel(string $level): string
    {
        return match ($level) {
            'highschool' => 'hs',
            'senior_high_school' => 'shs',
            default => $level,
        };
    }
}