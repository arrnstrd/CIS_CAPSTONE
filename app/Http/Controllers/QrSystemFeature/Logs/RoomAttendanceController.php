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
     * Scoped to the logged-in teacher, with search, classroom, and date filters.
     */
    public function index(Request $request)
    {
        $query = RoomAttendance::with([
            'teachingAssignment.subject',
            'teachingAssignment.section',
            'enrollment.student',
            'enrollment.section',
        ]);

        // Scope to the logged-in teacher's own classes
        // NOTE: assumes TeachingAssignment has a `teacher_id` column — adjust if named differently
        if (auth()->check()) {
            $query->whereHas('teachingAssignment', function ($q) {
                $q->where('teacher_id', auth()->id());
            });
        }

        // Search by student number or name
        $search = $request->query('query');
        if ($search) {
            $query->whereHas('enrollment.student', function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Filter by classroom/section
        $sectionId = $request->query('section_id');
        if ($sectionId) {
            $query->whereHas('enrollment.section', fn($q) => $q->where('id', $sectionId));
        }

        // Date filter
        $dateFilter = $request->query('date_filter', 'today');
        switch ($dateFilter) {
            case 'today':
                $query->whereDate('attendance_date', now()->toDateString());
                break;
            case 'week':
                $query->whereBetween('attendance_date', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('attendance_date', now()->month)
                      ->whereYear('attendance_date', now()->year);
                break;
            case 'custom':
                if ($request->query('custom_start_date') && $request->query('custom_end_date')) {
                    $query->whereBetween('attendance_date', [
                        $request->query('custom_start_date'),
                        $request->query('custom_end_date'),
                    ]);
                }
                break;
        }

        $roomAttendance = $query->orderBy('attendance_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Sections for the classroom filter dropdown, scoped to this teacher
        $sections = TeachingAssignment::with('section')
            ->where('teacher_id', auth()->id())
            ->get()
            ->pluck('section')
            ->filter()
            ->unique('id')
            ->values();

        return view('teacher-modules.room-attendance', [
            'roomAttendance' => $roomAttendance,
            'sections' => $sections,
            'dateFilter' => $dateFilter,
            'query' => $search,
            'sectionId' => $sectionId,
            'customStartDate' => $request->query('custom_start_date'),
            'customEndDate' => $request->query('custom_end_date'),
        ]);
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