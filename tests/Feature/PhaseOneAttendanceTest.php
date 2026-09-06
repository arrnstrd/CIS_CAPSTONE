<?php

namespace Tests\Feature;

use App\Jobs\SendGateScanNotification;
use App\Models\Enrollment;
use App\Models\Guardian;
use App\Models\ScheduleConfig;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PhaseOneAttendanceTest extends TestCase
{
    use RefreshDatabase;

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

        $activeYear = SchoolYear::create([
            'school_year' => '2026-2027',
            'is_active' => true,
        ]);
        $inactiveYear = SchoolYear::create([
            'school_year' => '2025-2026',
            'is_active' => false,
        ]);
        $section = Section::create([
            'name' => 'A',
            'level' => 'highschool',
            'grade_level' => 7,
            'session_type' => 'morning',
            'status' => 'active',
        ]);
        $student = Student::create([
            'lrn' => '123456789012',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'sex' => 'female',
            'address' => 'Test address',
            'status' => 'active',
            'student_number' => 'STU-TEST-001',
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
        $student->qrCode()->create([
            'code' => 'QR-TEST-001',
            'is_active' => true,
        ]);
        ScheduleConfig::create([
            'level' => 'hs',
            'session_type' => 'morning',
            'in_start' => '07:00',
            'in_end' => '09:00',
            'late_threshold' => '08:00',
            'out_start' => '15:00',
            'out_end' => '17:00',
        ]);

        $response = $this->actingAs(User::factory()->create([
            'role' => User::ROLE_SCANNER_OPERATOR,
        ]))->postJson('/qr-station/scan', [
            'code' => 'QR-TEST-001',
            'device_id' => 'station-1',
        ]);

        $response->assertOk();
        $response->assertJsonPath('attendance_log.scan_type', 'IN');
        $this->assertDatabaseHas('attendance_logs', [
            'enrollment_id' => $activeEnrollment->id,
            'scan_type' => 'IN',
        ]);
        $this->assertDatabaseMissing('attendance_logs', [
            'enrollment_id' => $wrongEnrollment->id,
        ]);
        $this->assertDatabaseHas('qr_attendances', [
            'enrollment_id' => $activeEnrollment->id,
            'attendance_date' => '2026-09-06',
        ]);
        Queue::assertPushed(SendGateScanNotification::class);

        Carbon::setTestNow();
    }
}
