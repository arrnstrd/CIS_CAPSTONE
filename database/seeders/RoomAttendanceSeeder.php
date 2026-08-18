<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\RoomAttendance;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoomAttendanceSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Get the current school year (create if it doesn't exist)
        $schoolYear = SchoolYear::firstOrCreate([
            'school_year' => '2025-2026'
        ], [
            'is_active' => true
        ]);

        // Create a section if none exists
        $section = Section::firstOrCreate([
            'name' => 'Grade 10 - Rizal',
            'level' => 'highschool',
            'grade_level' => 10
        ], [
            'advisor_id' => null,
            'capacity' => 40,
            'status' => 'active'
        ]);

        // Create a subject if none exists
        $subject = Subject::firstOrCreate([
            'code' => 'ENGLISH10',
            'name' => 'English',
            'level' => 'hs'
        ]);

        // Get the existing teacher user
        $teacherUser = User::where('email', 'arriane.estrada@example.com')->first();
        
        if (!$teacherUser) {
            // Create teacher user if it doesn't exist
            $teacherUser = User::create([
                'first_name' => 'Arriane',
                'last_name' => 'Estrada',
                'email' => 'arriane.estrada@example.com',
                'password' => Hash::make('Password123'),
                'role' => User::ROLE_TEACHER,
                'status' => 'active',
            ]);
        }

        // Create teacher record if it doesn't exist
        $teacher = Teacher::firstOrCreate([
            'user_id' => $teacherUser->id
        ], [
            'status' => 'active'
        ]);

        // Create teaching assignment (basic fields only)
        $teachingAssignment = TeachingAssignment::firstOrCreate([
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'section_id' => $section->id,
            'school_year_id' => $schoolYear->id
        ], [
            'status' => 'active'
        ]);

        // Create 5 students
        $students = [
            ['first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'sex' => 'male'],
            ['first_name' => 'Maria', 'last_name' => 'Santos', 'sex' => 'female'],
            ['first_name' => 'Pedro', 'last_name' => 'Gonzalez', 'sex' => 'male'],
            ['first_name' => 'Ana', 'last_name' => 'Reyes', 'sex' => 'female'],
            ['first_name' => 'Carlos', 'last_name' => 'Lopez', 'sex' => 'male'],
        ];

        foreach ($students as $studentData) {
            $student = Student::firstOrCreate([
                'lrn' => '123456789' . rand(1000, 9999),
                'first_name' => $studentData['first_name'],
                'last_name' => $studentData['last_name'],
                'sex' => $studentData['sex']
            ], [
                'address' => '123 Sample Street, City',
                'birthdate' => now()->subYears(15)->subDays(rand(1, 365)),
                'status' => 'active'
            ]);

            // Create enrollment
            Enrollment::firstOrCreate([
                'student_id' => $student->id,
                'school_year_id' => $schoolYear->id,
                'grade_level' => $section->grade_level,
                'section_id' => $section->id,
                'level' => 'hs', // Enrollment uses 'hs' for high school
                'session_type' => 'morning'
            ], [
                'status' => 'active'
            ]);
        }

        // Get all created students
        $createdStudents = Student::whereIn('first_name', array_column($students, 'first_name'))
                                 ->whereIn('last_name', array_column($students, 'last_name'))
                                 ->get();

        // Get enrollments for these students
        $enrollments = Enrollment::where('school_year_id', $schoolYear->id)
                                 ->where('section_id', $section->id)
                                 ->get();

        // Define dates for attendance records
        $dates = [
            'today' => now()->toDateString(),
            '3_days_ago' => now()->subDays(3)->toDateString(),
            '7_days_ago' => now()->subDays(7)->toDateString(),
        ];

        // Create attendance records for each date
        foreach ($dates as $dateKey => $date) {
            foreach ($enrollments as $index => $enrollment) {
                // Create 3-5 records per date (varying which students have attendance)
                if ($index < 3) { // Only first 3 students per date
                    $timeIn = now()->setTime(7, 15 + $index * 5, 0); // 7:15, 7:20, 7:25
                    $timeOut = $timeIn->addHours(8); // 8+ hours later
                    
                    RoomAttendance::firstOrCreate([
                        'teaching_assignment_id' => $teachingAssignment->id,
                        'enrollment_id' => $enrollment->id,
                        'attendance_date' => $date
                    ], [
                        'time_in' => $timeIn,
                        'time_out' => $timeOut,
                        'remarks' => null
                    ]);
                }
            }
        }

        // Create some late records for variety
        $lateEnrollment = $enrollments->first();
        if ($lateEnrollment) {
            $lateTimeIn = now()->setTime(8, 15, 0); // Late arrival
            $lateTimeOut = $lateTimeIn->addHours(8);
            
            RoomAttendance::firstOrCreate([
                'teaching_assignment_id' => $teachingAssignment->id,
                'enrollment_id' => $lateEnrollment->id,
                'attendance_date' => $dates['today']
            ], [
                'time_in' => $lateTimeIn,
                'time_out' => $lateTimeOut,
                'remarks' => 'Arrived 15 minutes late'
            ]);
        }
    }
}