<?php

namespace App\Http\Controllers\QrSystemFeature\Scanner;

use App\Http\Controllers\Controller;
use App\Services\QrSystem\QrScanService;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function __construct(private readonly QrScanService $qrScanService) {}

    public function scan(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required_without:student_id', 'string'],
            'student_id' => ['required_without:code', 'integer'],
            'device_id' => ['nullable', 'string'],
        ]);

        return $this->qrScanService->processScan($validated);
    }
}
