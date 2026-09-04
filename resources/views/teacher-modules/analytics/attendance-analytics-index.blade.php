<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-clipboard-user"></i>
            Attendance Analytics
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">Observed patterns between attendance records and student academic performance.</span>
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'analytics'])

    <form method="GET" action="{{ route('teacher.grading-system.attendance') }}" class="gs-filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Grade Level</label>
                <select name="grade_level" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Grades</option>
                    @foreach ($gradeLevels as $gl)
                        <option value="{{ $gl }}" @selected($selectedGradeLevel == $gl)>Grade {{ $gl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Section</label>
                <select name="section_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Sections</option>
                    @foreach ($sections as $s)
                        <option value="{{ $s->id }}" @selected($selectedSectionId == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Term</label>
                <select name="term" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Terms / Current</option>
                    @for ($t = 1; $t <= 3; $t++)
                        <option value="{{ $t }}" @selected($selectedTerm == $t)>Term {{ $t }}</option>
                    @endfor
                </select>
            </div>
        </div>
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
                    <p class="gs-grade-card-avg text-success">{{ number_format($attendanceSummary['present']) }}</p>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="gs-stat-card">
                    <p class="gs-grade-card-title">Late Days</p>
                    <p class="gs-grade-card-avg text-warning">{{ number_format($attendanceSummary['late']) }}</p>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="gs-stat-card">
                    <p class="gs-grade-card-title">Absences</p>
                    <p class="gs-grade-card-avg text-danger">{{ number_format($attendanceSummary['absent']) }}</p>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="gs-stat-card">
                    <p class="gs-grade-card-title">Excused</p>
                    <p class="gs-grade-card-avg text-info">{{ number_format($attendanceSummary['excused']) }}</p>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="gs-stat-card">
                    <p class="gs-grade-card-title">Not in Classroom</p>
                    <p class="gs-grade-card-avg text-secondary">{{ number_format($attendanceSummary['not_in_classroom']) }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Section Attendance Summary Table --}}
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
                    @foreach ($sectionSummaries as $sec)
                        <tr>
                            <td class="fw-semibold">{{ $sec['name'] }}</td>
                            <td>Grade {{ $sec['grade_level'] }}</td>
                            <td>{{ $sec['student_count'] }}</td>
                            <td>
                                <span class="badge {{ $sec['attendance_rate'] >= 85 ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $sec['attendance_rate'] }}%
                                </span>
                            </td>
                            <td>{{ $sec['avg_grade'] !== null ? number_format($sec['avg_grade'], 1) : '—' }}</td>
                            <td class="text-success">{{ number_format($sec['present']) }}</td>
                            <td class="text-warning">{{ number_format($sec['late']) }}</td>
                            <td class="text-danger">{{ number_format($sec['absent']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        </div>
    @endif

    <div class="gs-note-banner mb-3">
        <i class="fa-solid fa-circle-info"></i>
        This page correlates physical QR check-ins and teacher classroom verifications against academic performance. These are correlational trends, not causal claims.
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="gs-panel">
                <p class="gs-panel-title">Absences vs Average Grade (per student)</p>
                <p class="gs-row-subtext mb-2">Based on weekdays within the tracking window.</p>
                @if ($studentPoints->isNotEmpty())
                    <canvas id="studentScatter" height="220"></canvas>
                @else
                    <div class="gs-chart-empty" style="height: 220px;">
                        <i class="fa-solid fa-chart-scatter gs-chart-empty-icon"></i>
                        <p class="mb-0">No grades or attendance records found.</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="gs-panel">
                <p class="gs-panel-title">Section Attendance Rate vs Average Grade</p>
                @if ($sectionPoints->isNotEmpty())
                    <canvas id="sectionScatter" height="220"></canvas>
                @else
                    <div class="gs-chart-empty" style="height: 220px;">
                        <i class="fa-solid fa-chart-scatter gs-chart-empty-icon"></i>
                        <p class="mb-0">No section attendance data available.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="gs-panel">
                <p class="gs-panel-title">Attendance & Grade Trend Across Terms</p>
                @if (collect($termTrend['gradeSeries'])->filter()->isNotEmpty() || collect($termTrend['attendanceSeries'])->filter()->isNotEmpty())
                    <canvas id="termTrendChart" height="180"></canvas>
                @else
                    <div class="gs-chart-empty" style="height: 180px;">
                        <i class="fa-solid fa-chart-line gs-chart-empty-icon"></i>
                        <p class="mb-0">No multi-term trends recorded yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="gs-panel mt-3">
        <p class="gs-panel-title">Performance Pattern Summary</p>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="gs-insight-card">
                    <p class="gs-insight-label">Observed Relationship</p>
                    <p class="gs-insight-text">{{ $insights['relationship'] }}</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="gs-insight-card">
                    <p class="gs-insight-label">Absence–Grade Trend</p>
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
    @include('teacher-modules.partials.chart-defaults')
    <script>
        @if ($studentPoints->isNotEmpty())
            new Chart(document.getElementById('studentScatter'), {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Students',
                        data: @json($studentPoints),
                        backgroundColor: '#2438b9',
                    }]
                },
                options: {
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const raw = context.raw;
                                    return (raw.name ? raw.name + ': ' : '') + raw.x + ' absences, ' + raw.y + ' avg grade';
                                }
                            }
                        }
                    },
                    scales: {
                        x: { 
                            title: { display: true, text: 'Absences (school days missed)' }, 
                            min: 0,
                            grid: { display: false }
                        },
                        y: { 
                            title: { display: true, text: 'Average Grade' }, 
                            min: 60, 
                            max: 100,
                            grid: { color: 'rgba(243, 244, 246, 1)' }
                        }
                    }
                }
            });
        @endif

        @if ($sectionPoints->isNotEmpty())
            new Chart(document.getElementById('sectionScatter'), {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Sections',
                        data: @json($sectionPoints),
                        backgroundColor: '#f5a623',
                        pointRadius: 6,
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { title: { display: true, text: 'Section Attendance Rate (%)' }, min: 0, max: 100 },
                        y: { title: { display: true, text: 'Section Average Grade' }, min: 60, max: 100 }
                    }
                }
            });
        @endif

        @if (collect($termTrend['gradeSeries'])->filter()->isNotEmpty())
            new Chart(document.getElementById('termTrendChart'), {
                data: {
                    labels: @json($termTrend['labels']),
                    datasets: [
                        {
                            type: 'line',
                            label: 'Avg Attendance (%)',
                            data: @json($termTrend['attendanceSeries']),
                            borderColor: '#6c63ff',
                            backgroundColor: 'rgba(108, 99, 255, 0.08)',
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            yAxisID: 'yAttendance',
                            borderDash: [4, 4],
                            tension: 0.35,
                        },
                        {
                            type: 'line',
                            label: 'Avg Grade',
                            data: @json($termTrend['gradeSeries']),
                            borderColor: '#2438b9',
                            backgroundColor: 'rgba(36, 56, 185, 0.08)',
                            borderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            yAxisID: 'yGrade',
                            tension: 0.35,
                        }
                    ]
                },
                options: {
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        yAttendance: { type: 'linear', position: 'left', min: 70, max: 100, title: { display: true, text: 'Attendance (%)' } },
                        yGrade: { type: 'linear', position: 'right', min: 70, max: 100, grid: { drawOnChartArea: false }, title: { display: true, text: 'Grade' } }
                    }
                }
            });
        @endif
    </script>

</x-layouts.teacher>