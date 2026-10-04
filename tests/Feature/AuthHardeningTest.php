<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class AuthHardeningTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_login_rejects_weak_passwords_before_authentication(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'weakpass',
        ]);

        $response->assertSessionHas('error');
        $response->assertRedirect();
    }

    public function test_login_throttles_repeated_failed_attempts(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }
}
