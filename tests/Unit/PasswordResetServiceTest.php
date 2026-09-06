<?php

namespace Tests\Unit;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use App\Services\Administration\PasswordResetService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\CreatesApplication;

class PasswordResetServiceTest extends BaseTestCase
{
    /**
     * Creates the application.
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    public function test_reset_password_mail_properties(): void
    {
        $mail = new ResetPasswordMail('test@cis.edu.ph', 'sample-token', 'Jane Doe');
        $this->assertEquals('test@cis.edu.ph', $mail->email);
        $this->assertEquals('sample-token', $mail->token);
        $this->assertEquals('Jane Doe', $mail->name);
    }

    public function test_validate_token_rejects_empty_inputs(): void
    {
        $service = new PasswordResetService();

        $this->assertFalse($service->validateToken('', 'test@cis.edu.ph'));
        $this->assertFalse($service->validateToken('token123', ''));
        $this->assertFalse($service->validateToken(null, null));
    }

    public function test_validate_token_returns_false_when_no_record_found(): void
    {
        DB::shouldReceive('table')
            ->once()
            ->with('password_reset_tokens')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->once()
            ->with('email', 'unknown@cis.edu.ph')
            ->andReturnSelf();

        DB::shouldReceive('first')
            ->once()
            ->andReturnNull();

        $service = new PasswordResetService();
        $this->assertFalse($service->validateToken('any-token', 'unknown@cis.edu.ph'));
    }

    public function test_validate_token_returns_false_when_expired(): void
    {
        $expiredRecord = (object) [
            'email' => 'user@cis.edu.ph',
            'token' => 'hashed-token',
            'created_at' => Carbon::now()->subMinutes(65)->toDateTimeString(),
        ];

        DB::shouldReceive('table')
            ->once()
            ->with('password_reset_tokens')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->once()
            ->with('email', 'user@cis.edu.ph')
            ->andReturnSelf();

        DB::shouldReceive('first')
            ->once()
            ->andReturn($expiredRecord);

        $service = new PasswordResetService();
        $this->assertFalse($service->validateToken('raw-token', 'user@cis.edu.ph'));
    }

    public function test_validate_token_returns_true_when_valid_and_within_window(): void
    {
        $validRecord = (object) [
            'email' => 'user@cis.edu.ph',
            'token' => 'hashed-token',
            'created_at' => Carbon::now()->subMinutes(15)->toDateTimeString(),
        ];

        DB::shouldReceive('table')
            ->once()
            ->with('password_reset_tokens')
            ->andReturnSelf();

        DB::shouldReceive('where')
            ->once()
            ->with('email', 'user@cis.edu.ph')
            ->andReturnSelf();

        DB::shouldReceive('first')
            ->once()
            ->andReturn($validRecord);

        Hash::shouldReceive('check')
            ->once()
            ->with('correct-token', 'hashed-token')
            ->andReturnTrue();

        $service = new PasswordResetService();
        $this->assertTrue($service->validateToken('correct-token', 'user@cis.edu.ph'));
    }

    public function test_account_enumeration_defense_silently_handles_missing_user(): void
    {
        // For a non-existent email, sendResetLink must not throw or send an email
        Mail::fake();

        $service = new PasswordResetService();
        $service->sendResetLink('nonexistent@cis.edu.ph');

        Mail::assertNothingSent();
    }
}
