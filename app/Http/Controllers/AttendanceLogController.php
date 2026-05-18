<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;


class AttendanceLogController extends Controller
{


    public function index()
    {
        $attendance_logs = AttendanceLog::with([
            'enrollment.student'
        ])
            ->orderBy('created_at' , 'desc')
            ->paginate(20);

        return view(
            'admin-modules.monitoring.entry-exit',
            compact('attendance_logs')
        );

    }
}
