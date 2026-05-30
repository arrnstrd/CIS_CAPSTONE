<?php

namespace App\Http\Controllers;

use App\Models\Student;

class StudentProfileController extends Controller
{
    public function show(Student $student){

        return view('admin-modules.management.student-profile', compact('student'));
    }
}
