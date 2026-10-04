<?php

namespace Tests\Feature;

use App\Jobs\SendGateScanNotification;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\QrAttendance;
use App\Models\ScheduleConfig;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PhaseOneAttendanceTest extends TestCase
{

    public function test_teacher_cannot_access_school_admin_routes(): void
    {
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
        ]);

        $this->actingAs($teacher)
            ->get('/school-admin/qr-station')
            ->assertForbidden();
    }

    public function test_scanner_operator_routes_remain_isolated(): void
    {
        $scanner = User::factory()->create([
            'role' => User::ROLE_SCANNER_OPERATOR,
        ]);

        $this->actingAs($scanner)
            ->get('/student-management')
            ->assertForbidden();
    }

    public function test_scan_route_has_dedicated_throttle(): void
    {
        $middleware = Route::getRoutes()
            ->getByName('qr-station.scan')
            ->gatherMiddleware();

        $this->assertContains('throttle:qr-scan', $middleware);
    }

    public function test_scan_uses_active_school_year_and_queues_guardian_notification(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-06 08:30:00', 'Asia/Manila'));
        Queue::fake();

        $fixture = $this->createScanFixture();

        $response = $this->actingAs(User::factory()->create([
            'role' => User::ROLE_SCANNER_OPERATOR,
        ]))->postJson('/qr-station/scan', [
            'code' => $fixture['code'],
            'device_id' => 'station-1',
        ]);

        $response->assertOk();
        $response->assertJsonPath('attendance_log.scan_type', 'IN');
        $this->assertDatabaseHas('attendance_logs', [
            'enrollment_id' => $fixture['activeEnrollment']->id,
            'scan_type' => 'IN',
        ]);
        $this->assertDatabaseMissing('attendance_logs', [
            'enrollment_id' => $fixture['wrongEnrollment']->id,
        ]);
        $this->assertDatabaseHas('qr_attendances', [
            'enrollment_id' => $fixture['activeEnrollment']->id,
            'attendance_date' => '2026-09-06',
        ]);
        Queue::assertPushed(SendGateScanNotification::class);
    }

    public function test_scan_transitions_from_in_to_out_within_dismissal_window(): void
    {
        Queue::fake();
        $fixture = $this->createScanFixture();
        $user = User::factory()->create(['role' => User::ROLE_SCANNER_OPERATOR]);

        Carbon::setTestNow(Carbon::parse('2026-09-06 08:30:00', 'Asia/Manila'));
        $this->actingAs($user)->postJson('/qr-station/scan', ['code' => $fixture['code']])->assertOk();

        Carbon::setTestNow(Carbon::parse('2026-09-06 15:30:00', 'Asia/Manila'));
        $this->actingAs($user)
            ->postJson('/qr-station/scan', ['code' => $fixture['code']])
            ->assertOk()
            ->assertJsonPath('attendance_log.scan_type', 'OUT');

        $this->assertDatabaseCount('attendance_logs', 2);
        $this->assertDatabaseHas('qr_attendances', [
            'enrollment_id' => $fixture['activeEnrollment']->id,
            'time_out_log_id' => $this->app['db']->table('attendance_logs')->where('scan_type', 'OUT')->value('id'),
        ]);
    }

    public function test_scan_timing_boundaries_preserve_on_time_and_late_rules(): void
    {
        Queue::fake();
        $fixture = $this->createScanFixture();
        $user = User::factory()->create(['role' => User::ROLE_SCANNER_OPERATOR]);

        Carbon::setTestNow(Carbon::parse('2026-09-06 08:00:00', 'Asia/Manila'));
        $this->actingAs($user)->postJson('/qr-station/scan', ['code' => $fixture['code']])
            ->assertOk()
            ->assertJsonPath('late', false);

        $secondFixture = $this->createScanFixture('QR-TEST-002', 'STU-TEST-002');
        Carbon::setTestNow(Carbon::parse('2026-09-06 08:01:00', 'Asia/Manila'));
        $this->actingAs($user)->postJson('/qr-station/scan', ['code' => $secondFixture['code']])
            ->assertOk()
            ->assertJsonPath('late', true);

        $this->assertDatabaseHas('flagged_scans', ['flag_type' => 'late_arrival']);
    }

    public function test_scan_cooldown_blocks_repeated_scan_and_records_excess_flag(): void
    {
        Queue::fake();
        $fixture = $this->createScanFixture();
        $user = User::factory()->create(['role' => User::ROLE_SCANNER_OPERATOR]);
        Carbon::setTestNow(Carbon::parse('2026-09-06 08:30:00', 'Asia/Manila'));

        $this->actingAs($user)->postJson('/qr-station/scan', ['code' => $fixture['code']])->assertOk();
        $this->actingAs($user)
            ->postJson('/qr-station/scan', ['code' => $fixture['code']])
            ->assertStatus(429);

        $this->assertDatabaseHas('flagged_scans', ['flag_type' => 'excess_scan']);
    }

    public function test_scan_uses_a_postgres_row_lock_for_daily_attendance_state(): void
    {
        Queue::fake();
        $fixture = $this->createScanFixture();
        $user = User::factory()->create(['role' => User::ROLE_SCANNER_OPERATOR]);
        $queries = [];

        $this->app['db']->listen(function ($query) use (&$queries): void {
            if (str_contains(strtolower($query->sql), 'qr_attendances')) {
                $queries[] = strtolower($query->sql);
            }
        });

        Carbon::setTestNow(Carbon::parse('2026-09-06 08:30:00', 'Asia/Manila'));
        $this->actingAs($user)->postJson('/qr-station/scan', ['code' => $fixture['code']])->assertOk();

        $this->assertTrue(collect($queries)->contains(fn (string $sql): bool => str_contains($sql, 'for update')));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function createScanFixture(string $code = 'QR-TEST-001', string $studentNumber = 'STU-TEST-001'): array
    {
        $activeYear = SchoolYear::firstOrCreate(['school_year' => '2026-2027'], ['is_active' => true]);
        $inactiveYear = SchoolYear::firstOrCreate(['school_year' => '2025-2026'], ['is_active' => false]);
        $section = Section::firstOrCreate([
            'name' => 'A',
            'level' => 'highschool',
            'grade_level' => 7,
            'session_type' => 'morning',
        ], ['status' => 'active']);
        $student = Student::create([
            'lrn' => str_pad((string) Student::count(), 12, '0', STR_PAD_LEFT),
            'first_name' => 'Test',
            'last_name' => 'Student',
            'sex' => 'female',
            'address' => 'Test address',
            'status' => 'active',
            'student_number' => $studentNumber,
        ]);
        Guardian::create([
            'student_id' => $student->id,
            'name' => 'Parent',
            'relationship' => 'guardian',
            'email' => 'parent@example.test',
        ]);
        $wrongEnrollment = Enrollment::create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'school_year_id' => $inactiveYear->id,
            'grade_level' => '7',
            'status' => 'active',
        ]);
        $activeEnrollment = Enrollment::create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'school_year_id' => $activeYear->id,
            'grade_level' => '7',
            'status' => 'active',
        ]);
        $student->qrCode()->create(['code' => $code, 'is_active' => true]);
        ScheduleConfig::firstOrCreate([
            'level' => 'hs',
            'session_type' => 'morning',
        ], [
            'in_start' => '07:00',
            'in_end' => '09:00',
            'late_threshold' => '08:00',
            'out_start' => '15:00',
            'out_end' => '17:00',
        ]);

        return compact('code', 'wrongEnrollment', 'activeEnrollment');
    }
}
