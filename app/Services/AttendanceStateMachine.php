<?php

namespace App\Services;

use App\Models\AttendanceLog;

class AttendanceStateMachine
{
    public function nextState(?AttendanceLog $lastLog): string
    {
        if (!$lastLog) {
            return 'IN';
        }

        return match ($lastLog->scan_type) {

            'IN' => 'OUT',

            'OUT' => 'IN',

            default => 'IN',
        };
    }
}
