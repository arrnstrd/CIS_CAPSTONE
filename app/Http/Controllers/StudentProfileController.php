<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\Student;

class StudentProfileController extends Controller
{
 


    public function show(Student $student)
    {
        $schoolYearId = session('school_year_id')
            ?? SchoolYear::query()->active()->value('id');

        $currentEnrollment = $student->enrollments()
            ->with(['schoolYear', 'section.advisor.user', 'adviser.user'])
            ->when($schoolYearId, function ($query, $schoolYearId) {
                $query->where('school_year_id', $schoolYearId);
            })
            ->first();

        return view('admin-modules.management.student-profile', compact(
            'student',
            'currentEnrollment'
        ));
    }

}
