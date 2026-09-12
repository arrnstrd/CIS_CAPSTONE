<?php

namespace App\Mail\Transport;

use Exception;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class ResendApiTransport extends AbstractTransport
{
    protected string $key;

    public function __construct(string $key)
    {
        parent::__construct();
        $this->key = $key;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $payload = [
            'from' => $email->getFrom()[0]->toString(),
            'to' => array_map(fn($addr) => $addr->getAddress(), $email->getTo()),
            'subject' => $email->getSubject(),
        ];

        if ($email->getHtmlBody()) {
            $payload['html'] = $email->getHtmlBody();
        }

        if ($email->getTextBody()) {
            $payload['text'] = $email->getTextBody();
        }

        if ($email->getCc()) {
            $payload['cc'] = array_map(fn($addr) => $addr->getAddress(), $email->getCc());
        }

        if ($email->getBcc()) {
            $payload['bcc'] = array_map(fn($addr) => $addr->getAddress(), $email->getBcc());
        }

        if ($email->getReplyTo()) {
            $payload['reply_to'] = array_map(fn($addr) => $addr->getAddress(), $email->getReplyTo());
        }

        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $attachments[] = [
                'filename' => $attachment->getFilename(),
                'content' => base64_encode($attachment->getBody()),
            ];
        }
        if (!empty($attachments)) {
            $payload['attachments'] = $attachments;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->key,
            'Content-Type' => 'application/json',
        ])->post('https://api.resend.com/emails', $payload);

        if (!$response->successful()) {
            $error = $response->json('message') ?? $response->body();
            throw new Exception('Resend API error: ' . $error);
        }
    }

    public function __toString(): string
    {
        return 'resend-api';
    }
}
