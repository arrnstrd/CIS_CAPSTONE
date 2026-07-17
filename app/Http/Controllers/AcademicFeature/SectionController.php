<?php

namespace App\Http\Controllers\AcademicFeature;

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
            ->paginate(10)
            ->appends($request->only('search', 'status', 'grade_level'));

        $teachers = Teacher::with('user')
            ->where('status', 'active')
            ->get()
            ->sortBy('full_name')
            ->values();

       return view('admin-modules.management.sections', compact('sections', 'teachers'));
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
            'advisor_id' => ['nullable', 'exists:teachers,id'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate(
            $this->validationRules()
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
            $this->validationRules($section)
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
}
