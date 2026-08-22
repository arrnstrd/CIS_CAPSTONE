<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $recipientName;
    public $setupLink;
    public $expiresAt;

    public function __construct(string $recipientName, string $setupLink, string $expiresAt)
    {
        $this->recipientName = $recipientName;
        $this->setupLink = $setupLink;
        $this->expiresAt = $expiresAt;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invitation - Complete Your Account Setup"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
