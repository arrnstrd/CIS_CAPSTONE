<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use App\Services\Administration\PasswordResetService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordTest extends BaseTestCase
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

    public function test_guest_can_view_forgot_password_page(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('Forgot Password?');
        $response->assertSee('Send Reset Link');
        $response->assertSee(route('login'));
    }

    public function test_request_reset_link_defends_against_account_enumeration(): void
    {
        Mail::fake();

        $response = $this->from('/forgot-password')->post('/forgot-password', [
            'email' => 'random_unregistered_email_' . Str::random(8) . '@cis.edu.ph',
        ]);

        // Generic response must be returned
        $response->assertRedirect('/forgot-password');
        $response->assertSessionHas(
            'status',
            'If an account exists with this email address, you will receive an email with instructions to reset your password.'
        );

        Mail::assertNothingSent();
    }

    public function test_reset_password_page_shows_invalid_state_for_invalid_token(): void
    {
        $response = $this->get('/reset-password?token=invalid-dummy-token&email=test@cis.edu.ph');

        $response->assertStatus(200);
        $response->assertSee('Link Invalid or Expired');
        $response->assertSee('Request New Reset Link');
        $response->assertDontSee('Set New Password');
    }

    public function test_forgot_password_submission_is_rate_limited(): void
    {
        $testIp = '198.51.100.' . rand(1, 250);

        for ($i = 0; $i < 5; $i++) {
            $response = $this->withServerVariables(['REMOTE_ADDR' => $testIp])
                ->post('/forgot-password', ['email' => 'rate_test_' . $testIp . '@cis.edu.ph']);
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        // 6th attempt must be throttled
        $response = $this->withServerVariables(['REMOTE_ADDR' => $testIp])
            ->post('/forgot-password', ['email' => 'rate_test_' . $testIp . '@cis.edu.ph']);
        $response->assertStatus(429);
    }
}
