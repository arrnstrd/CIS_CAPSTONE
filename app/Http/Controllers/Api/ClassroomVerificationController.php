<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QrSystem\ClassroomVerificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClassroomVerificationController extends Controller
{
    public function __construct(private ClassroomVerificationService $service) {}

    public function roster(Request $request, int $teachingAssignmentId)
    {
        try {
            return response()->json([
                'data' => $this->service->getRoster($teachingAssignmentId),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Teaching assignment not found.'], 404);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Classroom verification roster error: ' . $e->getMessage());
            return response()->json(['message' => 'Unable to load class roster. Please try again.'], 500);
        }
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
            'teaching_assignment_id' => ['required', 'integer', 'exists:teaching_assignments,id'],
            'status' => ['required', 'string', 'in:present,late,absent,not_in_classroom,excused'],
            'remarks' => ['nullable', 'string'],
        ]);

        try {
            $verification = $this->service->verify($data, $request->user()->id);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Classroom verification record error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Attendance verification could not be recorded at this time. Please try again.',
            ], 500);
        }

        return response()->json([
            'data' => $verification->load('enrollment.student', 'teachingAssignment'),
        ]);
    }
}
