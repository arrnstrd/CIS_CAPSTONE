<?php

namespace App\Jobs;

use App\Events\EmailLogCreated;
use App\Mail\GateScanMail;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendGateScanNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $emailLogId)
    {
    }

    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(): void
    {
        $emailLog = EmailLog::with('student')->findOrFail($this->emailLogId);
        $now = now();

        $emailLog->update([
            'status' => 'pending',
            'attempt_count' => $emailLog->attempt_count + 1,
            'last_attempt_at' => $now,
        ]);

        Mail::to($emailLog->email)->send(
            new GateScanMail(
                $emailLog->student,
                $emailLog->scan_type,
                $emailLog->attendanceLog?->scan_time?->format('F d, Y - h:i A') ?? $now->format('F d, Y - h:i A')
            )
        );

        $emailLog->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        try {
            EmailLogCreated::dispatch([
                'id' => $emailLog->id,
                'student_id' => $emailLog->student_id,
                'student_name' => trim(($emailLog->student?->first_name ?? '') . ' ' . ($emailLog->student?->last_name ?? '')),
                'student_number' => $emailLog->student?->student_number,
                'email' => $emailLog->email,
                'scan_type' => $emailLog->scan_type,
                'status' => 'sent',
                'time' => now()->format('h:i A'),
                'date' => now()->format('M d, Y'),
            ]);
        } catch (Throwable $e) {
            // Ignore broadcast failure
        }
    }

    public function failed(Throwable $exception): void
    {
        EmailLog::whereKey($this->emailLogId)->update([
            'status' => 'failed',
            'last_attempt_at' => now(),
        ]);

        try {
            $emailLog = EmailLog::with('student')->find($this->emailLogId);
            if ($emailLog) {
                EmailLogCreated::dispatch([
                    'id' => $emailLog->id,
                    'student_id' => $emailLog->student_id,
                    'student_name' => trim(($emailLog->student?->first_name ?? '') . ' ' . ($emailLog->student?->last_name ?? '')),
                    'student_number' => $emailLog->student?->student_number,
                    'email' => $emailLog->email,
                    'scan_type' => $emailLog->scan_type,
                    'status' => 'failed',
                    'time' => now()->format('h:i A'),
                    'date' => now()->format('M d, Y'),
                ]);
            }
        } catch (Throwable $e) {
            // Ignore broadcast failure
        }
    }
}
