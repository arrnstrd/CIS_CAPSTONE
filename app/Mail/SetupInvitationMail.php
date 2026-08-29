<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SetupInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $email,
        public string $token,
        public string $name
    ) {
        //
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Complete Your Account Setup')
            ->markdown('emails.setup-invitation', [
                'name' => $this->name,
                'setupUrl' => route('setup.show', ['token' => $this->token, 'email' => $this->email]),
            ]);
    }
}