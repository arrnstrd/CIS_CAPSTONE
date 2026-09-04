<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gate Scan Attendance Logs</title>
    <style>
        @page {
            margin: 8mm 10mm 10mm 10mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            line-height: 1.3;
        }
        .header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .header table {
            width: 100%;
        }
        .header h1 {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header .meta {
            text-align: right;
            font-size: 8px;
            color: #64748b;
        }
        table.log-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.log-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
            text-align: left;
        }
        table.log-table td {
            border: 1px solid #e2e8f0;
            padding: 3.5px 5px;
            font-size: 8px;
            color: #334155;
        }
        table.log-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge-in { background-color: #d1fae5; color: #065f46; font-weight: bold; padding: 1px 3px; border-radius: 2px; }
        .badge-out { background-color: #dbeafe; color: #1e40af; font-weight: bold; padding: 1px 3px; border-radius: 2px; }
        .badge-late { background-color: #fef3c7; color: #92400e; font-weight: bold; padding: 1px 3px; border-radius: 2px; }
        .badge-flag { background-color: #fee2e2; color: #991b1b; font-weight: bold; padding: 1px 3px; border-radius: 2px; }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <h1>Gate Attendance & Scan Logs</h1>
                    <div style="font-size: 8px; color: #64748b;">Period: {{ $dateRangeLabel }} &bull; Total Logs: {{ number_format($logs->count()) }}</div>
                </td>
                <td class="meta">
                    Printed: {{ now()->format('M d, Y h:i A') }}
                </td>
            </tr>
        </table>
    </div>

    <table class="log-table">
        <thead>
            <tr>
                <th style="width: 25%;">Student Name</th>
                <th style="width: 12%;">Student ID</th>
                <th style="width: 18%;">Grade & Section</th>
                <th style="width: 10%;">Date</th>
                <th style="width: 8%;">Scan</th>
                <th style="width: 10%;">Time</th>
                <th style="width: 9%;">Session</th>
                <th style="width: 8%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                @php
                    $student = $log->enrollment?->student;
                    $section = $log->enrollment?->section;
                    $studentName = trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? '')) ?: '-';
                    $flagTypes = $log->flagged_scans->pluck('flag_type')->filter()->unique();
                @endphp
                <tr>
                    <td><strong>{{ $studentName }}</strong></td>
                    <td>{{ $student?->student_number ?? '-' }}</td>
                    <td>Grade {{ $section?->grade_level ?? '-' }} — {{ $section?->name ?? '-' }}</td>
                    <td>{{ $log->scan_time?->format('Y-m-d') }}</td>
                    <td>
                        @if ($log->scan_type === 'IN')
                            <span class="badge-in">IN</span>
                        @else
                            <span class="badge-out">OUT</span>
                        @endif
                    </td>
                    <td>{{ $log->scan_time?->format('h:i A') }}</td>
                    <td>{{ $log->session_type ? ucfirst(str_replace('_', ' ', $log->session_type)) : '-' }}</td>
                    <td>
                        @if ($flagTypes->contains('late_arrival'))
                            <span class="badge-late">Late</span>
                        @elseif ($flagTypes->isNotEmpty())
                            <span class="badge-flag">Review</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #94a3b8; padding: 12px;">No scan logs found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
