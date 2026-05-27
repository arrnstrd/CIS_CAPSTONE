<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Student;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // public function searchAttendanceLog(Request $request){
    //     $search = $request->search;
    //     $attendanceLogs = AttendanceLog::where(function($query) use($search){
    //         $query->where()
    //     });
    // }


    public function searchStudent(Request $request)
    {
        $search = trim($request->search);

        $students = Student::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('lrn', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%");
                });
            })
            ->paginate(15);

        return view('admin-modules.management.studentList', compact('students'));
    }
}


