<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PrivilegedAccessControlTest extends TestCase
{

    public function test_teacher_users_cannot_manage_users_via_the_api(): void
    {
        $teacher = new User([
            'role' => User::ROLE_TEACHER,
            'status' => 'active',
        ]);

        $response = $this->actingAs($teacher)->postJson('/api/users', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'newuser@example.com',
            'password' => 'TestPass123!@#',
            'password_confirmation' => 'TestPass123!@#',
            'role' => 'teacher',
            'status' => 'active',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_users_must_confirm_their_password_for_sensitive_user_management(): void
    {
        $admin = new User([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => Hash::make('AdminPass123!'),
        ]);

        $response = $this->actingAs($admin)->postJson('/api/users', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'newuser2@example.com',
            'password' => 'TestPass123!@#',
            'password_confirmation' => 'TestPass123!@#',
            'role' => 'teacher',
            'status' => 'active',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['current_password']);
    }
}
