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
        $setupUrl = route('setup.show', ['token' => $this->token, 'email' => $this->email]);

        if (str_contains($setupUrl, 'localhost') || str_contains($setupUrl, '127.0.0.1')) {
            $path = parse_url($setupUrl, PHP_URL_PATH) ?? '';
            $query = parse_url($setupUrl, PHP_URL_QUERY);
            $queryString = $query ? '?' . $query : '';
            $setupUrl = 'https://cis-capstone.onrender.com' . $path . $queryString;
        }

        return $this->subject('Complete Your Account Setup')
            ->markdown('emails.auth.setup-invitation', [
                'name' => $this->name,
                'setupUrl' => $setupUrl,
            ]);
    }
}