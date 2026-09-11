<?php

namespace App\Http\Controllers\SchoolAdmin\Emails;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');
        $scan_type = $request->input('scan_type');
        $status = $request->input('status');
        $dateFilter = $request->input('date_filter', 'today');
        $customStartDate = $request->input('custom_start_date');
        $customEndDate = $request->input('custom_end_date');

        // Build the base query with eager loading
        $baseQuery = EmailLog::with('student');

        // Apply date filtering based on the selected filter
        $baseQuery = $this->applyDateFilter($baseQuery, $dateFilter, $customStartDate, $customEndDate);

        // Apply search query
        $baseQuery = $baseQuery->when($query, function ($q) use ($query) {
            $q->whereHas('student', function ($q) use ($query) {
                $q->where('student_number', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%");
            })
                ->orWhere('email', 'like', "%{$query}%");
        });

        // Apply scan type filter
        $baseQuery = $baseQuery->when($scan_type && $scan_type !== 'all', function ($q) use ($scan_type) {
            $q->where('scan_type', $scan_type);
        });

        // Apply status filter
        $baseQuery = $baseQuery->when($status && $status !== 'all', function ($q) use ($status) {
            $q->where('status', $status);
        });

        // Get statistics from the complete filtered dataset before pagination
        $emailCounts = $this->getFilteredStatistics($baseQuery);

        // Apply sorting and pagination
        $emailLogs = $baseQuery->orderBy('last_attempt_at', 'desc')
            ->orderBy('student_id')
            ->paginate(15)
            ->withQueryString();

        return view('pov.school-admin.emails.emails', compact(
            'emailLogs',
            'emailCounts',
            'dateFilter',
            'customStartDate',
            'customEndDate'
        ));
    }

    /**
     * Apply date filtering to the query based on the selected filter
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $dateFilter
     * @param string|null $customStartDate
     * @param string|null $customEndDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyDateFilter($query, $dateFilter, $customStartDate = null, $customEndDate = null)
    {
        return $query->when(true, function ($q) use ($dateFilter, $customStartDate, $customEndDate) {
            switch ($dateFilter) {
                case 'today':
                    return $q->todayOnly();

                case 'yesterday':
                    return $q->yesterdayOnly();

                case 'week':
                    return $q->thisWeekOnly();

                case 'last_7_days':
                    return $q->last7Days();

                case 'month':
                    return $q->where('created_at', '>=', now()->subMonth());

                case 'custom':
                    if ($customStartDate && $customEndDate) {
                        return $q->filterByDateRange($customStartDate, $customEndDate);
                    }
                    return $q->todayOnly();

                default:
                    return $q->todayOnly();
            }
        });
    }

    /**
     * Get statistics from the filtered query without applying pagination
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return array
     */
    private function getFilteredStatistics($query)
    {
        $queryClone = clone $query;

        return [
            'total' => $queryClone->count(),
            'sent' => (clone $query)->where('status', 'sent')->count(),
            'failed' => (clone $query)->where('status', 'failed')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'email' => ['required', 'string'],
            'scan_type' => ['required', 'in:IN,OUT'],
            'status' => ['required', 'in:pending,sent,failed']
        ]);

        $emailLog = EmailLog::create($data);

        return response()->json([
            'message' => 'Email log created successfully',
            'data' => $emailLog
        ]);
    }


    //email logic
    private function resendEmail($emailLog)
    {
        try {
            Mail::raw(
                "Your child  {$emailLog->student->first_name} gate attendance recorded at " . now(),
                function ($message) use ($emailLog) {
                    $message->to($emailLog->email)
                        ->subject('CIS Gate Scan Notification');
                }
            );

            $emailLog->update([
                'status' => 'sent',
                'attempt_count' => ($emailLog->attempt_count ?? 0) + 1
            ]);

            return true;
        } catch (\Exception $e) {

            $emailLog->update([
                'status' => 'failed',
                'attempt_count' => ($emailLog->attempt_count ?? 0) + 1
            ]);
            return false;
        }
    }


    // one - resend
    public function retry($id)
    {
        $emailLog = EmailLog::find($id);

        if (!$emailLog) {
            return response()->json([
                'message' => 'Email log not found'
            ], 404);
        }

        $this->resendEmail($emailLog);

        return response()->json([
            'message' => 'Retry attempted',
            'data' => $emailLog
        ]);
    }

    //bulk - all failed
    public function retryAll()
    {
        $failedLogs = EmailLog::where('status', 'failed')->get();

        $success = 0;
        $failed = 0;

        foreach ($failedLogs as $log) {

            // limit retries
            if (($log->attempt_count ?? 0) >= 4) {
                continue;
            }

            $result = $this->resendEmail($log);

            if ($result) {
                $success++;
            } else {
                $failed++;
            }
        }

        return response()->json([
            'message' => 'Retry process completed',
            'success_count' => $success,
            'failed_count' => $failed
        ]);
    }
}
