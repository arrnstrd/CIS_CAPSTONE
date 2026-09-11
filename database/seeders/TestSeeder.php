<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Section;
use App\Models\SchoolYear;
use App\Models\TeachingAssignment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database for testing.
     */
    public function run(): void
    {
        // Create a school year
        $schoolYear = SchoolYear::firstOrCreate([
            'school_year' => '2026-2027',
        ], [
            'is_active' => true,
        ]);

        // Create a section
        $section = Section::firstOrCreate([
            'name' => 'Test Section',
        ], [
            'grade_level' => 11,
            'status' => 'active',
        ]);

        // Create a subject
        $subject = Subject::firstOrCreate([
            'name' => 'Mathematics',
            'code' => 'MATH101',
        ], [
            'description' => 'Basic Mathematics',
            'level' => 'Grade 11',
            'status' => 'active',
        ]);

        // Create a teacher user
        $teacherUser = User::factory()->create([
            'role' => 'teacher',
            'status' => 'active',
            'email' => 'test.teacher@example.com',
        ]);

        // Create a teacher record
        $teacher = Teacher::firstOrCreate([
            'user_id' => $teacherUser->id,
        ], [
            'status' => 'active',
        ]);

        // Create a teaching assignment
        TeachingAssignment::firstOrCreate([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'section_id' => $section->id,
            'school_year_id' => $schoolYear->id,
        ], [
            'semester' => '1',
            'status' => 'active',
        ]);
    }
}