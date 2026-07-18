<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthRoleRedirectTest extends TestCase
{
    public function test_authenticated_admin_can_view_the_login_page_without_redirecting(): void
    {
        $admin = new User([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/login');

        $response->assertStatus(200);
    }

    public function test_authenticated_teacher_can_view_the_login_page_without_redirecting(): void
    {
        $teacher = new User([
            'role' => User::ROLE_TEACHER,
            'status' => 'active',
        ]);

        $response = $this->actingAs($teacher)->get('/login');

        $response->assertStatus(200);
    }

    public function test_non_teacher_users_cannot_access_teacher_dashboard(): void
    {
        $admin = new User([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/teacher/dashboard');

        $response->assertStatus(403);
    }

    public function test_non_teacher_users_cannot_access_teacher_module_schedule_page(): void
    {
        $admin = new User([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/teacher/teaching-assignments');

        $response->assertStatus(403);
    }
}
