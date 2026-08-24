<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImportDataController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;
        return view('teacher-modules.utilities.import-data');
    }
}