<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller

{
    public function index(Request $request)
    {
        $query = $request->input('query');
        $school_year_id = $request->input('school_year_id');
        $grade_level = $request->input('grade_level');

        $enrollments = Enrollment::with(['student', 'schoolYear'])
            ->when($query, function ($q) use ($query) {
                $q->whereHas('student', function ($q) use ($query) {
                    $q->where('student_number', 'like', "%{$query}%")
                        ->orWhere('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%");
                });
            })
            ->when($school_year_id && $school_year_id !== 'all', function ($q) use ($school_year_id) {
                $q->where('school_year_id', $school_year_id);
            })
            ->when($grade_level && $grade_level !== 'all', function ($q) use ($grade_level) {
                $q->where('grade_level', $grade_level);
            })
            ->orderBy('student_id')
            ->paginate(25)
            ->withQueryString();

        $school_years = SchoolYear::orderBy('school_year', 'desc')
            ->get(['id', 'school_year']);

        $statusCounts= Enrollment::getEnrollmentStatistics($school_year_id);
        $studentWithoutEnrollment= Student::withoutCurrentEnrollment($school_year_id)->count();


        return view('admin-modules.management.enrollment.index', compact('enrollments', 'school_years' , 'statusCounts' , 'studentWithoutEnrollment'));
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'level' => ['required', 'in:elementary,hs,shs'],
            'grade_level' => [
                'required',
                Rule::in([
                    'Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6',
                    'Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12',
                ])
            ],
            'section' => ['required', 'string', 'max:255'],
            'session_type' => ['required', 'in:morning,afternoon'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $currentSchoolYear = SchoolYear::where('is_active', true)->firstOrFail();

        $validatedData['school_year_id'] = $currentSchoolYear->id;

        try {
            $enrollment = Enrollment::create($validatedData);

            return response()->json([
                'message' => 'Enrollment created successfully',
                'data' => $enrollment
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Student is already enrolled for this school year'
            ], 422);
        }
    }


    public function update(Request $request, string $id)
    {
        $enrollment = Enrollment::findOrFail($id);

        $validatedData = $request->validate([
            'level' => ['required', 'in:elementary,hs,shs'],
            'grade_level' => [
                'required',
                Rule::in([
                    'Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6',
                    'Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12',
                ])
            ],
            'section' => ['required', 'string', 'max:255'],
            'session_type' => ['required', 'in:morning,afternoon'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $exists = Enrollment::where('student_id', $enrollment->student_id)
            ->where('school_year_id', $enrollment->school_year_id)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This student is already enrolled for this school year'
            ], 422);
        }

        $enrollment->update($validatedData);

        return response()->json([
            'message' => 'Enrollment updated successfully',
            'data' => $enrollment->fresh()
        ]);
    }


    public function destroy(string $id)
    {
        $enrollment = Enrollment::find($id);

        if (!$enrollment) {
            return response()->json([
                'message' => 'Enrollment not found'
            ], 404);
        }

        try {
            $enrollment->delete();

            return response()->json([
                'message' => 'Enrollment deleted successfully',
                'data' => [
                    'id' => $enrollment->id,
                    'student_id' => $enrollment->student_id,
                    'school_year_id' => $enrollment->school_year_id,
                    'level' => $enrollment->level,
                    'status' => 'deleted'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete enrollment',
                'error' => $e->getMessage()
            ], 500);
        }
    }





    
}