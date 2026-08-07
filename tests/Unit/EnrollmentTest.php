<?php

namespace Tests\Unit;

use App\Models\Enrollment;
use App\Models\Section;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_auto_populates_grade_level_and_section_from_section(): void
    {
        $schoolYear = SchoolYear::create([
            'school_year' => '2026-2027',
            'is_active' => true,
        ]);

        $section = Section::create([
            'name' => 'Test Sec A',
            'level' => 'elementary',
            'grade_level' => 3,
            'capacity' => 30,
            'status' => 'active',
        ]);

        $student = Student::create([
            'student_number' => 'STU-TEST-1',
            'lrn' => '999999999999',
            'first_name' => 'Unit',
            'last_name' => 'Tester',
            'sex' => 'female',
            'birthdate' => '2015-01-01',
            'address' => 'Test',
            'status' => 'active',
        ]);

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'section_id' => $section->id,
            'school_year_id' => $schoolYear->id,
            'level' => 'elementary',
            'session_type' => 'morning',
            'status' => 'active',
        ]);

        $this->assertNotEmpty($enrollment->grade_level, 'grade_level should be populated from section');
        $this->assertEquals((string) $section->grade_level, $enrollment->grade_level);
        $this->assertNotNull($enrollment->section, 'section relation should be available');
        $this->assertEquals($section->name, $enrollment->section->name);
    }
}
