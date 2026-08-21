<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Overview of your sections' grading performance.
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'overview'])

    <form method="GET" action="{{ route('teacher.grading-system') }}" class="gs-filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="gs-filter-label">School Year</label>
                <select name="school_year_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="" @selected(!$selectedSchoolYearId)>All School Years</option>
                    @foreach ($schoolYears as $sy)
                        <option value="{{ $sy->id }}" @selected($selectedSchoolYearId == $sy->id)>{{ $sy->school_year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Term</label>
                <select name="grading_period_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach ($gradingPeriods as $gp)
                        <option value="{{ $gp->id }}" @selected($selectedGradingPeriodId == $gp->id)>Term {{ $gp->sequence }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-neutral">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
                <div>
                    <p class="gs-stat-label">Average Grade</p>
                    <p class="gs-stat-value">{{ $stats['avg_grade'] !== null ? $stats['avg_grade'] : '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card gs-stat-card-success d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-success">
                    <i class="fa-solid fa-user-check"></i>
                </span>
                <div>
                    <p class="gs-stat-label gs-stat-label-success">Passing Rate</p>
                    <p class="gs-stat-value gs-stat-present">{{ $stats['passing_rate'] !== null ? $stats['passing_rate'].'%' : '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card gs-stat-card-danger d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-danger">
                    <i class="fa-solid fa-user-xmark"></i>
                </span>
                <div>
                    <p class="gs-stat-label gs-stat-label-danger">Failing Rate</p>
                    <p class="gs-stat-value gs-stat-danger">{{ $stats['failing_rate'] !== null ? $stats['failing_rate'].'%' : '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-neutral">
                    <i class="fa-solid fa-calendar-check"></i>
                </span>
                <div>
                    <p class="gs-stat-label">Avg Attendance</p>
                    <p class="gs-stat-value">{{ $stats['avg_attendance'] !== null ? $stats['avg_attendance'].'%' : '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-neutral">
                    <i class="fa-solid fa-users"></i>
                </span>
                <div>
                    <p class="gs-stat-label">Total Students</p>
                    <p class="gs-stat-value">{{ $stats['total_students'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card gs-stat-card-danger d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
                <div>
                    <p class="gs-stat-label gs-stat-label-danger">Failing Students</p>
                    <p class="gs-stat-value gs-stat-danger">{{ $stats['failing_students'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="gs-panel">
                <p class="gs-panel-title">Average Grade by Section</p>
                <canvas id="avgGradeChart" height="180"></canvas>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="gs-panel">
                <p class="gs-panel-title">Attendance Rate by Section</p>
                <canvas id="attendanceChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="gs-panel">
                <p class="gs-panel-title">Passing vs Failing Students</p>
                @if (($chartData['totalPassing'] + $chartData['totalFailing']) > 0)
                    <canvas id="passFailChart" height="180"></canvas>
                @else
                    <div class="gs-chart-empty">
                        <i class="fa-solid fa-chart-pie gs-chart-empty-icon"></i>
                        <p class="mb-0">No grades recorded yet for this term.</p>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="gs-panel">
                <p class="gs-panel-title">Performance Trend (All Terms)</p>
                <canvas id="trendChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <div class="gs-panel">
        <p class="gs-panel-title">Section Breakdown</p>
        <div class="table-panel">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Subject</th>
                        <th>Students</th>
                        <th>Avg Grade</th>
                        <th>Passing Rate</th>
                        <th>Attendance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sectionBreakdown as $row)
                        <tr>
                            <td>
                                {{ $row->section_name }}
                                <span class="gs-row-subtext">· Grade {{ $row->grade_level }}</span>
                            </td>
                            <td>{{ $row->subject_name }}</td>
                            <td>{{ $row->total_students }}</td>
                            <td>{{ $row->avg_grade !== null ? $row->avg_grade : '—' }}</td>
                            <td>{{ $row->passing_rate !== null ? $row->passing_rate.'%' : '—' }}</td>
                            <td>
                                @php
                                    $rate = $row->attendance_rate;
                                    $badgeClass = $rate >= 90 ? 'gs-badge-success' : ($rate >= 50 ? 'gs-badge-warning' : 'gs-badge-danger');
                                @endphp
                                <span class="gs-badge {{ $badgeClass }}">{{ $rate }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No active teaching assignments found for this school year.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @include('teacher-modules.partials.chart-defaults')
    <script>
        const avgGradeCtx = document.getElementById('avgGradeChart');
        new Chart(avgGradeCtx, {
            type: 'bar',
            data: {
                labels: @json($chartData['sectionLabels']),
                datasets: [{
                    label: 'Average Grade',
                    data: @json($chartData['sectionAvgGrades']),
                    backgroundColor: '#2438b9',
                    borderRadius: 6,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { 
                    x: { grid: { display: false } },
                    y: { 
                        grid: { color: 'rgba(243, 244, 246, 1)' }, 
                        beginAtZero: true, 
                        suggestedMax: 100 
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

        const trendCtx = document.getElementById('trendChart');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($chartData['trendLabels']),
                datasets: [{
                    label: 'Average Grade',
                    data: @json($chartData['trendData']),
                    borderColor: '#f5a623',
                    backgroundColor: 'rgba(245, 166, 35, 0.15)',
                    tension: 0.35,
                    fill: true,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { 
                    x: { grid: { display: false } },
                    y: { 
                        grid: { color: 'rgba(243, 244, 246, 1)' }, 
                        beginAtZero: true, 
                        suggestedMax: 100 
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

        const passFailCtx = document.getElementById('passFailChart');
        if (passFailCtx) {
            new Chart(passFailCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Passing', 'Failing'],
                    datasets: [{
                        data: [@json($chartData['totalPassing']), @json($chartData['totalFailing'])],
                        backgroundColor: ['#085041', '#791F1F'],
                    }]
                },
                options: {
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        const attendanceCtx = document.getElementById('attendanceChart');
        new Chart(attendanceCtx, {
            type: 'bar',
            data: {
                labels: @json($chartData['attendanceLabels']),
                datasets: [{
                    label: 'Attendance Rate',
                    data: @json($chartData['attendanceRates']),
                    backgroundColor: '#6c63ff',
                    borderRadius: 6,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { 
                    x: { grid: { display: false } },
                    y: { 
                        grid: { color: 'rgba(243, 244, 246, 1)' }, 
                        beginAtZero: true, 
                        suggestedMax: 100 
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
    </script>

</x-layouts.teacher>