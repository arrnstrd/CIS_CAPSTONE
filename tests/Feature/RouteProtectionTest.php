<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RouteProtectionTest extends TestCase
{
    public function test_guest_is_redirected_to_login_for_protected_admin_route(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirectContains('/login');
    }

    public function test_teacher_user_cannot_access_admin_dashboard(): void
    {
        $teacher = new User([
            'role' => User::ROLE_TEACHER,
            'status' => 'active',
        ]);

        $response = $this->actingAs($teacher)->get('/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $admin = new User([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
    }
}
