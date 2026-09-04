<?php

namespace App\Http\Controllers\SchoolAdmin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolYear;

class SettingsController extends Controller
{
    public function index()
    {
        return view('pov.school-admin.settings.settings', [
            'schoolYears' => SchoolYear::orderByDesc('is_active')
                ->orderBy('school_year', 'desc')
                ->get(),
        ]);
    }
}