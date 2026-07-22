<?php

namespace App\Http\Controllers\QrSystemFeature\ClassroomScanner;

use App\Exceptions\ClassroomScanRejectedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClassroomScanRequest;
use App\Services\ClassroomScanService;

class ClassroomScanController extends Controller
{
    public function __construct(private ClassroomScanService $classroomScanService)
    {
    }

    public function scan(ClassroomScanRequest $request)
    {
        try {
            $result = $this->classroomScanService->scan(
                $request->validated(),
                $request->user()->id
            );

            return response()->json($result);
        } catch (ClassroomScanRejectedException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->statusCode);
        }
    }
}
