<?php

namespace App\Services\Grading;

use App\Models\AttendanceLog;
use App\Models\AttendanceVerification;
use App\Models\Enrollment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceReportService
{
    /**
     * Get monthly attendance data for a student.
     * 
     * @param Enrollment $enrollment The student enrollment
     * @param int $schoolYearId The school year ID
     * @return array Monthly attendance data with class days, present days, absent days, and fallback counts
     */
    public function getMonthlyAttendance(Enrollment $enrollment, int $schoolYearId): array
    {
        $schoolYear = $this->getSchoolYearDates($schoolYearId);
        
        // Get all distinct school days that have attendance records for this student's section
        $sectionClassDays = $this->getSectionClassDays($enrollment->section_id, $schoolYear);
        
        // Group by month and calculate attendance statistics
        $monthlyData = [];
        $totalFallbackDays = 0;
        
        foreach ($this->getSchoolMonths() as $monthName => $monthNumber) {
            $monthDays = $this->filterDaysByMonth($sectionClassDays, $monthNumber, $schoolYear['start_year']);
            
            $classDays = count($monthDays);
            $presentDays = 0;
            $absentDays = 0;
            $fallbackDays = 0;
            
            foreach ($monthDays as $day) {
                $studentStatus = $this->getStudentAttendanceStatus($enrollment->id, $day);
                $isFallback = false;
                
                if ($studentStatus === AttendanceVerification::STATUS_PRESENT) {
                    $presentDays++;
                } elseif ($studentStatus === AttendanceVerification::STATUS_ABSENT) {
                    $absentDays++;
                } elseif ($studentStatus === AttendanceVerification::STATUS_LATE) {
                    // Late counts as present for attendance reporting
                    $presentDays++;
                } elseif ($studentStatus === AttendanceVerification::STATUS_EXCUSED) {
                    // Excused counts as present for attendance reporting
                    $presentDays++;
                } elseif ($studentStatus === AttendanceVerification::STATUS_NOT_IN_CLASSROOM) {
                    // Not in classroom counts as absent for attendance reporting
                    $absentDays++;
                } elseif ($studentStatus === AttendanceVerification::STATUS_NO_DATA) {
                    // Fallback: derive from attendance logs
                    $derivedStatus = $this->deriveAttendanceFromLogs($enrollment->id, $day);
                    if ($derivedStatus === 'present') {
                        $presentDays++;
                    } elseif ($derivedStatus === 'absent') {
                        $absentDays++;
                    }
                    $isFallback = true;
                }
                
                if ($isFallback) {
                    $fallbackDays++;
                }
            }
            
            $monthlyData[$monthName] = [
                'class_days' => $classDays,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'fallback_days' => $fallbackDays,
            ];
            
            $totalFallbackDays += $fallbackDays;
        }
        
        // Add totals row
        $totalClassDays = array_sum(array_column($monthlyData, 'class_days'));
        $totalPresentDays = array_sum(array_column($monthlyData, 'present_days'));
        $totalAbsentDays = array_sum(array_column($monthlyData, 'absent_days'));
        $totalFallbackDays = array_sum(array_column($monthlyData, 'fallback_days'));
        
        $monthlyData['TOTAL'] = [
            'class_days' => $totalClassDays,
            'present_days' => $totalPresentDays,
            'absent_days' => $totalAbsentDays,
            'fallback_days' => $totalFallbackDays,
        ];
        
        return $monthlyData;
    }
    
    /**
     * Get school year date range.
     */
    private function getSchoolYearDates(int $schoolYearId): array
    {
        $schoolYear = \App\Models\SchoolYear::find($schoolYearId);
        if (!$schoolYear) {
            throw new \Exception("School year not found");
        }
        
        // Assuming school year format like "2023-2024"
        $years = explode('-', $schoolYear->school_year);
        $startYear = (int)$years[0];
        $endYear = (int)$years[1];
        
        return [
            'start_year' => $startYear,
            'end_year' => $endYear,
            'start_date' => Carbon::create($startYear, 6, 1), // June 1
            'end_date' => Carbon::create($endYear, 4, 30), // April 30
        ];
    }
    
    /**
     * Get all distinct class days for a section within the school year.
     */
    private function getSectionClassDays(int $sectionId, array $schoolYear): array
    {
        // Get all distinct dates that have attendance records for this section
        $classDays = AttendanceLog::whereHas('enrollment', function($query) use ($sectionId) {
                $query->where('section_id', $sectionId);
            })
            ->whereBetween('scan_time', [$schoolYear['start_date'], $schoolYear['end_date']])
            ->distinct()
            ->orderBy('scan_time')
            ->pluck('scan_time')
            ->map(function($date) {
                return $date->format('Y-m-d');
            })
            ->toArray();
        
        return $classDays;
    }
    
    /**
     * Get school months in order (June through April).
     */
    private function getSchoolMonths(): array
    {
        return [
            'Jun' => 6,
            'Jul' => 7,
            'Aug' => 8,
            'Sep' => 9,
            'Oct' => 10,
            'Nov' => 11,
            'Dec' => 12,
            'Jan' => 1,
            'Feb' => 2,
            'Mar' => 3,
            'Apr' => 4,
        ];
    }
    
    /**
     * Filter days by month.
     */
    private function filterDaysByMonth(array $days, int $monthNumber, int $startYear): array
    {
        return array_filter($days, function($day) use ($monthNumber, $startYear) {
            $date = Carbon::parse($day);
            // For January-April, use the end year
            $year = ($monthNumber >= 1 && $monthNumber <= 4) ? $startYear + 1 : $startYear;
            return $date->month === $monthNumber && $date->year === $year;
        });
    }
    
    /**
     * Get attendance status for a specific student on a specific day.
     * Returns: present, absent, or no_data (for fallback processing)
     */
    private function getStudentAttendanceStatus(int $enrollmentId, string $day): ?string
    {
        $date = Carbon::parse($day);
        
        // Get the latest verification for this student on this day
        $verification = AttendanceVerification::where('enrollment_id', $enrollmentId)
            ->whereDate('attendance_date', $date)
            ->orderBy('verified_at', 'desc')
            ->first();
        
        if ($verification) {
            return $verification->status;
        }
        
        // If no verification, check if there are any attendance logs for this day
        $hasLogs = AttendanceLog::where('enrollment_id', $enrollmentId)
            ->whereDate('scan_time', $date)
            ->exists();
        
        if (!$hasLogs) {
            return null; // No data for this day
        }
        
        // Return 'no_data' to trigger fallback processing
        return AttendanceVerification::STATUS_NO_DATA;
    }
    
    /**
     * Derive attendance status from raw attendance logs when no verification exists.
     * 
     * @param int $enrollmentId The student enrollment ID
     * @param string $day The date in Y-m-d format
     * @return string 'present' or 'absent'
     */
    private function deriveAttendanceFromLogs(int $enrollmentId, string $day): string
    {
        $date = Carbon::parse($day);
        
        // Check if the student has at least one IN scan on this day
        $hasInScan = AttendanceLog::where('enrollment_id', $enrollmentId)
            ->whereDate('scan_time', $date)
            ->where('scan_type', 'IN')
            ->exists();
        
        if ($hasInScan) {
            return 'present';
        }
        
        // If no IN scan, count as absent
        return 'absent';
    }
}