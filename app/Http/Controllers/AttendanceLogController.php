<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use Illuminate\Http\Request;

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
            'adminModules.monitoring.entry_exit_monitoring',
            compact('attendance_logs')
        );

    }
}
