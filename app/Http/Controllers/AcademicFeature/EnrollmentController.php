<?php

namespace App\Http\Controllers\AcademicFeature;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Services\EnrollmentService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollmentService,
    ) {}

    public function index(Request $request)
    {
        $query = $request->input('query');
        $grade_level = $request->input('grade_level');
        $activeSchoolYear = SchoolYear::query()->where('is_active', true)->first();
        $activeSections = Section::with('advisor')
            ->where('status', 'active')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        $school_years = SchoolYear::orderBy('school_year', 'desc')
            ->get(['id', 'school_year']);

        if (! $activeSchoolYear) {
            $enrollments = new LengthAwarePaginator([], 0, 25);
            $notEnrolledStudents = new LengthAwarePaginator([], 0, 10);
            $statusCounts = [
                'total' => 0,
                'elementary' => 0,
                'hs' => 0,
                'shs' => 0,
            ];
            $studentWithoutEnrollment = 0;

            return view('admin-modules.management.enrollment.index', compact('enrollments', 'school_years', 'statusCounts', 'studentWithoutEnrollment', 'notEnrolledStudents', 'activeSchoolYear', 'activeSections'));
        }

        $enrollments = Enrollment::with(['student', 'schoolYear', 'section.advisor'])
            ->where('school_year_id', $activeSchoolYear->id)
            ->when($query, function ($q) use ($query) {
                $q->whereHas('student', function ($q) use ($query) {
                    $q->where('student_number', 'like', "%{$query}%")
                        ->orWhere('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%");
                });
            })
            ->when($grade_level && $grade_level !== 'all', function ($q) use ($grade_level) {
                $q->whereHas('section', function ($sectionQuery) use ($grade_level) {
                    $sectionQuery->where('grade_level', $grade_level);
                });
            })
            ->orderBy('student_id')
            ->paginate(25)
            ->withQueryString();

        $statusCounts = Enrollment::getEnrollmentStatistics($activeSchoolYear->id);
        $studentWithoutEnrollment = Student::withoutCurrentEnrollment($activeSchoolYear->id)
            ->count();

        $notEnrolledStudents = Student::withoutCurrentEnrollment($activeSchoolYear->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        // return view('admin-modules.academic.enrollment');

        return view('admin-modules.management.enrollment.index', compact('enrollments', 'school_years', 'statusCounts', 'studentWithoutEnrollment', 'notEnrolledStudents', 'activeSchoolYear', 'activeSections'));
    }


    public function store(Request $request)
    {
        $currentSchoolYear = SchoolYear::query()->where('is_active', true)->first();

        if (! $currentSchoolYear) {
            return response()->json([
                'message' => 'No active school year exists. Please create or activate a school year before enrolling students.'
            ], 422);
        }

        $validatedData = $request->validate([
            'student_id' => [
                'required',
                'exists:students,id',
                Rule::unique('enrollments', 'student_id')
                    ->where(fn($query) => $query->where('school_year_id', $currentSchoolYear->id)),
            ],
            'section_id' => ['required', 'exists:sections,id'],
            'session_type' => ['required', 'in:morning,afternoon,whole_day'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        try {
            $enrollment = $this->enrollmentService->createEnrollment(
                $validatedData,
                $currentSchoolYear->id
            );

            $enrollment->load(['student', 'schoolYear', 'section']);

            return response()->json([
                'message' => 'Enrollment created successfully',
                'data' => $enrollment,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (QueryException $e) {
            report($e);

            return response()->json([
                'message' => 'Unable to create enrollment. Please check the selected student and section.',
            ], 422);
        }
    }


    public function update(Request $request, string $id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $currentSchoolYear = SchoolYear::query()->where('is_active', true)->first();

        if (! $currentSchoolYear && ! $enrollment->school_year_id) {
            return response()->json([
                'message' => 'No active school year exists. Please create or activate a school year before updating enrollments.'
            ], 422);
        }

        $validatedData = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'session_type' => ['required', 'in:morning,afternoon,whole_day'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        try {
            $enrollment = $this->enrollmentService->updateEnrollment(
                $enrollment,
                $validatedData
            );

            return response()->json([
                'message' => 'Enrollment updated successfully',
                'data' => $enrollment->fresh()->load(['student', 'schoolYear', 'section']),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
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
                    'section_id' => $enrollment->section_id,
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
