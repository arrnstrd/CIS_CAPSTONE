<?php

namespace Tests\Unit;

use App\Http\Requests\Administration\Authentication\LoginRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LoginRequestValidationTest extends TestCase
{
    public function test_login_request_allows_simple_passwords_for_existing_users(): void
    {
        $validator = Validator::make([
            'email' => 'admin@example.com',
            'password' => 'WeakPass1',
        ], (new LoginRequest())->rules());

        $this->assertFalse($validator->fails());
        $this->assertSame([], $validator->errors()->messages());
    }
}
