<?php

namespace App\Http\Controllers\SchoolAdmin\QrStation;

use App\Http\Controllers\Controller;
use App\Services\QrSystem\QrScanService;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function __construct(private readonly QrScanService $qrScanService) {}

    public function scan(Request $request)
    {
        $t0 = microtime(true); // TEMP

        $validated = $request->validate([
            'code' => ['required_without:student_id', 'string'],
            'student_id' => ['required_without:code', 'integer'],
            'device_id' => ['nullable', 'string'],
        ]);

        $result = $this->qrScanService->processScan($validated);

        \Illuminate\Support\Facades\Log::debug('[PROFILE-SCAN] ms=' . round((microtime(true) - $t0) * 1000, 1)); // TEMP

        return $result;
    }
}
