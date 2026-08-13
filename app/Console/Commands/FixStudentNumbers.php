<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:fix-student-numbers')]
#[Description('Fix empty student_number fields for all students')]
class FixStudentNumbers extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix student numbers...');
        
        $students = Student::whereNull('student_number')->orWhere('student_number', '')->get();
        
        if ($students->count() === 0) {
            $this->info('No students found with empty student_number fields.');
            return 0;
        }
        
        $this->info("Found {$students->count()} students to fix.");
        
        foreach ($students as $student) {
            $student->student_number = 'STU-' . now()->year . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT);
            $student->saveQuietly();
            $this->info("Updated student ID {$student->id} with number: {$student->student_number}");
        }
        
        $this->info('Student numbers fix completed successfully!');
        return 0;
    }
}
