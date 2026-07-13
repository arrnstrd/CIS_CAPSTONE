<?php

namespace App\Http\Controllers\QrSystemFeature\Logs;

use App\Http\Controllers\Controller;
use App\Http\Requests\QrSystem\Attendance\StoreRoomAttendanceRequest;
use App\Http\Requests\QrSystem\Attendance\UpdateRoomAttendanceRequest;
use App\Models\RoomAttendance;
use App\Models\TeachingAssignment;
use App\Services\QrSystem\RoomAttendanceService;
use Illuminate\Http\Request;

class RoomAttendanceController extends Controller
{
    protected RoomAttendanceService $roomAttendanceService;

    public function __construct(RoomAttendanceService $roomAttendanceService)
    {
        $this->roomAttendanceService = $roomAttendanceService;
    }

    /**
     * Display a listing of room attendance records.
     */
    public function index()
    {
        $roomAttendance = RoomAttendance::with(['teachingAssignment', 'enrollment.student', 'enrollment.section'])
            ->orderBy('attendance_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('qr-system.attendance.room-attendance.index', compact('roomAttendance'));
    }

    /**
     * Show the form for creating a new room attendance record.
     */
    public function create()
    {
        $teachingAssignments = TeachingAssignment::with(['subject', 'section', 'schoolYear'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('qr-system.attendance.room-attendance.create', compact('teachingAssignments'));
    }

    /**
     * Store a newly created room attendance record.
     */
    public function store(StoreRoomAttendanceRequest $request)
    {
        $roomAttendance = $this->roomAttendanceService->create($request->validated());
        return redirect()->route('room-attendance.index')
            ->with('success', 'Room attendance recorded successfully.');
    }

    /**
     * Display the specified room attendance record.
     */
    public function show(RoomAttendance $roomAttendance)
    {
        $roomAttendance->load(['teachingAssignment', 'enrollment.student', 'enrollment.section']);
        return view('qr-system.attendance.room-attendance.show', compact('roomAttendance'));
    }

    /**
     * Show the form for editing the specified room attendance record.
     */
    public function edit(RoomAttendance $roomAttendance)
    {
        $roomAttendance->load(['teachingAssignment', 'enrollment.student', 'enrollment.section']);
        return view('qr-system.attendance.room-attendance.edit', compact('roomAttendance'));
    }

    /**
     * Update the specified room attendance record.
     */
    public function update(UpdateRoomAttendanceRequest $request, RoomAttendance $roomAttendance)
    {
        $roomAttendance = $this->roomAttendanceService->update($roomAttendance, $request->validated());
        return redirect()->route('room-attendance.index')
            ->with('success', 'Room attendance updated successfully.');
    }

    /**
     * Remove the specified room attendance record.
     */
    public function destroy(RoomAttendance $roomAttendance)
    {
        $this->roomAttendanceService->delete($roomAttendance);
        return redirect()->route('room-attendance.index')
            ->with('success', 'Room attendance deleted successfully.');
    }

    /**
     * Display room attendance for a specific teaching assignment on a specific date.
     */
    public function byTeachingAssignmentAndDate(Request $request, TeachingAssignment $teachingAssignment)
    {
        $date = $request->query('date', now()->toDateString());
        $roomAttendance = $this->roomAttendanceService->getByTeachingAssignmentAndDate($teachingAssignment->id, $date);
        
        return view('qr-system.attendance.room-attendance.by-assignment-date', compact('teachingAssignment', 'date', 'roomAttendance'));
    }

    /**
     * Show the form for bulk creating room attendance for a class.
     */
    public function bulkCreateForm(TeachingAssignment $teachingAssignment)
    {
        $teachingAssignment->load(['section.enrollments.student']);
        $date = now()->toDateString();
        
        return view('qr-system.attendance.room-attendance.bulk-create', compact('teachingAssignment', 'date'));
    }

    /**
     * Bulk create room attendance records for a class.
     */
    public function bulkStore(Request $request, TeachingAssignment $teachingAssignment)
    {
        $request->validate([
            'attendance_date' => 'required|date',
            'attendance_data' => 'required|array',
            'attendance_data.*.enrollment_id' => 'required|exists:enrollments,id',
            'attendance_data.*.time_in' => 'required|date_format:H:i',
            'attendance_data.*.time_out' => 'nullable|date_format:H:i',
            'attendance_data.*.remarks' => 'nullable|string|max:255',
        ]);

        $roomAttendance = $this->roomAttendanceService->bulkCreate(
            $teachingAssignment->id,
            $request->attendance_date,
            $request->attendance_data
        );

        return redirect()->route('room-attendance.by-assignment-date', [
            'teachingAssignment' => $teachingAssignment->id,
            'date' => $request->attendance_date
        ])->with('success', "Bulk room attendance recorded successfully. {$roomAttendance->count()} records created.");
    }
}
