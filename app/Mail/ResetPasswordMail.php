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

        if (str_contains($resetUrl, 'localhost') || str_contains($resetUrl, '127.0.0.1')) {
            $path = parse_url($resetUrl, PHP_URL_PATH) ?? '';
            $query = parse_url($resetUrl, PHP_URL_QUERY);
            $queryString = $query ? '?' . $query : '';
            $resetUrl = 'https://cis-capstone.onrender.com' . $path . $queryString;
        }

        return $this->subject('Reset Your Password - Concepcion Integrated School')
            ->markdown('emails.auth.reset-password', [
                'name' => $this->name ?? 'User',
                'resetUrl' => $resetUrl,
                'expiresInMinutes' => 60,
            ]);
    }
}
