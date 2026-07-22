<?php

namespace App\Services;

use App\Models\TeachingAssignment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TeachingAssignmentService
{
    public function list(Request $request): LengthAwarePaginator
    {
        $query = TeachingAssignment::query()
            ->with(['teacher', 'subject', 'section', 'schoolYear']);

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->integer('teacher_id'));
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->integer('section_id'));
        }

        if ($request->filled('school_year_id')) {
            $query->where('school_year_id', $request->integer('school_year_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $query
            ->orderBy('school_year_id')
            ->orderBy('section_id')
            ->paginate(20)
            ->withQueryString();
    }

    /**
     * @return array{success: bool, data?: TeachingAssignment, message?: string}
     */
    public function create(array $validatedData): array
    {
        try {
            $assignment = TeachingAssignment::create($validatedData);

            return ['success' => true, 'data' => $assignment];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => 'A teaching assignment already exists for this teacher, subject, section, and school year',
            ];
        }
    }

    /**
     * @return array{success: bool, data?: TeachingAssignment, message?: string}
     */
    public function update(int $id, array $validatedData): array
    {
        $assignment = TeachingAssignment::findOrFail($id);

        try {
            $assignment->update($validatedData);
            $assignment->refresh();

            return ['success' => true, 'data' => $assignment];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => 'Another teaching assignment already exists for this teacher, subject, section, and school year',
            ];
        }
    }

    /**
     * Plain delete, relying on the existing DB foreign key constraints
     * (e.g. RoomAttendance.teaching_assignment_id is restrictOnDelete)
     * to prevent deletion when related records exist.
     *
     * No application-level "has related records, block deletion, use
     * status=inactive instead" guard is implemented here yet — per
     * Arianne, that behavior is intended for later, during the system
     * hardening phase (likely alongside SoftDeletes), and should not be
     * built now. This keeps deletion behavior exactly as the existing
     * project convention already provides via FK constraints.
     *
     * @return array{success: bool, data?: TeachingAssignment, message?: string}
     */
    public function delete(int $id): array
    {
        $assignment = TeachingAssignment::findOrFail($id);

        try {
            DB::transaction(fn () => $assignment->delete());

            return ['success' => true, 'data' => $assignment];
        } catch (QueryException $e) {
            return [
                'success' => false,
                'message' => 'This teaching assignment cannot be deleted because related records reference it.',
            ];
        }
    }

    public function find(int $id): TeachingAssignment
    {
        return TeachingAssignment::with(['teacher', 'subject', 'section', 'schoolYear'])
            ->findOrFail($id);
    }
}
