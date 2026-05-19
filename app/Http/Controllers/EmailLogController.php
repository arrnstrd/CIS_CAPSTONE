<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailLogController extends Controller
{
    public function index()
    {
        // $logs = EmailLog::latest()->get();
        // return response()->json($logs);

        $emailLogs = EmailLog::with('student')
            ->orderBy('student_id')
            ->paginate(25);
            return view('admin-modules.monitoring.emails', compact('emailLogs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'email' => ['required', 'string'],
            'scan_type' => ['required', 'in:IN,OUT,RE_ENTRY,RE_EXIT'],
            'status' => ['required', 'in:pending,sent,failed']
        ]);

        $emailLog = EmailLog::create($data);

        return response()->json([
            'message' => 'Email log created successfully',
            'data' => $emailLog
        ]);
    }


    //email logic
    private function resendEmail($emailLog)
    {
        try {
            Mail::raw(
                "Student ID {$emailLog->student_id} gate attendance recorded at " . now(),
                function ($message) use ($emailLog) {
                    $message->to($emailLog->email)
                        ->subject('CIS Gate Scan Notification');
                }
            );

            $emailLog->update([
                'status' => 'sent',
                'attempt_count' => ($emailLog->attempt_count ?? 0) + 1
            ]);

            return true;

        } catch (\Exception $e) {

            $emailLog->update([
                'status' => 'failed',
                'attempt_count' => ($emailLog->attempt_count ?? 0) + 1
            ]);

            return false;
        }
    }

    public function retry($id)
    {
        $emailLog = EmailLog::find($id);

        if (!$emailLog) {
            return response()->json([
                'message' => 'Email log not found'
            ], 404);
        }

        $this->resendEmail($emailLog);

        return response()->json([
            'message' => 'Retry attempted',
            'data' => $emailLog
        ]);
    }

    public function retryAll()
    {
        $failedLogs = EmailLog::where('status', 'failed')->get();

        $success = 0;
        $failed = 0;

        foreach ($failedLogs as $log) {

            // limit retries
            if (($log->attempt_count ?? 0) >= 4) {
                continue;
            }

            $result = $this->resendEmail($log);

            if ($result) {
                $success++;
            } else {
                $failed++;
            }
        }

        return response()->json([
            'message' => 'Retry process completed',
            'success_count' => $success,
            'failed_count' => $failed
        ]);
    }
}