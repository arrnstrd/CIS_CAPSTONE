<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies the role-separation refactor for the Teachers module:
 * - School Admin cannot create teacher accounts (teachers.store is super_admin only)
 * - School Admin can access the teacher directory (teachers.index)
 * - School Admin can access a teacher's workspace (teachers.show)
 * - Super Admin retains the ability to create teacher accounts
 */
class TeacherModuleAccessTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────
    // School Admin — teacher creation is FORBIDDEN
    // ─────────────────────────────────────────────────

    public function test_school_admin_cannot_post_to_teachers_store(): void
    {
        $admin = User::factory()->create([
            'role'   => User::ROLE_ADMIN,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post('/teachers', [
            'first_name' => 'Test',
            'last_name'  => 'Teacher',
            'email'      => 'test.teacher@example.com',
        ]);

        // Middleware should block with 403
        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────
    // School Admin — directory and workspace are ALLOWED
    // ─────────────────────────────────────────────────

    public function test_school_admin_can_view_teacher_directory(): void
    {
        $admin = User::factory()->create([
            'role'   => User::ROLE_ADMIN,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/teachers');

        $response->assertStatus(200);
    }

    public function test_school_admin_can_view_teacher_workspace(): void
    {
        $admin = User::factory()->create([
            'role'   => User::ROLE_ADMIN,
            'status' => 'active',
        ]);

        // Create a teacher to look up
        $teacherUser = User::factory()->create([
            'role'   => User::ROLE_TEACHER,
            'status' => 'active',
        ]);
        $teacher = Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            ['status'  => 'active']
        );

        $response = $this->actingAs($admin)->get("/teachers/{$teacher->id}");

        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────
    // Super Admin — teacher creation is ALLOWED
    // ─────────────────────────────────────────────────

    public function test_super_admin_can_post_to_teachers_store(): void
    {
        $superAdmin = User::factory()->create([
            'role'   => User::ROLE_SUPER_ADMIN,
            'status' => 'active',
        ]);

        $response = $this->actingAs($superAdmin)->post('/teachers', [
            'first_name' => 'Super',
            'last_name'  => 'CreatedTeacher',
            'email'      => 'super.created@example.com',
        ]);

        // Should succeed (200/201/redirect) or fail validation (422),
        // but must NOT be 403 (authorization failure)
        $this->assertNotEquals(403, $response->getStatusCode(),
            'Super Admin must not receive 403 on POST /teachers');
    }
}
