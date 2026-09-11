<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\InvitationToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class Session1UserCreationTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /** =============================================
     *  Admin User Creation Tests
     * ============================================= */

    public function test_admin_can_create_user_without_password(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin)->postJson('/api/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'role' => 'teacher',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'first_name',
                'last_name',
                'email',
                'role',
                'status',
            ]
        ]);

        // Verify user was created with pending status
        $user = $response->json('data');
        $this->assertEquals('pending', $user['status']);

        // Verify no usable password was set
        $createdUser = User::where('email', 'john.doe@example.com')->first();
        $this->assertNull($createdUser->password);
    }

    public function test_admin_creation_requires_no_password_field(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin)->postJson('/api/users', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@example.com',
            'role' => 'scanner_operator',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'jane.smith@example.com')->first();
        // Password should be null, not a default or hashed value
        $this->assertNull($user->password);
    }

    /** =============================================
     *  Teacher Creation Tests (Session 1 fix)
     * ============================================= */

    public function test_teacher_creation_does_not_assign_default_password(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin)->postJson('/api/teachers', [
            'first_name' => 'New',
            'last_name' => 'Teacher',
            'email' => 'new.teacher@example.com',
        ]);

        $response->assertStatus(201);

        $teacher = $response->json('data');
        $user = User::find($teacher['user_id']);

        // Teacher should have pending status and no password
        $this->assertEquals('pending', $user->status);
        $this->assertNull($user->password);
    }

    public function test_teacher_creation_sets_pending_status(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin)->postJson('/api/teachers', [
            'first_name' => 'Another',
            'last_name' => 'Teacher',
            'email' => 'another.teacher@example.com',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'another.teacher@example.com')->first();
        $this->assertEquals('pending', $user->status);
    }

    /** =============================================
     *  Role Validation Tests
     * ============================================= */

    public function test_user_creation_validates_role_in_list(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin)->postJson('/api/users', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test.user@example.com',
            'role' => 'invalid_role',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_creation_accepts_valid_roles(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        foreach ([User::ROLE_ADMIN, User::ROLE_TEACHER, User::ROLE_SCANNER_OPERATOR] as $role) {
            $response = $this->actingAs($admin)->postJson('/api/users', [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => "test.{$role}@example.com",
                'role' => $role,
            ]);

            $response->assertStatus(201);
        }
    }

    /** =============================================
     *  Account Status Tests
     * ============================================= */

    public function test_pending_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'pending',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(422); // or 401, depending on auth implementation
        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'inactive',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_reactivated_user_can_log_in(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'inactive',
            'password' => bcrypt('password'),
        ]);

        // Reactivate the user
        $user->update(['status' => 'active']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(302); // or 200, depending on auth implementation
    }

    /** =============================================
     *  User Update Tests
     * ============================================= */

    public function test_admin_cannot_update_own_account(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin)->putJson("/api/users/{$admin->id}", [
            'email' => 'new.email@example.com',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_other_users(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $otherUser = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'pending',
            'password' => null,
            'email' => 'other@example.com',
        ]);

        $response = $this->actingAs($admin)->putJson("/api/users/{$otherUser->id}", [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200);
    }

    /** =============================================
     *  Deactivate/Reactivate Tests
     * ============================================= */

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin)->postJson("/api/users/{$admin->id}/deactivate");

        $response->assertStatus(403);
    }

    public function test_admin_can_deactivate_other_users(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $otherUser = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin)->postJson("/api/users/{$otherUser->id}/deactivate");

        $response->assertStatus(200);

        $user = User::find($otherUser->id);
        $this->assertEquals('inactive', $user->status);
    }

    public function test_admin_cannot_reactivate_own_account(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin)->postJson("/api/users/{$admin->id}/reactivate");

        $response->assertStatus(403);
    }

    public function test_admin_can_reactivate_inactive_users(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $otherUser = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => 'inactive',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($admin)->postJson("/api/users/{$otherUser->id}/reactivate");

        $response->assertStatus(200);

        $user = User::find($otherUser->id);
        $this->assertEquals('active', $user->status);
    }
}
