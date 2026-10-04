<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\AttendanceVerification;
use App\Models\AttendanceVerificationHistory;
use App\Models\Enrollment;
use App\Models\FlaggedScan;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class SchoolAdminAttendanceMonitoringTest extends TestCase
{

    public function test_school_admin_sidebar_displays_sf1_import_and_no_bulk_import(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->get(route('attendance.grade-level'));

        $response->assertOk();
        $response->assertSee('SF1 Import');
        $response->assertDontSee('>Bulk Import<', false);
    }

    public function test_attendance_description_reflects_section_monitoring(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->get(route('attendance.grade-level'));

        $response->assertOk();
        $response->assertSee('Monitor attendance records and verification status by section.');
    }

    public function test_school_admin_can_view_read_only_section_attendance(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $sy = SchoolYear::create([
            'school_year' => '2026-2027',
            'is_active' => true,
        ]);

        $section = Section::create([
            'name' => 'Section Emerald',
            'level' => 'senior_high_school',
            'grade_level' => 11,
            'capacity' => 40,
        ]);

        $student = Student::create([
            'lrn' => '123456789011',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'student_number' => 'SN-11001',
            'sex' => 'male',
            'address' => 'Sample Address',
            'status' => 'active',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'school_year_id' => $sy->id,
            'grade_level' => 11,
            'status' => 'active',
        ]);

        $noScanStudent = Student::create([
            'lrn' => '123456789099',
            'first_name' => 'No Scan',
            'last_name' => 'Student',
            'student_number' => 'SN-11099',
            'sex' => 'female',
            'address' => 'Sample Address',
            'status' => 'active',
        ]);

        Enrollment::create([
            'student_id' => $noScanStudent->id,
            'section_id' => $section->id,
            'school_year_id' => $sy->id,
            'grade_level' => 11,
            'status' => 'active',
        ]);

        $log = AttendanceLog::create([
            'enrollment_id' => $enrollment->id,
            'scan_time' => Carbon::today()->setTime(7, 45),
            'scan_type' => 'IN',
            'session_type' => 'morning',
        ]);

        FlaggedScan::create([
            'attendance_log_id' => $log->id,
            'flag_type' => 'late_arrival',
            'description' => 'Arrived after the scheduled entry window.',
        ]);

        $response = $this->actingAs($admin)->get(route('attendance.section.show', [
            'grade' => 11,
            'section' => $section->id,
        ]));

        $response->assertOk();
        $response->assertSee('Section Emerald');
        $response->assertSee('Juan Dela Cruz');
        $response->assertSee('SN-11001');
        $response->assertSee('Read-Only Monitoring Mode');
        $response->assertSee('Back to Sections');
        $response->assertSee('table-name-avatar');
        $response->assertSee('sa-att-history-panel');
        $response->assertSee('sa-att-history-context');
        $response->assertSee('sa-att-history-section');
        $response->assertDontSee('class-hub-drawer');
        $response->assertDontSee('ra-side-panel');
        $response->assertSee('Grade Level');
        $response->assertSee('Subject Teacher');
        $response->assertSee('Adviser');
        $response->assertSee('Remarks');
        $response->assertSee('>Time In<', false);
        $response->assertDontSee('>OUT<', false);
        $response->assertSee('Late Arrival');
        $response->assertDontSee('Scan times');
        $response->assertSee('All subjects');
        $response->assertSee('No remarks');
        $response->assertDontSee('Unverified');
        $response->assertDontSee('Showing enrolled students for');
        $response->assertDontSee('Gate IN');
        $response->assertDontSee('Gate OUT');
        $response->assertDontSee('Gate Scan');
        $response->assertDontSee('Gate Telemetry');
    }

    public function test_academic_setup_focuses_on_subjects_only(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->actingAs($admin)->get(route('academic.index'));

        $response->assertOk();
        $response->assertSee('Subjects');
        $response->assertSee('Manage curriculum subjects for academic configuration.');
        $response->assertDontSee('#section-table-pane');
    }

    public function test_teacher_verification_history_is_accessible_in_read_only_audit_endpoint(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $teacherUser = User::factory()->create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'role' => User::ROLE_TEACHER,
        ]);

        $teacher = $teacherUser->teacher ?: Teacher::create(['user_id' => $teacherUser->id]);

        $sy = SchoolYear::create([
            'school_year' => '2026-2027',
            'is_active' => true,
        ]);

        $section = Section::create([
            'name' => 'Section Sapphire',
            'level' => 'highschool',
            'grade_level' => 10,
            'capacity' => 45,
        ]);

        $student = Student::create([
            'lrn' => '123456789012',
            'first_name' => 'Pedro',
            'last_name' => 'Penduko',
            'student_number' => 'SN-10042',
            'sex' => 'male',
            'address' => 'Sample Address',
            'status' => 'active',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'school_year_id' => $sy->id,
            'grade_level' => 10,
            'status' => 'active',
        ]);

        $subject = \App\Models\Subject::create([
            'name' => 'Science',
            'code' => 'SCI-10',
            'level' => 'hs',
        ]);

        $assignment = \App\Models\TeachingAssignment::create([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'section_id' => $section->id,
            'school_year_id' => $sy->id,
            'status' => 'active',
        ]);

        $today = Carbon::today();

        $verification = AttendanceVerification::create([
            'enrollment_id' => $enrollment->id,
            'teaching_assignment_id' => $assignment->id,
            'attendance_date' => $today->toDateString(),
            'teacher_id' => $teacher->id,
            'resolved_by' => AttendanceVerification::RESOLVED_BY_TEACHER,
            'status' => AttendanceVerification::STATUS_PRESENT,
            'remarks' => 'Verified physically in seat',
            'verified_at' => now(),
        ]);

        AttendanceVerificationHistory::create([
            'attendance_verification_id' => $verification->id,
            'previous_status' => 'absent',
            'new_status' => 'present',
            'changed_by' => $teacherUser->id,
            'remarks' => 'Verified physically in seat',
        ]);

        $response = $this->actingAs($admin)->getJson(route('attendance.section.student-history', [
            'grade' => 10,
            'section' => $section->id,
            'enrollment' => $enrollment->id,
            'date' => $today->toDateString(),
        ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'student' => ['name', 'student_number'],
            'date_formatted',
            'stepper',
            'scans',
        ]);

        $data = $response->json();
        $this->assertEquals('Pedro Penduko', $data['student']['name']);
        $this->assertNotEmpty($data['stepper']);

        // Check verification change details
        $verificationStep = collect($data['stepper'])->firstWhere('stage', 'verification_change');
        $this->assertNotNull($verificationStep);
        $this->assertEquals('absent', $verificationStep['previous_status']);
        $this->assertEquals('present', $verificationStep['new_status']);
        $this->assertStringContainsString('Maria Santos', $verificationStep['actor']);
    }

    public function test_in_out_history_displays_specific_flag_reasons_instead_of_generic_needs_review(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $sy = SchoolYear::create([
            'school_year' => '2026-2027',
            'is_active' => true,
        ]);

        $section = Section::create([
            'name' => 'Section Ruby',
            'level' => 'highschool',
            'grade_level' => 9,
            'capacity' => 35,
        ]);

        $student = Student::create([
            'lrn' => '123456789013',
            'first_name' => 'Jose',
            'last_name' => 'Rizal',
            'student_number' => 'SN-09001',
            'sex' => 'male',
            'address' => 'Sample Address',
            'status' => 'active',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'school_year_id' => $sy->id,
            'grade_level' => 9,
            'status' => 'active',
        ]);

        $log = AttendanceLog::create([
            'enrollment_id' => $enrollment->id,
            'scan_time' => now(),
            'scan_type' => 'IN',
            'session_type' => 'morning',
        ]);

        FlaggedScan::create([
            'attendance_log_id' => $log->id,
            'flag_type' => 'late_arrival',
            'description' => 'Late arrival: Scanned at 07:45 AM',
        ]);

        $response = $this->actingAs($admin)->get(route('school_admin.time-in-time-out-history.index'));

        $response->assertOk();
        $response->assertSee('Late Arrival');
        $response->assertDontSee('Needs review');
    }
}
