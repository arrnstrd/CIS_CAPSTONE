<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class StudentController extends Controller
{
    public function __construct(
        private readonly StudentService $studentService,
    ) {}


    public function index()
    {
        $grades = [
            'Elementary' => range(1, 6),
            'Junior High School' => range(7, 10),
            'Senior High School' => range(11, 12),
        ];

        $activeSchoolYear = SchoolYear::query()->where('is_active', true)->first();

        $gradeCounts = Enrollment::query()
            ->when($activeSchoolYear, fn($q) => $q->where('school_year_id', $activeSchoolYear->id))
            ->selectRaw('grade_level, COUNT(DISTINCT student_id) as total')
            ->groupBy('grade_level')
            ->pluck('total', 'grade_level');

        return view('admin-modules.management.students.index', compact('grades', 'gradeCounts'));
    }

    public function byGrade(Request $request, string $grade)
    {
        $grade = (int) $grade;

        if ($grade < 1 || $grade > 12) {
            abort(404);
        }

        $activeSchoolYear = SchoolYear::query()->where('is_active', true)->first();
        $schoolYearId = $request->input('school_year_id');
        $sectionId = $request->input('section');
        $status = $request->input('status');
        $query = $request->input('query');

        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get(['id', 'school_year']);
        $sections = Section::query()
            ->where('grade_level', $grade)
            ->orderBy('name')
            ->get(['id', 'name']);
        $allSections = Section::query()
            ->where('status', 'active')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level']);

        $students = Student::with(['enrollments.sectionModel', 'guardian'])
            ->whereHas('enrollments', function ($q) use ($grade, $schoolYearId, $activeSchoolYear, $sectionId, $status) {
                $q->where('grade_level', (string) $grade);

                if ($schoolYearId && $schoolYearId !== 'all') {
                    $q->where('school_year_id', $schoolYearId);
                } elseif ($activeSchoolYear) {
                    $q->where('school_year_id', $activeSchoolYear->id);
                }

                if ($sectionId && $sectionId !== 'all') {
                    $q->where('section_id', $sectionId);
                }

                if ($status && $status !== 'all') {
                    $q->where('status', $status);
                }
            })
            ->when(filled($query), function ($q) use ($query) {
                $q->where(function ($q) use ($query) {
                    $q->where('student_number', 'like', "%{$query}%")
                        ->orWhere('lrn', 'like', "%{$query}%")
                        ->orWhere('first_name', 'like', "%{$query}%")
                        ->orWhere('last_name', 'like', "%{$query}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();

        $students->getCollection()->each(function (Student $student) use ($grade, $schoolYearId, $activeSchoolYear) {
            $enrollment = $student->enrollments
                ->filter(fn($e) => (int) $e->grade_level === $grade)
                ->filter(function ($e) use ($schoolYearId, $activeSchoolYear) {
                    return $schoolYearId && $schoolYearId !== 'all'
                        ? $e->school_year_id == $schoolYearId
                        : $e->school_year_id === $activeSchoolYear?->id;
                })
                ->first();

            $student->setAttribute('grade_level', $enrollment?->sectionModel?->grade_level ?? $grade);
            $student->setAttribute('section_name', $enrollment?->sectionModel?->name ?? '-');
            $student->setAttribute('enrollment_status', $enrollment?->status ?? '-');
            $student->setAttribute('enrollment_id', $enrollment?->id);
            $student->setAttribute('section_id', $enrollment?->section_id);
            $student->setAttribute('session_type', $enrollment?->session_type);
            $student->setAttribute('school_year_id', $enrollment?->school_year_id);
        });

        return view('admin-modules.management.students.show', compact('students', 'grade', 'sections', 'allSections', 'schoolYears', 'activeSchoolYear'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPayload($request);

        try {
            $student = $this->studentService->createStudent(
                studentData: [
                    'lrn' => $validated['lrn'],
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'middle_name' => $validated['middle_name'] ?? null,
                    'suffix' => $validated['suffix'] ?? null,
                    'sex' => $validated['sex'],
                    'address' => $validated['address'],
                    'birthdate' => $validated['birthdate'],
                    'status' => $validated['status'],
                ],
                guardianData: [
                    'name' => $validated['name'],
                    'relationship' => $validated['relationship'],
                    'contact_number' => $validated['contact_number'] ?? null,
                    'email' => $validated['email'],
                ],
                enrollmentData: [
                    'school_year_id' => $validated['school_year_id'],
                    'section_id' => $validated['section_id'],
                    'session_type' => $validated['session_type'],
                    'status' => $validated['enrollment_status'],
                ],
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Student, guardian, and enrollment created successfully',
            'student' => $student->load(['guardian', 'enrollments', 'qrCode']),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $student = Student::findOrFail($id);
        $validated = $this->validatedPayload($request, $id);

        try {
            $student = $this->studentService->updateStudent(
                student: $student,
                studentData: [
                    'lrn' => $validated['lrn'],
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'middle_name' => $validated['middle_name'] ?? null,
                    'suffix' => $validated['suffix'] ?? null,
                    'sex' => $validated['sex'],
                    'address' => $validated['address'],
                    'birthdate' => $validated['birthdate'],
                    'status' => $validated['status'],
                ],
                guardianData: [
                    'name' => $validated['name'],
                    'relationship' => $validated['relationship'],
                    'contact_number' => $validated['contact_number'] ?? null,
                    'email' => $validated['email'],
                ],
                enrollmentData: [
                    'school_year_id' => $validated['school_year_id'],
                    'section_id' => $validated['section_id'],
                    'session_type' => $validated['session_type'],
                    'status' => $validated['enrollment_status'],
                ],
            );
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Student, guardian, and enrollment updated successfully',
            'student' => $student->load(['guardian', 'enrollments']),
        ]);
    }

    private function validatedPayload(Request $request, ?int $studentId = null): array
    {
        return $request->validate([
            'lrn' => ['required', 'digits_between:12,13', Rule::unique('students', 'lrn')->ignore($studentId)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'sex' => ['required', 'in:female,male'],
            'address' => ['required', 'string'],
            'birthdate' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],

            // guardian
            'name' => ['required', 'string'],
            'relationship' => ['required', 'in:mother,father,sibling,guardian'],
            'contact_number' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email'],

            // enrollment
            'school_year_id' => ['required', 'exists:school_years,id'],
            'grade_level' => ['required', 'integer', 'between:1,12'],
            'section_id' => [
                'required',
                Rule::exists('sections', 'id')->where(function ($query) use ($request) {
                    $query->where('grade_level', $request->input('grade_level'));
                }),
            ],
            'session_type' => ['required', 'in:morning,afternoon,whole_day'],
            'enrollment_status' => ['required', 'in:active,inactive'],
        ]);
    }

    public function destroy(string $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Student not found'
            ], 404);
        }

        try {
            $student->delete();

            return response()->json([
                'message' => 'Student deleted successfully',
                'data' => $student
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete student',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function search(Request $request)
    {
        $q = $request->query('q', '');
        $activeSchoolYearId = SchoolYear::query()->where('is_active', true)->value('id');

        $students = Student::query()
            ->with('enrollments')
            ->where(function ($query) use ($q) {
                $query->where('student_number', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'student_number', 'first_name', 'last_name'])
            ->each(function (Student $student) use ($activeSchoolYearId) {
                $student->is_enrolled = $activeSchoolYearId
                    ? $student->enrollments->contains('school_year_id', $activeSchoolYearId)
                    : false;
            });

        return response()->json($students);
    }

    public function show($id)
    {
        return Student::with('guardian')->findOrFail($id);
    }
}
