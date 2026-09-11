<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class InvitationToken extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'token_hash',
        'expires_at',
        'used_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the invitation token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the invitation token is valid (not used and not expired).
     */
    public function isValid(): bool
    {
        return !$this->used_at && $this->expires_at->isFuture();
    }

    /**
     * Mark the invitation token as used.
     */
    public function markUsed(): void
    {
        $this->update([
            'used_at' => now(),
        ]);
    }

    /**
     * Check if the invitation token has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
