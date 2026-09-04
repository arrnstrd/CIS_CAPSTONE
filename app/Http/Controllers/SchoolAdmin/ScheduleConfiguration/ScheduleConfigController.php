<?php

namespace App\Http\Controllers\SchoolAdmin\ScheduleConfiguration;

use App\Http\Controllers\Controller;
use App\Models\ScheduleConfig;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ScheduleConfigController extends Controller
{
    public function index()
    {
        $scheduleConfigs = ScheduleConfig::orderBy('level')
            ->orderBy('session_type')
            ->get();

        return view('pov.school-admin.schedule-configuration.schedule-configuration', compact('scheduleConfigs'));
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'level' => ['required', 'in:elementary,hs,shs'],
            'session_type' => ['required', 'in:morning,afternoon,whole_day'],
            'in_start' => ['required', 'date_format:H:i'],
            'in_end' => ['required', 'date_format:H:i', 'after:in_start'],
            'late_threshold' => ['required', 'date_format:H:i'],
            'out_start' => ['required', 'date_format:H:i'],
            'out_end' => ['required', 'date_format:H:i', 'after:out_start'],
        ]);

        try {
            $schedule = ScheduleConfig::create($validatedData);

            return response()->json([
                'message' => 'Schedule configuration successfully created',
                'data' => $schedule
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Schedule already exists for this level and session type'
            ], 409);
        }
    }




    public function update(Request $request, string $id)
    {
        $schedule = ScheduleConfig::findOrFail($id);

        $validatedData = $request->validate([
            'level' => ['required', 'in:elementary,hs,shs'],
            'session_type' => ['required', 'in:morning,afternoon,whole_day'],
            'in_start' => ['required', 'date_format:H:i'],
            'in_end' => ['required', 'date_format:H:i', 'after:in_start'],
            'late_threshold' => ['required', 'date_format:H:i'],
            'out_start' => ['required', 'date_format:H:i'],
            'out_end' => ['required', 'date_format:H:i', 'after:out_start'],
        ]);

        $exists = ScheduleConfig::where('level', $validatedData['level'])
            ->where('session_type', $validatedData['session_type'])
            ->where('id', '!=', $id)
            ->first();

        if ($exists) {
            return response()->json([
                'message' => 'Another schedule configuration already exists'
            ], 409);
        }

        $schedule->update($validatedData);
        $schedule->refresh();

        return response()->json([
            'message' => 'Schedule configuration successfully updated',
            'data' => $schedule
        ]);
    }



    public function destroy(string $id)
    {
        $schedule = ScheduleConfig::findOrFail($id);

        $schedule->delete();

        return response()->json([
            'message' => 'Schedule configuration successfully deleted',
            'data' => $schedule
        ]);
    }
}
