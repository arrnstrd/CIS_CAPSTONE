<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentRiskInterventionMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $interventionData;
    public ?string $teacherNote;

    /**
     * Create a new message instance.
     *
     * @param array $interventionData
     * @param string|null $teacherNote
     */
    public function __construct(array $interventionData, ?string $teacherNote = null)
    {
        $this->interventionData = $interventionData;
        $this->teacherNote = $teacherNote;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->interventionData['subject'] ?? 'Academic & Attendance Notice - Concepcion Integrated School'
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.teacher.risk-intervention',
            with: [
                'data' => $this->interventionData,
                'teacherNote' => $this->teacherNote,
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
