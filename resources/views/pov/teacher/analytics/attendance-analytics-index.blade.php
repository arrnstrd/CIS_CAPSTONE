<x-layouts.teacher> <x-slot name="pageName"> <span class="page-title-icon"> <i class="fa-solid fa-chart-line"></i>
Attendance Analytics </span> </x-slot>


<x-slot name="subtitle">
    <span class="page-title-subtitle">Observed patterns between attendance records and student academic performance.</span>
</x-slot>

{{-- Analytics Sub-Navigation --}}
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('teacher.grading-system.analytics') }}"
       class="btn btn-sm {{ request()->routeIs('teacher.grading-system.analytics*') ? 'btn-primary text-white' : 'btn-outline-secondary' }}"
       style="border-radius: 20px; font-weight: 600; font-size: 0.82rem; padding: 5px 14px;">
        <i class="fa-solid fa-graduation-cap me-1"></i> Academic Analytics
    </a>

    <a href="{{ route('teacher.grading-system.attendance') }}"
       class="btn btn-sm {{ request()->routeIs('teacher.grading-system.attendance*') ? 'btn-primary text-white' : 'btn-outline-secondary' }}"
       style="border-radius: 20px; font-weight: 600; font-size: 0.82rem; padding: 5px 14px;">
        <i class="fa-solid fa-clipboard-user me-1"></i> Attendance Analytics
    </a>
        <a href="{{ route('teacher.grading-system.correlation') }}"
           class="btn btn-sm {{ request()->routeIs('teacher.grading-system.correlation*') ? 'btn-primary text-white' : 'btn-outline-secondary' }}"
           style="border-radius: 20px; font-weight: 600; font-size: 0.82rem; padding: 5px 14px;">
            <i class="fa-solid fa-chart-simple me-1"></i> Attendance vs Performance
        </a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('teacher.grading-system.attendance') }}" class="gs-filter-bar mb-3">
    <div class="row g-2 align-items-end">

        <div class="col-6 col-md-3">
            <label class="gs-filter-label">School Year</label>
            <select name="school_year_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All School Years</option>
                @foreach ($schoolYears as $schoolYear)
                    <option value="{{ $schoolYear->id }}"
                        @selected(request('school_year_id') == $schoolYear->id)>
                        {{ $schoolYear->school_year }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-md-3">
            <label class="gs-filter-label">Term</label>
            <select name="term" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Terms</option>
                @for ($t = 1; $t <= 3; $t++)
                    <option value="{{ $t }}" @selected(request('term') == $t)>
                        Term {{ $t }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-6 col-md-3">
            <label class="gs-filter-label">Grade Level</label>
            <select name="grade_level" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Levels</option>
                @foreach ($gradeLevels as $gl)
                    <option value="{{ $gl }}" @selected(request('grade_level') == $gl)>
                        Grade {{ $gl }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-md-3">
            <label class="gs-filter-label">Section</label>
            <select name="section_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Sections</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected(request('section_id') == $section->id)>
                        {{ $section->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-md-3">
            <label class="gs-filter-label">Subject</label>
            <select name="subject_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Subjects</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-md-3">
            <label class="gs-filter-label">Student</label>
            <select name="student_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Students</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected(request('student_id') == $student->id)>
                        {{ $student->full_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-md-3">
        </div>

    </div>

    @if (request()->hasAny([
        'school_year_id',
        'term',
        'grade_level',
        'section_id',
        'subject_id',
    ]))
        <div class="mt-2">
            <a href="{{ route('teacher.grading-system.attendance') }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-xmark me-1"></i>
                Clear Filters
            </a>
        </div>
    @endif
</form>

{{-- Attendance Summary Cards --}}
@if ($attendanceSummary)
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card">
                <p class="gs-grade-card-title">Overall Attendance</p>
                <p class="gs-grade-card-avg">{{ $attendanceSummary['overall_rate'] }}%</p>
                <span class="gs-status-badge {{ $attendanceSummary['overall_rate'] >= 85 ? 'gs-status-good' : 'gs-status-at_risk' }}">
                    {{ $attendanceSummary['overall_rate'] >= 85 ? 'Healthy' : 'Needs Attention' }}
                </span>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card">
                <p class="gs-grade-card-title">Present Days</p>
                <p class="gs-grade-card-avg text-success">
                    {{ number_format($attendanceSummary['present']) }}
                </p>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card">
                <p class="gs-grade-card-title">Late Days</p>
                <p class="gs-grade-card-avg text-warning">
                    {{ number_format($attendanceSummary['late']) }}
                </p>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card">
                <p class="gs-grade-card-title">Absences</p>
                <p class="gs-grade-card-avg text-danger">
                    {{ number_format($attendanceSummary['absent']) }}
                </p>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card">
                <p class="gs-grade-card-title">Excused</p>
                <p class="gs-grade-card-avg text-info">
                    {{ number_format($attendanceSummary['excused']) }}
                </p>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card">
                <p class="gs-grade-card-title">Not in Classroom</p>
                <p class="gs-grade-card-avg text-secondary">
                    {{ number_format($attendanceSummary['not_in_classroom']) }}
                </p>
            </div>
        </div>
    </div>
@endif

{{-- Section Attendance Overview --}}
@if ($sectionSummaries->isNotEmpty())
    <div class="gs-panel mb-3">
        <p class="gs-panel-title mb-3">Section Attendance Overview</p>

        <x-ui.table>
            <thead>
                <tr>
                    <th>Section</th>
                    <th>Grade Level</th>
                    <th>Students</th>
                    <th>Attendance Rate</th>
                    <th>Avg Grade</th>
                    <th>Present Days</th>
                    <th>Late Days</th>
                    <th>Absences</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($sectionSummaries as $section)
                    <tr>
                        <td class="fw-semibold">{{ $section['name'] }}</td>
                        <td>Grade {{ $section['grade_level'] }}</td>
                        <td>{{ $section['student_count'] }}</td>

                        <td>
                            <span class="badge {{ $section['attendance_rate'] >= 85 ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $section['attendance_rate'] }}%
                            </span>
                        </td>

                        <td>
                            {{ $section['avg_grade'] !== null
                                ? number_format($section['avg_grade'], 1)
                                : 'N/AÂ' }}
                        </td>

                        <td class="text-success">
                            {{ number_format($section['present']) }}
                        </td>

                        <td class="text-warning">
                            {{ number_format($section['late']) }}
                        </td>

                        <td class="text-danger">
                            {{ number_format($section['absent']) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    </div>
@endif

{{-- Student Attendance Rankings --}}
<div class="row g-3 mb-3">

    {{-- Most Absent --}}
    <div class="col-12 col-lg-4">
        <div class="gs-panel h-100">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <p class="gs-panel-title mb-0">Most Absent</p>
                <span class="badge bg-danger-subtle text-danger">Absences</span>
            </div>

            <p class="text-muted small mb-3">
                Students with the highest number of recorded absences.
            </p>

            @if ($mostAbsentStudents->isNotEmpty())
                <x-ui.table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th class="text-end">Absent</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($mostAbsentStudents as $student)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $student['name'] }}</div>
                                    @if (!empty($student['section']))
                                        <div class="text-muted small">{{ $student['section'] }}</div>
                                    @endif
                                </td>
                                <td class="text-end text-danger fw-semibold">
                                    {{ number_format($student['absent']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @else
                <div class="gs-chart-empty">
                    <i class="fa-solid fa-user-check gs-chart-empty-icon"></i>
                    <p class="fw-medium mb-1">No absence data</p>
                    <p class="text-muted small mb-0">No students have recorded absences for the selected filters.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Early Arrivals --}}
    <div class="col-12 col-lg-4">
        <div class="gs-panel h-100">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <p class="gs-panel-title mb-0">Early Check-in</p>
                <span class="badge bg-success-subtle text-success">Early Arrivals</span>
            </div>

            <p class="text-muted small mb-3">
                Students with QR IN scans before the applicable late threshold.
            </p>

            @if ($earlyArrivalStudents->isNotEmpty())
                <x-ui.table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th class="text-end">Early</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($earlyArrivalStudents as $student)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $student['name'] }}</div>
                                    @if (!empty($student['section']))
                                        <div class="text-muted small">{{ $student['section'] }}</div>
                                    @endif
                                </td>
                                <td class="text-end text-success fw-semibold">
                                    {{ number_format($student['early_arrivals']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @else
                <div class="gs-chart-empty">
                    <i class="fa-solid fa-clock gs-chart-empty-icon"></i>
                    <p class="fw-medium mb-1">No early check-ins</p>
                    <p class="text-muted small mb-0">No early QR check-ins were recorded for the selected filters.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Most Present --}}
    <div class="col-12 col-lg-4">
        <div class="gs-panel h-100">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <p class="gs-panel-title mb-0">Most Present</p>
                <span class="badge bg-primary-subtle text-primary">Attendance</span>
            </div>

            <p class="text-muted small mb-3">
                Students with the highest number of attended school days.
            </p>

            @if ($mostPresentStudents->isNotEmpty())
                <x-ui.table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th class="text-end">Present</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($mostPresentStudents as $student)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $student['name'] }}</div>
                                    @if (!empty($student['section']))
                                        <div class="text-muted small">{{ $student['section'] }}</div>
                                    @endif
                                </td>
                                <td class="text-end text-primary fw-semibold">
                                    {{ number_format($student['attended']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @else
                <div class="gs-chart-empty">
                    <i class="fa-solid fa-calendar-check gs-chart-empty-icon"></i>
                    <p class="fw-medium mb-1">No attendance data</p>
                    <p class="text-muted small mb-0">No attended school days were found for the selected filters.</p>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- Student Attendance Detail --}}
@if ($studentSummaries->isNotEmpty())
    <div class="gs-panel mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <p class="gs-panel-title mb-0">Student Attendance Details</p>
        </div>

        <p class="text-muted small mb-3">
            Student-level attendance performance based on the current filters.
        </p>

        <x-ui.table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Section</th>
                    <th>Attendance Rate</th>
                    <th>Present</th>
                    <th>Late</th>
                    <th>Absent</th>
                    <th>Excused</th>
                    <th>Not in Class</th>
                    <th>Avg Grade</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($studentSummaries as $student)
                    <tr>
                        <td class="fw-semibold">{{ $student['name'] }}</td>

                        <td>{{ $student['section'] ?? 'N/AÂ' }}</td>

                        <td>
                            <span class="badge {{ $student['attendance_rate'] >= 85 ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $student['attendance_rate'] }}%
                            </span>
                        </td>

                        <td class="text-success">
                            {{ number_format($student['attended']) }}
                        </td>

                        <td class="text-warning">
                            {{ number_format($student['late']) }}
                        </td>

                        <td class="text-danger">
                            {{ number_format($student['absent']) }}
                        </td>

                        <td class="text-info">
                            {{ number_format($student['excused']) }}
                        </td>

                        <td class="text-secondary">
                            {{ number_format($student['not_in_classroom']) }}
                        </td>

                        <td>
                            {{ $student['avg_grade'] !== null
                                ? number_format($student['avg_grade'], 1)
                                : 'N/AÂ' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    </div>
@endif

<div class="gs-note-banner mb-3">
    <i class="fa-solid fa-circle-info"></i>
    This page correlates physical QR check-ins and teacher classroom verifications against academic performance.
    These are correlational trends, not causal claims.
</div>

{{-- Attendance Visualizations --}}
<div class="row g-3 mb-3">

    {{-- Attendance Trend --}}
    <div class="col-12 col-lg-6">
        <div class="gs-panel h-100">
            <p class="gs-panel-title mb-1">Attendance Trend</p>
            <p class="text-muted small mb-3">Attendance rate across the selected term or period.</p>

            @if (collect($termTrend['attendanceSeries'])->filter(fn ($v) => $v !== null)->isNotEmpty())
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="attendanceTrendChart"></canvas>
                </div>
            @else
                <div class="gs-chart-empty" style="height: 260px;">
                    <i class="fa-solid fa-chart-line gs-chart-empty-icon"></i>
                    <p class="fw-medium mb-1">No attendance trend data</p>
                    <p class="text-muted small mb-0">
                        No attendance records are available for the selected period.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Attendance Status --}}
    <div class="col-12 col-lg-6">
        <div class="gs-panel h-100">
            <p class="gs-panel-title mb-1">Attendance Status</p>
            <p class="text-muted small mb-3">Total student-days recorded by attendance category.</p>

            @if ($attendanceSummary &&
                ($attendanceSummary['present']
                + $attendanceSummary['late']
                + $attendanceSummary['excused']
                + $attendanceSummary['absent']
                + $attendanceSummary['not_in_classroom']) > 0)

                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="attendanceStatusChart"></canvas>
                </div>
            @else
                <div class="gs-chart-empty" style="height: 260px;">
                    <i class="fa-solid fa-chart-simple gs-chart-empty-icon"></i>
                    <p class="fw-medium mb-1">No status data available</p>
                    <p class="text-muted small mb-0">
                        No attendance statuses recorded for the current filter.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Attendance by Section --}}
@if ($sectionSummaries->isNotEmpty())
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="gs-panel">
                <p class="gs-panel-title mb-1">Attendance by Section</p>
                <p class="text-muted small mb-3">
                    Overall attendance rate comparison across assigned sections.
                </p>

                <div style="position: relative; height: {{ max(180, min(380, $sectionSummaries->count() * 45 + 50)) }}px; width: 100%;">
                    <canvas id="sectionAttendanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>
@endif


{{-- Performance Pattern Summary --}}
<div class="gs-panel mb-3">
    <p class="gs-panel-title mb-3">Performance Pattern Summary</p>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="gs-insight-card">
                <p class="gs-insight-label">Observed Relationship</p>
                <p class="gs-insight-text">{{ $insights['relationship'] }}</p>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="gs-insight-card">
                <p class="gs-insight-label">Absence - Grade Trend</p>
                <p class="gs-insight-text">{{ $insights['absence_trend'] }}</p>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="gs-insight-card">
                <p class="gs-insight-label">Section Pattern</p>
                <p class="gs-insight-text">{{ $insights['term_pattern'] }}</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@include('pov.teacher.my-classes.partials.common.chart-defaults')

<script>
    // 1. Attendance Trend
    @if (collect($termTrend['attendanceSeries'])->filter(fn ($v) => $v !== null)->isNotEmpty())
        new Chart(document.getElementById('attendanceTrendChart'), {
            type: 'line',
            data: {
                labels: @json($termTrend['labels']),
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: @json($termTrend['attendanceSeries']),
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.08)',
                    pointBackgroundColor: '#059669',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    borderWidth: 2.5,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.25,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Attendance Rate: ' + (context.raw !== null ? context.raw + '%' : 'No data');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        min: 0,
                        suggestedMin: 50,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Attendance Rate (%)',
                            font: { weight: '600', size: 11 }
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: 'rgba(243, 244, 246, 0.8)'
                        }
                    }
                }
            }
        });
    @endif

    // 2. Attendance Status
    @if ($attendanceSummary &&
        ($attendanceSummary['present']
        + $attendanceSummary['late']
        + $attendanceSummary['excused']
        + $attendanceSummary['absent']
        + $attendanceSummary['not_in_classroom']) > 0)

        new Chart(document.getElementById('attendanceStatusChart'), {
            type: 'bar',
            data: {
                labels: [
                    'Present',
                    'Late',
                    'Excused',
                    'Absent',
                    'Not in Class'
                ],
                datasets: [{
                    label: 'Student-Days',
                    data: [
                        {{ $attendanceSummary['present'] }},
                        {{ $attendanceSummary['late'] }},
                        {{ $attendanceSummary['excused'] }},
                        {{ $attendanceSummary['absent'] }},
                        {{ $attendanceSummary['not_in_classroom'] }}
                    ],
                    backgroundColor: [
                        '#10b981',
                        '#f59e0b',
                        '#06b6d4',
                        '#ef4444',
                        '#6b7280'
                    ],
                    borderRadius: 4,
                    maxBarThickness: 45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' +
                                    context.raw.toLocaleString() +
                                    ' student-days';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        title: {
                            display: true,
                            text: 'Total Student-Days',
                            font: { weight: '600', size: 11 }
                        },
                        grid: {
                            color: 'rgba(243, 244, 246, 0.8)'
                        }
                    }
                }
            }
        });
    @endif

    // 3. Section Attendance Comparison
    @if ($sectionSummaries->isNotEmpty())
        @php
            $sortedSections = $sectionSummaries->sortByDesc('attendance_rate')->values();
        @endphp

        new Chart(document.getElementById('sectionAttendanceChart'), {
            type: 'bar',
            data: {
                labels: @json($sortedSections->pluck('name')),
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: @json($sortedSections->pluck('attendance_rate')),
                    backgroundColor: '#2438b9',
                    borderRadius: 4,
                    maxBarThickness: 28
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Attendance Rate: ' +
                                    context.raw + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        min: 0,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Attendance Rate (%)',
                            font: { weight: '600', size: 11 }
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: 'rgba(243, 244, 246, 0.8)'
                        }
                    },
                    y: {
                        grid: { display: false }
                    }
                }
            }
        });
    @endif

</script>


</x-layouts.teacher>


