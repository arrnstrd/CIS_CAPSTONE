<?php

namespace Tests\Unit;

use App\Models\InvitationToken;
use App\Models\User;
use App\Services\InvitationService;
use Tests\TestCase;

class InvitationServiceTest extends TestCase
{

    protected InvitationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvitationService();
    }

    /** @test */
    public function generate_creates_invitation_with_7_day_expiration(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'pending',
            'password' => null,
        ]);

        $result = $this->service->generate($user);

        $this->assertArrayHasKey('invitation', $result);
        $this->assertArrayHasKey('plainToken', $result);
        $this->assertInstanceOf(InvitationToken::class, $result['invitation']);
        $this->assertIsString($result['plainToken']);
        $this->assertEquals(64, strlen($result['plainToken']));

        $invitation = $result['invitation'];
        $this->assertEquals($user->id, $invitation->user_id);
        $this->assertNotNull($invitation->expires_at);
        $this->assertNull($invitation->used_at);

        // Check expiration is approximately 7 days from now
        $expectedExpiry = now()->addDays(7);
        $this->assertTrue(
            $invitation->expires_at->diffInSeconds($expectedExpiry) < 5,
            'Expiration should be exactly 7 days from creation'
        );
    }

    /** @test */
    public function generate_stores_only_sha256_hash_not_plaintext(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'pending',
            'password' => null,
        ]);

        $result = $this->service->generate($user);
        $plainToken = $result['plainToken'];
        $invitation = $result['invitation'];

        // Refresh from database
        $invitation->refresh();

        // The stored token_hash should be SHA-256 of plainToken
        $expectedHash = hash('sha256', $plainToken);
        $this->assertEquals($expectedHash, $invitation->token_hash);

        // The plaintext token should NOT be stored anywhere
        $this->assertNotEquals($plainToken, $invitation->token_hash);
    }

    /** @test */
    public function validate_returns_invitation_for_valid_token(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'pending',
            'password' => null,
        ]);

        $result = $this->service->generate($user);
        $plainToken = $result['plainToken'];

        $validated = $this->service->validate($plainToken);

        $this->assertNotNull($validated);
        $this->assertEquals($result['invitation']->id, $validated->id);
        $this->assertTrue($validated->isValid());
    }

    /** @test */
    public function validate_returns_null_for_invalid_token(): void
    {
        $validated = $this->service->validate('invalid-token-that-does-not-exist');
        $this->assertNull($validated);
    }

    /** @test */
    public function validate_returns_null_for_expired_token(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'pending',
            'password' => null,
        ]);

        $result = $this->service->generate($user);
        $invitation = $result['invitation'];

        // Manually expire the invitation
        $invitation->update(['expires_at' => now()->subHour()]);

        $validated = $this->service->validate($result['plainToken']);
        $this->assertNull($validated);
    }

    /** @test */
    public function validate_returns_null_for_used_token(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'pending',
            'password' => null,
        ]);

        $result = $this->service->generate($user);
        $invitation = $result['invitation'];

        // Mark as used
        $invitation->markUsed();

        $validated = $this->service->validate($result['plainToken']);
        $this->assertNull($validated);
    }

    /** @test */
    public function invalidate_marks_invitation_as_used(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'pending',
            'password' => null,
        ]);

        $result = $this->service->generate($user);
        $invitation = $result['invitation'];

        $this->assertNull($invitation->used_at);

        $this->service->invalidate($invitation);

        $invitation->refresh();
        $this->assertNotNull($invitation->used_at);
        $this->assertFalse($invitation->isValid());
    }

    /** @test */
    public function invalidate_existing_marks_all_unused_invitations_as_used(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'pending',
            'password' => null,
        ]);

        // Create multiple invitations
        $result1 = $this->service->generate($user);
        $result2 = $this->service->generate($user); // This will invalidate the first

        $invitation1 = $result1['invitation'];
        $invitation2 = $result2['invitation'];

        $invitation1->refresh();
        $invitation2->refresh();

        // First should be invalidated
        $this->assertNotNull($invitation1->used_at);
        $this->assertFalse($invitation1->isValid());

        // Second should be valid
        $this->assertNull($invitation2->used_at);
        $this->assertTrue($invitation2->isValid());
    }

    /** @test */
    public function resend_creates_new_invitation_with_fresh_token_and_expiration(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'pending',
            'password' => null,
        ]);

        // Generate initial invitation
        $result1 = $this->service->generate($user);
        $invitation1 = $result1['invitation'];
        $plainToken1 = $result1['plainToken'];

        // Resend (generate replacement)
        $result2 = $this->service->resend($user);
        $invitation2 = $result2['invitation'];
        $plainToken2 = $result2['plainToken'];

        // Old invitation should be invalidated
        $invitation1->refresh();
        $this->assertNotNull($invitation1->used_at);
        $this->assertFalse($invitation1->isValid());

        // New invitation should be valid with different token
        $this->assertNotEquals($plainToken1, $plainToken2);
        $this->assertNull($invitation2->used_at);
        $this->assertTrue($invitation2->isValid());

        // New expiration should be fresh (approximately 7 days from resend)
        $expectedExpiry = now()->addDays(7);
        $this->assertTrue(
            $invitation2->expires_at->diffInSeconds($expectedExpiry) < 5,
            'Resend should give fresh 7-day expiration'
        );
    }

    /** @test */
    public function generate_uses_cryptographically_secure_random_token(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'pending',
            'password' => null,
        ]);

        $result1 = $this->service->generate($user);
        $result2 = $this->service->generate($user);

        // Tokens should be different (cryptographically secure random)
        $this->assertNotEquals($result1['plainToken'], $result2['plainToken']);
        $this->assertNotEquals($result1['invitation']->token_hash, $result2['invitation']->token_hash);
    }
}
