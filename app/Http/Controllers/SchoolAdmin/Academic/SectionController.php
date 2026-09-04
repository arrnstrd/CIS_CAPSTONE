<?php

namespace App\Http\Controllers\SchoolAdmin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SectionController extends Controller
{
    public static function sectionIndexQuery(?string $search = null, ?string $status = null, $gradeLevel = null): Builder
    {
        return Section::with('advisor.user')
            ->filterGradeLevel($gradeLevel)
            ->filterStatus($status)
            ->search($search)
            ->withCount('students')
            ->orderBy('grade_level')
            ->orderBy('name');
    }

    public function index(Request $request)
    {
        $sections = self::sectionIndexQuery(
            $request->query('search'),
            $request->query('status'),
            $request->query('grade_level')
        )
            ->paginate(15)
            ->appends($request->only('search', 'status', 'grade_level'));

        $teachers = Teacher::with('user')
            ->where('status', 'active')
            ->get()
            ->sortBy('full_name')
            ->values();

        return view('pov.school-admin.academic.sections', compact('sections', 'teachers'));
    }

    // for validation rules for the store and update
    private function validationRules(?Section $section = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:40',
                Rule::unique('sections')
                    ->ignore($section?->id)
                    ->where(fn($query) => $query
                        ->where('level', request('level'))
                        ->where('grade_level', request('grade_level')))
            ],
            'level' => ['required', 'in:elementary,highschool,senior_high_school'],
            'grade_level' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {

                    $level = request('level');

                    $isValid = match ($level) {
                        'elementary' => $value >= 1 && $value <= 6,
                        'highschool' => $value >= 7 && $value <= 10,
                        'senior_high_school' => $value >= 11 && $value <= 12,
                        default => false,
                    };

                    if (!$isValid) {
                        $fail('The selected grade level is invalid for the chosen level.');
                    }
                }
            ],
            'session_type' => ['required', 'in:morning,afternoon,whole_day'],
            'advisor_id' => [
                'nullable',
                'exists:teachers,id',
                Rule::unique('sections', 'advisor_id')
                    ->ignore($section?->id)
                    ->whereNotNull('advisor_id'),
            ],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'advisor_id.unique' => 'The selected teacher is already assigned as an adviser to another section.',
        ];
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate(
            $this->validationRules(),
            $this->validationMessages()
        );

        try {
            $section = Section::create($validatedData);

            return response()->json([
                'message' => 'Section created successfully',
                'data' => $section
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Unable to create section.',
            ], 422);
        }
    }

    public function update(Request $request, string $id)
    {
        $section = Section::findOrFail($id);

        $validatedData = $request->validate(
            $this->validationRules($section),
            $this->validationMessages()
        );

        try {
            $section->update($validatedData);

            return response()->json([
                'message' => 'Section updated successfully',
                'data' => $section->fresh()->load('advisor')
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Unable to update section.',
            ], 422);
        }
    }

    public function destroy(string $id)
    {
        $section = Section::findOrFail($id);

        if ($section->status === 'inactive') {
            return response()->json([
                'message' => 'Section is already inactive.'
            ], 422);
        }

        try {
            $section->update([
                'status' => 'inactive'
            ]);

            return response()->json([
                'message' => 'Section archived successfully',
                'data' => $section->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to archive the section',
            ], 500);
        }
    }

    public function restore(string $id)
    {
        $section = Section::findOrFail($id);

        if ($section->status === 'active') {
            return response()->json([
                'message' => 'Section is already active.'
            ], 422);
        }

        try {
            $section->update([
                'status' => 'active'
            ]);

            return response()->json([
                'message' => 'Section restored successfully',
                'data' => $section->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to restore the section',
            ], 500);
        }
    }

    public function forceDelete(Request $request, string $id)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $request->user()->password)) {
            return response()->json([
                'message' => 'The provided password does not match our records.',
                'errors' => [
                    'password' => ['The provided password does not match our records.']
                ]
            ], 422);
        }

        $section = Section::findOrFail($id);

        try {
            $section->delete();

            return response()->json([
                'message' => 'Section permanently deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to permanently delete section. It may have associated records (e.g. enrollments or teaching assignments).',
            ], 500);
        }
    }
}
