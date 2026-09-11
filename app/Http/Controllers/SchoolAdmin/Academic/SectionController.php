<?php

namespace App\Http\Controllers\SchoolAdmin\Academic;

use App\Events\SectionUpdated;
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
        $activeSchoolYear = \App\Models\SchoolYear::query()->active()->first();
        
        return Section::with('advisor.user')
            ->filterGradeLevel($gradeLevel)
            ->filterStatus($status)
            ->search($search)
            ->withCount(['students' => function ($query) use ($activeSchoolYear) {
                if ($activeSchoolYear) {
                    $query->where('enrollments.school_year_id', $activeSchoolYear->id);
                }
            }])
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

            try {
                SectionUpdated::dispatch('created', $section->toArray());
            } catch (\Throwable $e) {
            }

            return response()->json([
                'message' => 'Section created successfully',
                'data' => $section
            ]);
        } catch (QueryException $e) {
            \Illuminate\Support\Facades\Log::error('Section store error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Unable to create section due to a database constraint or duplicate entry. Please verify inputs.',
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

            try {
                SectionUpdated::dispatch('updated', $section->toArray());
            } catch (\Throwable $e) {
            }

            return response()->json([
                'message' => 'Section updated successfully',
                'data' => $section->fresh()->load('advisor')
            ]);
        } catch (QueryException $e) {
            \Illuminate\Support\Facades\Log::error('Section update error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Unable to update section due to a database constraint or duplicate entry. Please verify inputs.',
            ], 422);
        }
    }

    public function updateAdvisor(Request $request, Section $section)
    {
        $validatedData = $request->validate([
            'advisor_id' => [
                'nullable',
                'exists:teachers,id',
                Rule::unique('sections', 'advisor_id')
                    ->ignore($section->id)
                    ->whereNotNull('advisor_id'),
            ],
        ], [
            'advisor_id.unique' => 'The selected teacher is already assigned as an adviser to another section.',
        ]);

        try {
            $section->update([
                'advisor_id' => $validatedData['advisor_id'] ?? null,
            ]);

            $section->load(['advisor.user']);

            try {
                SectionUpdated::dispatch('updated', $section->toArray());
            } catch (\Throwable $e) {
            }

            return response()->json([
                'message' => $section->advisor_id ? 'Section adviser assigned successfully.' : 'Section adviser unassigned successfully.',
                'data' => $section,
            ]);
        } catch (QueryException $e) {
            \Illuminate\Support\Facades\Log::error('Section adviser update error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Unable to update section adviser.',
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

            try {
                SectionUpdated::dispatch('deleted', $section->toArray());
            } catch (\Throwable $e) {
            }

            return response()->json([
                'message' => 'Section archived successfully',
                'data' => $section->fresh()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Section archive error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Failed to archive the section. Please try again.',
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
            \Illuminate\Support\Facades\Log::error('Section restore error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Failed to restore the section. Please try again.',
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
            \Illuminate\Support\Facades\Log::error('Section force delete error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Failed to permanently delete section. It may have associated records (e.g. enrollments or teaching assignments).',
            ], 500);
        }
    }

    /**
     * Bulk archive (soft delete) multiple sections.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:sections,id'],
        ]);

        $ids = $request->input('ids', []);

        $sections = Section::whereIn('id', $ids)
            ->where('status', 'active')
            ->get();

        $count = 0;
        foreach ($sections as $section) {
            $section->update(['status' => 'inactive']);
            try {
                SectionUpdated::dispatch('deleted', $section->toArray());
            } catch (\Throwable $e) {
            }
            $count++;
        }

        return response()->json([
            'message' => $count . ' section(s) archived successfully.',
            'affected' => $count,
        ]);
    }

    /**
     * Bulk restore multiple sections.
     */
    public function bulkRestore(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:sections,id'],
        ]);

        $ids = $request->input('ids', []);

        $sections = Section::whereIn('id', $ids)
            ->where('status', 'inactive')
            ->get();

        $count = 0;
        foreach ($sections as $section) {
            $section->update(['status' => 'active']);
            try {
                SectionUpdated::dispatch('restored', $section->toArray());
            } catch (\Throwable $e) {
            }
            $count++;
        }

        return response()->json([
            'message' => $count . ' section(s) restored successfully.',
            'affected' => $count,
        ]);
    }
}
