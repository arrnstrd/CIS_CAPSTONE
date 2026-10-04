<?php

namespace App\Jobs;

use App\Mail\InvitationMail;
use App\Models\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvitationEmail
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $setupLink,
        public string $expiresAt
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->user->email)->send(new InvitationMail(
                $this->user->first_name . ' ' . $this->user->last_name,
                $this->setupLink,
                $this->expiresAt
            ));
        } catch (Exception $e) {
            Log::error('Failed to send invitation email: ' . $e->getMessage(), [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
            ]);
        }
    }
}
