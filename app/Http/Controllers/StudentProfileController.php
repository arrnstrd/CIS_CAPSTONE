<?php

namespace App\Http\Controllers;

use App\Models\Student;

class StudentProfileController extends Controller
{
    public function show(Student $student){
        
        $currentEnrollment = $student->enrollments()
            ->where('school_year_id' , session('school_year_id'))
            ->first();
      
        return view('admin-modules.management.student-profile', compact('student', 'currentEnrollment'));
    }
}
