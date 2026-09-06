<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $email,
        public string $token,
        public ?string $name = null
    ) {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $this->email,
        ]);

        return $this->subject('Reset Your Password - Concepcion Integrated School')
            ->markdown('emails.auth.reset-password', [
                'name' => $this->name ?? 'User',
                'resetUrl' => $resetUrl,
                'expiresInMinutes' => 60,
            ]);
    }
}
