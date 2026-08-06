<?php

namespace App\Http\Controllers\QrSystemFeature\TeachingAssignment;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeachingAssignmentRequest;
use App\Services\TeachingAssignmentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class TeachingAssignmentController extends Controller
{
    public function __construct(private TeachingAssignmentService $teachingAssignmentService)
    {
    }

    public function index(Request $request)
    {
        $teachingAssignments = $this->teachingAssignmentService->list($request);

        return response()->json($teachingAssignments);
    }

    public function show(string $id)
    {
        try {
            $assignment = $this->teachingAssignmentService->find((int) $id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Teaching assignment not found'], 404);
        }

        return response()->json(['data' => $assignment]);
    }

    public function store(TeachingAssignmentRequest $request)
    {
        $result = $this->teachingAssignmentService->create($request->validated());

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 409);
        }

        return response()->json([
            'message' => 'Teaching assignment successfully created',
            'data' => $result['data'],
        ], 201);
    }

    public function update(TeachingAssignmentRequest $request, string $id)
    {
        try {
            $result = $this->teachingAssignmentService->update((int) $id, $request->validated());
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Teaching assignment not found'], 404);
        }

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 409);
        }

        return response()->json([
            'message' => 'Teaching assignment successfully updated',
            'data' => $result['data'],
        ]);
    }

    public function destroy(string $id)
    {
        try {
            $result = $this->teachingAssignmentService->delete((int) $id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Teaching assignment not found'], 404);
        }

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 409);
        }

        return response()->json([
            'message' => 'Teaching assignment successfully deleted',
            'data' => $result['data'],
        ]);
    }
}
