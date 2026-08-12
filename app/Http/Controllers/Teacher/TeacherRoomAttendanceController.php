<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class TeacherRoomAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;

        $dateFilter = $request->input('date_filter', 'today');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');
        $selectedSectionId = $request->input('section_id');
        $statusFilter = $request->input('status_filter', 'present'); // present | absent

        $sectionIds = TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->pluck('section_id');

        $sections = Section::whereIn('id', $sectionIds)->orderBy('name')->get();

        $scopedSectionIds = ($selectedSectionId && $sectionIds->contains((int) $selectedSectionId))
            ? [$selectedSectionId]
            : $sectionIds;

        $enrollmentIds = Enrollment::whereIn('section_id', $scopedSectionIds)
            ->where('status', 'active')
            ->pluck('id');

        if ($statusFilter === 'absent') {
            $presentQuery = AttendanceLog::whereIn('enrollment_id', $enrollmentIds);
            $presentQuery = $this->applyDateFilter($presentQuery, $dateFilter, $customStartDate, $customEndDate);
            $presentEnrollmentIds = $presentQuery->pluck('enrollment_id')->unique();

            $absentEnrollmentIds = $enrollmentIds->diff($presentEnrollmentIds);

            $absentEnrollments = Enrollment::with(['student', 'section'])
                ->whereIn('id', $absentEnrollmentIds)
                ->paginate(20)
                ->withQueryString();

            $roomAttendance = AttendanceLog::whereRaw('1 = 0')->paginate(20);

            return view('teacher-modules.room-attendance', compact(
                'roomAttendance', 'absentEnrollments', 'dateFilter', 'customStartDate',
                'customEndDate', 'sections', 'selectedSectionId', 'statusFilter'
            ));
        }

        $query = AttendanceLog::with([
                'enrollment.student',
                'enrollment.section',
            ])
            ->whereIn('enrollment_id', $enrollmentIds);

        $query = $this->applyDateFilter($query, $dateFilter, $customStartDate, $customEndDate);

        $roomAttendance = $query
            ->orderBy('scan_time', 'desc')
            ->paginate(20)
            ->withQueryString();

        $absentEnrollments = Enrollment::whereRaw('1 = 0')->paginate(20);

        return view('teacher-modules.room-attendance', compact(
            'roomAttendance', 'absentEnrollments', 'dateFilter', 'customStartDate',
            'customEndDate', 'sections', 'selectedSectionId', 'statusFilter'
        ));
    }

    private function applyDateFilter($query, $dateFilter, $customStartDate = null, $customEndDate = null)
    {
        switch ($dateFilter) {
            case 'yesterday':
                return $query->yesterdayOnly();

            case 'week':
                return $query->thisWeekOnly();

            case 'custom':
                if ($customStartDate && $customEndDate) {
                    return $query->filterByDateRange($customStartDate, $customEndDate);
                }
                return $query->todayOnly();

            case 'today':
            default:
                return $query->todayOnly();
        }
    }
}