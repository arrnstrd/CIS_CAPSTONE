<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GateScanMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $scanType;
    public $time;

    public function __construct($student, $scanType, $time)
    {
        $this->student = $student;
        $this->scanType = $scanType;
        $this->time = $time;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Gate Scan Notification - {$this->student->first_name}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.attendance.gate-scan',
            with: [
                'student' => $this->student,
                'scanType' => $this->scanType,
                'time' => $this->time,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}