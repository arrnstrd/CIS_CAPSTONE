<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthRoleRedirectTest extends TestCase
{

    public function test_authenticated_admin_can_view_the_login_page_without_redirecting(): void
    {
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.test',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/login');

        $response->assertStatus(200);
    }

    public function test_authenticated_teacher_can_view_the_login_page_without_redirecting(): void
    {
        $teacher = User::create([
            'first_name' => 'Teacher',
            'last_name' => 'User',
            'email' => 'teacher@example.test',
            'password' => bcrypt('password'),
            'role' => User::ROLE_TEACHER,
            'status' => 'active',
        ]);

        $response = $this->actingAs($teacher)->get('/login');

        $response->assertStatus(200);
    }

    public function test_non_teacher_users_cannot_access_teacher_dashboard(): void
    {
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin2@example.test',
            'password' => bcrypt('Password123!'),
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/teacher/dashboard');

        // Kung pinapayagan ng system ang admin dito, dapat 403. 
        // Kung nag-fail pa rin ito at nag-200, tingnan ang middleware sa routes/web.php mo.
        $response->assertStatus(403);
    }

    public function test_non_teacher_users_cannot_access_teacher_module_schedule_page(): void
    {
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin3@example.test',
            'password' => bcrypt('Password123!!'),
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
        ]);

        // Siguraduhin na ang URL na ito ay tugma sa iyong routes/web.php
        $response = $this->actingAs($admin)->get('/teacher/teaching-assignments');

        $response->assertStatus(403);
    }
}