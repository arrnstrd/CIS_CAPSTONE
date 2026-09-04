<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Audit Log Report</title>
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
            padding: 4.5px 6px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
            text-align: left;
        }
        table.log-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            font-size: 8px;
            color: #334155;
        }
        table.log-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; font-weight: bold; padding: 1.5px 4px; border-radius: 2px; }
        .badge-failed { background-color: #fee2e2; color: #991b1b; font-weight: bold; padding: 1.5px 4px; border-radius: 2px; }
        .badge-lock { background-color: #fef3c7; color: #92400e; font-weight: bold; padding: 1.5px 4px; border-radius: 2px; }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <h1>System Security Audit Log</h1>
                    <div style="font-size: 8px; color: #64748b;">Period: {{ $dateRangeLabel }} &bull; Total Audit Events: {{ number_format($logs->count()) }}</div>
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
                <th style="width: 25%;">User Account</th>
                <th style="width: 20%;">Email Attempted</th>
                <th style="width: 15%;">IP Address</th>
                <th style="width: 18%;">Device / Browser</th>
                <th style="width: 12%;">Date & Time</th>
                <th style="width: 10%;">Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                @php
                    $userName = $log->user ? ($log->user->first_name . ' ' . $log->user->last_name) : 'Guest / System';
                @endphp
                <tr>
                    <td><strong>{{ $userName }}</strong></td>
                    <td>{{ $log->email_attempted }}</td>
                    <td><code>{{ $log->ip_address ?? '-' }}</code></td>
                    <td>{{ $log->formatted_device }}</td>
                    <td>{{ $log->attempted_at ? \Illuminate\Support\Carbon::parse($log->attempted_at)->format('Y-m-d H:i:s') : '-' }}</td>
                    <td>
                        @if($log->status === 'success')
                            <span class="badge-success">Success</span>
                        @elseif($log->status === 'locked_out')
                            <span class="badge-lock">Locked Out</span>
                        @else
                            <span class="badge-failed">Failed</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 12px;">No security audit events found for this range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
