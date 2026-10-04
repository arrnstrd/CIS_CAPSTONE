<?php

namespace Tests\Feature;

use App\Events\AttendanceRecorded;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

class PhaseTwoRealtimeTest extends TestCase
{

    public function test_attendance_event_has_a_lightweight_private_payload(): void
    {
        $event = new AttendanceRecorded([
            'attendance_log_id' => 10,
            'enrollment_id' => 20,
            'student' => [
                'id' => 30,
                'name' => 'Test Student',
                'student_number' => 'STU-001',
                'grade' => '7',
                'section' => 'A',
            ],
            'scan_type' => 'IN',
            'scan_time' => '2026-09-06T08:30:00+08:00',
            'late' => false,
            'flags' => [],
        ]);

        $this->assertTrue($event->afterCommit);
        $this->assertSame('private-attendance.monitoring', $event->broadcastOn()[0]->name);
        $this->assertSame('AttendanceRecorded', $event->broadcastAs());
        $this->assertArrayNotHasKey('counters', $event->broadcastWith());
        $this->assertArrayNotHasKey('guardian', $event->broadcastWith());
    }

    public function test_attendance_event_dispatches_after_transaction_commit(): void
    {
        Event::fake();
        $event = new AttendanceRecorded(['attendance_log_id' => 10]);

        DB::transaction(function () use ($event): void {
            DB::afterCommit(fn () => AttendanceRecorded::dispatch($event->broadcastWith()));
            Event::assertNotDispatched(AttendanceRecorded::class);
        });

        Event::assertDispatched(AttendanceRecorded::class);
    }

    public function test_only_admin_and_scanner_operator_can_authorize_private_channel(): void
    {
        $callback = Broadcast::getChannels()->get('attendance.monitoring');

        foreach ([User::ROLE_ADMIN, User::ROLE_SCANNER_OPERATOR] as $role) {
            $this->assertTrue($callback(User::factory()->create(['role' => $role])));
        }

        foreach ([User::ROLE_TEACHER, User::ROLE_SUPER_ADMIN] as $role) {
            $this->assertFalse($callback(User::factory()->create(['role' => $role])));
        }
    }

    public function test_unauthenticated_users_cannot_authorize_private_channel(): void
    {
        config(['broadcasting.default' => 'reverb']);

        $this->postJson('/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-attendance.monitoring',
        ])->assertForbidden();
    }

    public function test_resync_endpoint_is_role_protected_and_returns_authoritative_shape(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]))
            ->getJson('/school-admin/attendance/monitoring/resync')
            ->assertOk()
            ->assertJsonStructure([
                'overview' => ['total', 'present', 'in', 'out', 'late', 'flagged'],
                'recent_logs',
                'scan_buckets',
                'weekly_buckets',
            ]);

        $this->actingAs(User::factory()->create(['role' => User::ROLE_TEACHER]))
            ->getJson('/school-admin/attendance/monitoring/resync')
            ->assertForbidden();
    }
}
