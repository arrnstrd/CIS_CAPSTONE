<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminInOutInterventionMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $interventionData;
    public string $recipientType; // 'advisor' or 'parent'
    public ?string $adminNotes;

    /**
     * Create a new message instance.
     *
     * @param array $interventionData
     * @param string $recipientType
     * @param string|null $adminNotes
     */
    public function __construct(array $interventionData, string $recipientType = 'parent', ?string $adminNotes = null)
    {
        $this->interventionData = $interventionData;
        $this->recipientType = $recipientType;
        $this->adminNotes = $adminNotes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->interventionData['subject'] ?? (
            $this->recipientType === 'advisor'
                ? 'IN/OUT Attendance Notice - ' . ($this->interventionData['student_name'] ?? 'Student')
                : 'Concepcion Integrated School - Student Attendance Advisory'
        );

        return new Envelope(
            subject: $subject
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.attendance.in-out-intervention',
            with: [
                'data' => $this->interventionData,
                'recipientType' => $this->recipientType,
                'adminNotes' => $this->adminNotes,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
