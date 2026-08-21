<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Observed patterns between attendance and academic performance.
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'attendance'])

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
        </div>
    </form>

    <div class="gs-note-banner mb-3">
        <i class="fa-solid fa-circle-info"></i>
        This page shows observed patterns between attendance and academic performance. These are correlational trends, not causal claims.
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="gs-panel">
                <p class="gs-panel-title">Absences vs Average Grade (per student)</p>
                <p class="gs-row-subtext mb-2">Approximate — based on weekdays since each section's first recorded scan.</p>
                @if ($studentPoints->isNotEmpty())
                    <canvas id="studentScatter" height="220"></canvas>
                @else
                    <div class="gs-chart-empty" style="height: 220px;">
                        <i class="fa-solid fa-chart-scatter gs-chart-empty-icon"></i>
                        <p class="mb-0">No grades recorded yet.</p>
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
                        <p class="mb-0">No grades recorded yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="gs-panel">
                <p class="gs-panel-title">Attendance & Grade Trend Across Terms</p>
                @if (collect($termTrend['gradeSeries'])->filter()->isNotEmpty())
                    <canvas id="termTrendChart" height="180"></canvas>
                @else
                    <div class="gs-chart-empty" style="height: 180px;">
                        <i class="fa-solid fa-chart-line gs-chart-empty-icon"></i>
                        <p class="mb-0">No grades recorded yet across terms.</p>
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
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { 
                            title: { display: true, text: 'Absences (approx. school days missed)' }, 
                            min: 0,
                            grid: { display: false }
                        },
                        y: { 
                            title: { display: true, text: 'Average Grade' }, 
                            min: 60, 
                            max: 100,
                            grid: { color: 'rgba(243, 244, 246, 1)' }
                        }
                    },
                    plugins: {
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.90)',
                            padding: 10,
                            cornerRadius: 8,
                            displayColors: false
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
                            backgroundColor: 'rgba(108, 99, 255, 0.1)',
                            yAxisID: 'yAttendance',
                            borderDash: [5, 5],
                            tension: 0.2,
                        },
                        {
                            type: 'line',
                            label: 'Avg Grade',
                            data: @json($termTrend['gradeSeries']),
                            borderColor: '#2438b9',
                            backgroundColor: 'rgba(36, 56, 185, 0.1)',
                            yAxisID: 'yGrade',
                            tension: 0.2,
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