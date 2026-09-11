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

        // Failsafe: Ensure setup links never output localhost/127.0.0.1 in emails
        if (str_contains($setupLink, 'localhost') || str_contains($setupLink, '127.0.0.1')) {
            $path = parse_url($setupLink, PHP_URL_PATH) ?? '';
            $query = parse_url($setupLink, PHP_URL_QUERY);
            $queryString = $query ? '?' . $query : '';
            $setupLink = 'https://cis-capstone.onrender.com' . $path . $queryString;
        }

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
            view: 'emails.auth.invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
