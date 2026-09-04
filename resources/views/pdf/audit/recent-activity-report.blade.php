<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Administrative Activity Report</title>
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
        .badge-denied { background-color: #fee2e2; color: #991b1b; font-weight: bold; padding: 1.5px 4px; border-radius: 2px; }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <h1>Administrative Activity Log</h1>
                    <div style="font-size: 8px; color: #64748b;">Period: {{ $dateRangeLabel }} &bull; Total Activities: {{ number_format($logs->count()) }}</div>
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
                <th style="width: 20%;">Actor</th>
                <th style="width: 25%;">Administrative Action</th>
                <th style="width: 18%;">Target Resource</th>
                <th style="width: 15%;">IP & Device</th>
                <th style="width: 12%;">Date & Time</th>
                <th style="width: 10%;">Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                @php
                    $actorName = $log->actor ? ($log->actor->first_name . ' ' . $log->actor->last_name) : ($log->actor_name ?? 'System');
                    $target = ($log->target_type ? $log->target_type . ': ' : '') . ($log->target_identifier ?? '-');
                @endphp
                <tr>
                    <td>
                        <strong>{{ $actorName }}</strong>
                        <div style="font-size: 7px; color: #64748b;">{{ $log->actor_email }}</div>
                    </td>
                    <td>
                        <strong>{{ $log->action }}</strong>
                        @if($log->details)
                            <div style="font-size: 7px; color: #64748b;">{{ $log->details }}</div>
                        @endif
                    </td>
                    <td>{{ $target }}</td>
                    <td>
                        <code>{{ $log->ip_address ?? '-' }}</code>
                        <div style="font-size: 7px; color: #64748b;">{{ $log->formatted_device }}</div>
                    </td>
                    <td>{{ $log->created_at ? \Illuminate\Support\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') : '-' }}</td>
                    <td>
                        @if($log->result === 'success')
                            <span class="badge-success">Success</span>
                        @else
                            <span class="badge-denied">Denied</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 12px;">No administrative activity events found for this range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
