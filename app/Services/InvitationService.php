<?php

namespace App\Services;

use App\Models\InvitationToken;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvitationService
{
    /**
     * Generate a new invitation for the given user.
     *
     * Creates a cryptographically secure token, stores only its SHA-256 hash,
     * and returns both the InvitationToken model and the plaintext token
     * (which exists only in memory for email delivery).
     *
     * @return array{invitation: InvitationToken, plainToken: string}
     */
    public function generate(User $user): array
    {
        return DB::transaction(function () use ($user) {
            // Invalidate any existing unused invitations for this user
            $this->invalidateExisting($user);

            // Generate cryptographically secure random token (256 bits)
            $plainToken = Str::random(64);

            // Store only the SHA-256 hash
            $tokenHash = hash('sha256', $plainToken);

            // Exactly 72 hours from now
            $expiresAt = now()->addHours(72);

            $invitation = InvitationToken::create([
                'user_id' => $user->id,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
                'used_at' => null,
            ]);

            return [
                'invitation' => $invitation,
                'plainToken' => $plainToken,
            ];
        });
    }

    /**
     * Validate an invitation token.
     *
     * Hashes the provided plaintext token and looks up the digest.
     * Returns the InvitationToken if valid, null otherwise.
     */
    public function validate(string $plainToken): ?InvitationToken
    {
        $tokenHash = hash('sha256', $plainToken);

        $invitation = InvitationToken::where('token_hash', $tokenHash)->first();

        if (! $invitation) {
            return null;
        }

        if (! $invitation->isValid()) {
            return null;
        }

        return $invitation;
    }

    /**
     * Invalidate (mark as used) an invitation token.
     */
    public function invalidate(InvitationToken $invitation): void
    {
        $invitation->markUsed();
    }

    /**
     * Invalidate all existing unused invitations for a user.
     * Used when generating a new invitation (e.g., resend).
     */
    public function invalidateExisting(User $user): void
    {
        InvitationToken::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);
    }

    /**
     * Generate a replacement invitation for resend.
     *
     * Invalidates the previous invitation and creates a completely new one
     * with a fresh token and fresh 72-hour expiration.
     *
     * @return array{invitation: InvitationToken, plainToken: string}
     */
    public function resend(User $user): array
    {
        return $this->generate($user);
    }
}
