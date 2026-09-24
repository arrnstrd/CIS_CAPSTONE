<?php

namespace App\Http\Controllers\SchoolAdmin\Students;

use App\Events\StudentUpdated;
use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Enrollment;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Services\Export\StudentSf1ExportService;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class StudentManagementController extends Controller
{
    public function __construct(
        private readonly StudentService $studentService,
        private readonly StudentSf1ExportService $exportService,
    ) {}


    public function index(Request $request)
    {
        if ($request->user()?->isTeacher()) {
            return redirect()->route('teacher.student-management');
        }

        $t0 = microtime(true); // TEMP

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

        \Illuminate\Support\Facades\Log::debug('[PROFILE-CTRL:student-management] ms=' . round((microtime(true) - $t0) * 1000, 1)); // TEMP

        return view('pov.school-admin.students.students', compact('grades', 'gradeCounts'));
    }

    public function byGrade(Request $request, string $grade)
    {
        if ($request->user()?->isTeacher()) {
            return redirect()->route('teacher.student-management');
        }

        $grade = (int) $grade;

        if ($grade < 1 || $grade > 12) {
            abort(404);
        }

        $activeSchoolYear = SchoolYear::query()->where('is_active', true)->first();
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get(['id', 'school_year']);

        $sections = Section::query()
            ->where('grade_level', $grade)
            ->with('advisor.user')
            ->withCount(['enrollments as student_count' => function ($q) use ($activeSchoolYear) {
                if ($activeSchoolYear) {
                    $q->where('school_year_id', $activeSchoolYear->id);
                }
            }])
            ->orderBy('name')
            ->get();

        $allSections = Section::query()
            ->where('status', 'active')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level']);

        $teachers = Teacher::with('user')
            ->where('status', 'active')
            ->get()
            ->sortBy('full_name')
            ->values();

        return view('pov.school-admin.students.section-selection', compact(
            'grade',
            'sections',
            'allSections',
            'schoolYears',
            'activeSchoolYear',
            'teachers'
        ));
    }

    public function bySection(Request $request, string $grade, Section $section)
    {
        $grade = (int) $grade;

        if ($grade < 1 || $grade > 12 || (int) $section->grade_level !== $grade) {
            abort(404);
        }

        if ($request->user()?->isTeacher()) {
            $teacher = $request->user()->teacher;
            abort_unless($teacher, 403, 'Teacher profile not found.');

            $teachingSectionIds = TeachingAssignment::query()
                ->where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->pluck('section_id');

            $advisedSectionIds = Section::query()
                ->where('advisor_id', $teacher->id)
                ->where('status', 'active')
                ->pluck('id');

            $teacherSectionIds = $teachingSectionIds
                ->concat($advisedSectionIds)
                ->unique()
                ->values()
                ->all();

            abort_unless(
                in_array($section->id, $teacherSectionIds),
                403,
                'You do not have access to this section.'
            );

            return redirect()->route('teacher.student-management', ['section_id' => $section->id]);
        }

        $activeSchoolYear = SchoolYear::query()->where('is_active', true)->first();
        $schoolYearId = $request->input('school_year_id');
        $status = $request->input('status');
        $query = $request->input('query');
        $sort = $request->input('sort', 'last_name_asc');

        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get(['id', 'school_year']);
        $section->load(['advisor.user']);

        $allSections = Section::query()
            ->where('status', 'active')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get(['id', 'name', 'grade_level']);

        $teachers = Teacher::with('user')
            ->where('status', 'active')
            ->get()
            ->sortBy('full_name')
            ->values();

        $subjects = Subject::orderBy('name')->get();

        $teachingAssignments = TeachingAssignment::with(['teacher.user', 'subject', 'schoolYear'])
            ->where('section_id', $section->id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $students = Student::with(['enrollments.sectionModel', 'guardian'])
            ->whereHas('enrollments', function ($q) use ($grade, $schoolYearId, $activeSchoolYear, $section, $status) {
                $q->where('grade_level', (string) $grade)
                    ->where('section_id', $section->id);

                if ($schoolYearId && $schoolYearId !== 'all') {
                    $q->where('school_year_id', $schoolYearId);
                } elseif ($activeSchoolYear) {
                    $q->where('school_year_id', $activeSchoolYear->id);
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
            ->when($sort === 'last_name_desc', fn($q) => $q->orderByDesc('last_name')->orderByDesc('first_name'))
            ->when($sort === 'first_name_asc', fn($q) => $q->orderBy('first_name')->orderBy('last_name'))
            ->when($sort === 'first_name_desc', fn($q) => $q->orderByDesc('first_name')->orderByDesc('last_name'))
            ->when($sort === 'student_number_asc', fn($q) => $q->orderBy('student_number'))
            ->when($sort === 'student_number_desc', fn($q) => $q->orderByDesc('student_number'))
            ->when(!in_array($sort, ['last_name_desc', 'first_name_asc', 'first_name_desc', 'student_number_asc', 'student_number_desc'], true), function ($q) {
                $q->orderBy('last_name')->orderBy('first_name');
            })
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
            $student->setAttribute('session_type', $enrollment?->section?->session_type);
            $student->setAttribute('school_year_id', $enrollment?->school_year_id);
        });

        return view('pov.school-admin.students.show', compact(
            'students',
            'grade',
            'section',
            'allSections',
            'schoolYears',
            'activeSchoolYear',
            'sort',
            'teachers',
            'subjects',
            'teachingAssignments'
        ));
    }

    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = trim((string) $request->input('query', $request->input('q', '')));

        if (strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $activeSchoolYear = SchoolYear::where('is_active', true)->first();

        $students = Student::query()
            ->where(function ($q) use ($query) {
                $q->where('student_number', 'like', "%{$query}%")
                  ->orWhere('lrn', 'like', "%{$query}%")
                  ->orWhere('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%");
            })
            ->with(['enrollments' => function ($q) use ($activeSchoolYear) {
                if ($activeSchoolYear) {
                    $q->where('school_year_id', $activeSchoolYear->id);
                }
                $q->with('sectionModel:id,name,grade_level');
            }])
            ->limit(20)
            ->get(['id', 'student_number', 'lrn', 'first_name', 'last_name', 'middle_name', 'sex']);

        $results = $students->map(function (Student $student) {
            $activeEnrollment = $student->enrollments->first();
            return [
                'id' => $student->id,
                'student_number' => $student->student_number,
                'lrn' => $student->lrn,
                'name' => trim("{$student->last_name}, {$student->first_name} {$student->middle_name}"),
                'grade_level' => $activeEnrollment?->sectionModel?->grade_level ?? 'Unassigned',
                'section_name' => $activeEnrollment?->sectionModel?->name ?? 'Unassigned',
                'profile_url' => route('student.profile', ['student' => $student->id]),
            ];
        });

        return response()->json(['data' => $results]);
    }

    public function export(Request $request, string $grade, Section $section)
    {
        if ($request->user()?->isTeacher()) {
            abort(403, 'Unauthorized access.');
        }

        $grade = (int) $grade;

        if ($grade < 1 || $grade > 12 || (int) $section->grade_level !== $grade) {
            abort(404);
        }

        $activeSchoolYear = SchoolYear::query()->where('is_active', true)->first();
        $schoolYearId = $request->input('school_year_id');
        $status = $request->input('status');
        $query = $request->input('query');
        $sort = $request->input('sort', 'last_name_asc');

        $students = Student::with(['enrollments.sectionModel', 'guardian'])
            ->whereHas('enrollments', function ($q) use ($grade, $schoolYearId, $activeSchoolYear, $section, $status) {
                $q->where('grade_level', (string) $grade)
                    ->where('section_id', $section->id);

                if ($schoolYearId && $schoolYearId !== 'all') {
                    $q->where('school_year_id', $schoolYearId);
                } elseif ($activeSchoolYear) {
                    $q->where('school_year_id', $activeSchoolYear->id);
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
            ->when($sort === 'last_name_desc', fn($q) => $q->orderByDesc('last_name')->orderByDesc('first_name'))
            ->when($sort === 'first_name_asc', fn($q) => $q->orderBy('first_name')->orderBy('last_name'))
            ->when($sort === 'first_name_desc', fn($q) => $q->orderByDesc('first_name')->orderByDesc('last_name'))
            ->when($sort === 'student_number_asc', fn($q) => $q->orderBy('student_number'))
            ->when($sort === 'student_number_desc', fn($q) => $q->orderByDesc('student_number'))
            ->when(!in_array($sort, ['last_name_desc', 'first_name_asc', 'first_name_desc', 'student_number_asc', 'student_number_desc'], true), function ($q) {
                $q->orderBy('last_name')->orderBy('first_name');
            })
            ->get();

        $selectedSchoolYear = null;
        if ($schoolYearId && $schoolYearId !== 'all') {
            $selectedSchoolYear = SchoolYear::find($schoolYearId);
        } else {
            $selectedSchoolYear = $activeSchoolYear;
        }

        $format = $request->input('format', 'sf1');
        $filePath = $this->exportService->export($students, $section, $selectedSchoolYear, $grade, $format);

        $safeSectionName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $section->name);
        $prefix = strtolower($format) === 'raw' ? 'Student_List_Raw' : 'SF1';
        $filename = "{$prefix}_Grade_{$grade}_{$safeSectionName}_" . now()->format('Ymd_His') . ".xlsx";

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPayload($request);

        if ($request->user()?->isTeacher()) {
            $teacher = $request->user()->teacher;
            abort_unless($teacher, 403, 'Teacher profile not found.');

            $teachingSectionIds = TeachingAssignment::query()
                ->where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->pluck('section_id');

            $advisedSectionIds = Section::query()
                ->where('advisor_id', $teacher->id)
                ->where('status', 'active')
                ->pluck('id');

            $teacherSectionIds = $teachingSectionIds
                ->concat($advisedSectionIds)
                ->unique()
                ->values()
                ->all();

            abort_unless(
                in_array((int) $validated['section_id'], $teacherSectionIds, true),
                403,
                'You can only add students to sections you handle.'
            );
        }

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
                    'age' => $validated['age'],
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
                    'status' => $validated['enrollment_status'],
                ],
            );

            try { StudentUpdated::dispatch('created', ['id' => $student->id, 'section_id' => $validated['section_id']]); } catch (\Throwable $e) {}
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Student creation error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $isDuplicate = str_contains(strtolower($e->getMessage()), 'unique') || str_contains(strtolower($e->getMessage()), 'duplicate') || ($e instanceof \Illuminate\Database\QueryException && (string)$e->getCode() === '23505');
            $msg = $isDuplicate
                ? 'A student with this student number or guardian email already exists. Please check the existing records.'
                : ($e instanceof \RuntimeException ? $e->getMessage() : 'Unable to create student record. Please check the provided information and try again.');
            return response()->json(['message' => $msg], 422);
        }

        // Notify School Admin via AdminActivityLog when a Teacher creates a student.
        if ($request->user()?->isTeacher()) {
            $section = Section::find($validated['section_id']);
            $sectionLabel = $section
                ? 'Grade ' . $section->grade_level . ' - ' . $section->name
                : 'Section ID ' . $validated['section_id'];

            AdminActivityLog::record(
                actor: $request->user(),
                action: 'Teacher Added Student',
                targetIdentifier: trim($validated['first_name'] . ' ' . $validated['last_name']),
                result: 'success',
                details: 'Student added to ' . $sectionLabel,
                targetType: 'Student',
                targetId: $student->id,
            );
        }

        return response()->json([
            'message' => 'Student, guardian, and enrollment created successfully',
            'student' => $student->load(['guardian', 'enrollments', 'qrCode']),
        ]);
    }

    public function update(Request $request, string $id)
    {
        abort_if($request->user()?->isTeacher(), 403, 'Teachers are not authorized to edit student records.');

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
                    'age' => $validated['age'],
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
                    'status' => $validated['enrollment_status'],
                ],
            );

            try { StudentUpdated::dispatch('updated', ['id' => $student->id, 'section_id' => $validated['section_id']]); } catch (\Throwable $e) {}
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Student update error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $isDuplicate = str_contains(strtolower($e->getMessage()), 'unique') || str_contains(strtolower($e->getMessage()), 'duplicate') || ($e instanceof \Illuminate\Database\QueryException && (string)$e->getCode() === '23505');
            $msg = $isDuplicate
                ? 'A student with this student number or guardian email already exists. Please check the existing records.'
                : ($e instanceof \RuntimeException ? $e->getMessage() : 'Unable to update student record. Please check the provided information and try again.');
            return response()->json(['message' => $msg], 422);
        }

        return response()->json([
            'message' => 'Student, guardian, and enrollment updated successfully',
            'student' => $student->load(['guardian', 'enrollments']),
        ]);
    }

    private function validatedPayload(Request $request, ?int $studentId = null): array
    {
        return $request->validate([
            'lrn' => [
                'required',
                'string',
                'size:12',
                'regex:/^\d{12}$/',
                'unique:students,student_number' . ($studentId ? ',' . $studentId : ''),
            ],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:255',
            'sex' => 'required|in:Male,Female,Other',
            'address' => 'required|string|max:500',
            'age' => 'required|integer|min:3|max:100',
            'status' => 'required|in:active,inactive,transferred,graduated,dropped',
            'name' => 'required|string|max:255',
            'relationship' => 'required|in:Mother,Father,Guardian',
            'contact_number' => ['nullable', 'regex:/^(\+639\d{9}|09\d{9})$/'],
            'email' => 'required|email|max:255',
            'school_year_id' => 'required|exists:school_years,id',
            'section_id' => 'required|exists:sections,id',
            'enrollment_status' => 'required|in:active,inactive,completed,dropped',
        ]);
    }

    public function destroy(string $id)
    {
        abort_if(auth()->user()?->isTeacher(), 403, 'Teachers are not authorized to delete student records.');

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Student record not found'
            ], 404);
        }

        try {
            $student->delete();

            return response()->json([
                'message' => 'Student record deleted successfully',
                'data' => $student
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to delete student ID ' . $id . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $isConstraint = str_contains(strtolower($e->getMessage()), 'foreign key') || str_contains(strtolower($e->getMessage()), 'constraint') || ($e instanceof \Illuminate\Database\QueryException && (string)$e->getCode() === '23503');
            $msg = $isConstraint
                ? 'This student cannot be deleted because they have associated attendance logs, grade records, or enrollments.'
                : 'The student record could not be deleted. Please try again.';

            return response()->json([
                'message' => $msg,
            ], 422);
        }
    }


    public function show($id)
    {
        return Student::with('guardian')->findOrFail($id);
    }
}
