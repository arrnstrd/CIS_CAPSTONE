<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Executive Attendance Analytics Report</title>
    <style>
        @page {
            margin: 10mm 12mm 12mm 12mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.35;
        }
        .header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header table {
            width: 100%;
        }
        .header h1 {
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 2px 0;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header .subtitle {
            font-size: 9px;
            color: #64748b;
            margin: 0;
        }
        .header .meta {
            text-align: right;
            font-size: 8.5px;
            color: #64748b;
        }

        /* ── Health Scorecard Banner ── */
        .health-card {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 12px;
        }
        .health-card table {
            width: 100%;
        }
        .health-score-val {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
        }
        .kpi-title {
            font-size: 7.5px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }
        .kpi-val {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }
        .kpi-sub {
            font-size: 7.5px;
            color: #64748b;
        }

        /* ── Sections ── */
        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
            margin-top: 10px;
            margin-bottom: 6px;
        }

        /* ── Tables ── */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.data-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
            text-align: left;
        }
        table.data-table td {
            border: 1px solid #e2e8f0;
            padding: 4px 6px;
            font-size: 8.5px;
            color: #334155;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* ── Badges ── */
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
        }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-info { background-color: #e0e7ff; color: #3730a3; }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 7.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            text-align: center;
        }
    </style>
</head>
<body>

    @php
        $a = $analytics;
    @endphp

    {{-- Header --}}
    <div class="header">
        <table>
            <tr>
                <td>
                    <h1>Executive Attendance Flow Report</h1>
                    <div class="subtitle">Decision-Support Analytics & Operational Directives</div>
                </td>
                <td class="meta">
                    <strong>Report Period:</strong> {{ $dateRangeLabel }}<br>
                    <strong>Generated:</strong> {{ now()->format('M d, Y h:i A') }}
                </td>
            </tr>
        </table>
    </div>

    {{-- Executive Summary Card --}}
    <div class="health-card">
        <table>
            <tr>
                <td style="width: 25%; padding: 2px 6px;">
                    <div class="kpi-title">Students Attending</div>
                    <div class="kpi-val">{{ number_format($a['uniqueStudentsIn']) }}</div>
                    <div class="kpi-sub">{{ number_format($a['totalScans']) }} total scans</div>
                </td>
                <td style="width: 25%; padding: 2px 6px; border-left: 1px solid #e2e8f0;">
                    <div class="kpi-title">Attendance Rate</div>
                    <div class="kpi-val">{{ $a['attendanceRate'] !== null ? $a['attendanceRate'] . '%' : 'N/A' }}</div>
                    <div class="kpi-sub">{{ $a['uniqueStudentsIn'] }} of {{ number_format($a['expectedStudents']) }} active</div>
                </td>
                <td style="width: 25%; padding: 2px 6px; border-left: 1px solid #e2e8f0;">
                    <div class="kpi-title">On-Time Arrivals</div>
                    <div class="kpi-val">{{ $a['onTimeRate'] !== null ? $a['onTimeRate'] . '%' : 'N/A' }}</div>
                    <div class="kpi-sub">{{ $a['lateCount'] }} late arrival(s)</div>
                </td>
                <td style="width: 25%; padding: 2px 6px; border-left: 1px solid #e2e8f0;">
                    <div class="kpi-title">Exit Integrity</div>
                    <div class="kpi-val">{{ $a['checkoutIntegrityRate'] }}%</div>
                    <div class="kpi-sub">{{ $a['missingOutCount'] }} unrecorded exit(s)</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- 1. Prescriptive Action Plan --}}
    <div class="section-title">1. Prioritized Administrative Action Plan (Prescriptive Directives)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20%;">Category & Priority</th>
                <th style="width: 50%;">Findings & Context</th>
                <th style="width: 30%;">Recommended Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($a['prescriptiveActions'] as $action)
            <tr>
                <td>
                    <strong>{{ $action['category'] }}</strong><br>
                    <span class="badge badge-{{ $action['priority_tone'] }}">{{ $action['priority'] }}</span>
                </td>
                <td>
                    <strong>{{ $action['title'] }}</strong><br>
                    <span style="color: #475569;">{{ $action['description'] }}</span>
                </td>
                <td>
                    <strong style="color: #0f172a;">{{ $action['action_label'] }}</strong>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 2. Diagnostic Findings --}}
    <div class="section-title">2. Operational Diagnostics & Attendance Congestion (Why it Happened)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 33%;">Peak Surge Window</th>
                <th style="width: 33%;">Scanner Velocity</th>
                <th style="width: 34%;">Average Timing Diagnostics</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $a['rushWindow'] ?? 'Even Flow' }}</strong><br>
                    <span class="kpi-sub">Highest traffic period</span>
                </td>
                <td>
                    <strong>{{ $a['rushVelocity'] ? $a['rushVelocity'] . ' scans / min' : 'Normal velocity' }}</strong><br>
                    <span class="kpi-sub">{{ $a['rushVelocity'] > 15 ? 'Scanner bottleneck detected' : 'Acceptable queue rate' }}</span>
                </td>
                <td>
                    Avg IN: <strong>{{ $a['avgArrival'] ?? 'N/A' }}</strong> &nbsp;|&nbsp;
                    Avg OUT: <strong>{{ $a['avgDeparture'] ?? 'N/A' }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- 3. Multiple Late Arrivals Registry --}}
    @if ($a['frequentLateStudents']->isNotEmpty())
    <div class="section-title">3. Multiple Late Arrivals Registry (&ge; 2 Occurrences)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Student ID</th>
                <th>Grade & Section</th>
                <th style="text-align: center;">Late Entries</th>
                <th style="text-align: center;">Personal Late %</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($a['frequentLateStudents'] as $st)
            <tr>
                <td><strong>{{ $st['student_name'] }}</strong></td>
                <td>{{ $st['student_number'] }}</td>
                <td>Grade {{ $st['grade_level'] }} — {{ $st['section_name'] }}</td>
                <td style="text-align: center;"><strong>{{ $st['late_count'] }}</strong></td>
                <td style="text-align: center;">{{ $st['personal_late_rate'] }}%</td>
                <td><span class="badge badge-{{ $st['badge_tone'] ?? 'warning' }}">{{ $st['badge_label'] ?? ($st['late_count'] . ' Late Entries') }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- 4. Section-by-Section Performance Breakdown --}}
    @if ($a['sectionData']->isNotEmpty())
    <div class="section-title">4. Section Attendance & Tardiness Performance Matrix</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Section</th>
                <th style="text-align: right;">Unique Students</th>
                <th style="text-align: right;">Total Scans</th>
                <th style="text-align: right;">Late Scans</th>
                <th style="text-align: right;">Late Rate</th>
                <th style="text-align: right;">Missing OUT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($a['sectionData'] as $sd)
            <tr>
                <td><strong>{{ $sd['name'] }}</strong></td>
                <td style="text-align: right;">{{ $sd['students'] }}</td>
                <td style="text-align: right;">{{ number_format($sd['total_scans']) }}</td>
                <td style="text-align: right;">
                    @if ($sd['late_count'] > 0)
                        <span class="badge badge-warning">{{ $sd['late_count'] }}</span>
                    @else
                        0
                    @endif
                </td>
                <td style="text-align: right;"><strong>{{ $sd['late_rate'] }}%</strong></td>
                <td style="text-align: right;">
                    @if ($sd['missing_out'] > 0)
                        <span class="badge badge-danger">{{ $sd['missing_out'] }}</span>
                    @else
                        0
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        Confidential — School Administration Decision Support Report &bull; Page 1 of 1
    </div>

</body>
</html>
