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

            'OUT' => 'RE_ENTRY',

            'RE_ENTRY' => 'RE_EXIT',

            'RE_EXIT' => 'RE_ENTRY',

            default => 'IN',
        };
    }
}