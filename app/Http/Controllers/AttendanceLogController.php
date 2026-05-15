<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use Illuminate\Http\Request;

class AttendanceLogController extends Controller
{
    // public function store(Request $request){
    //     $validatedData = $request->validate([
    //         'enrollment_id' => ['required' , 'exists:enrollemts,id'],
    //         'scan_type' => ['required' , 'in:IN,OUT,RE_ENTRY,RE_EXIT'],
    //         'scan_time' => ['required' , 'date_format::Y-m-d H:i:s'],
    //         ''
    //     ]);
    // }



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
