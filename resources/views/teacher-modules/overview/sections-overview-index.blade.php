<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Compare sections within a grade level.
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'sections'])

    <form method="GET" action="{{ route('teacher.grading-system.sections') }}" class="gs-filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Grade Level</label>
                <select name="grade_level" class="form-select form-select-sm" onchange="this.form.submit()">
                    @forelse ($gradeLevels as $gl)
                        <option value="{{ $gl }}" @selected($selectedGradeLevel == $gl)>Grade {{ $gl }}</option>
                    @empty
                        <option value="">No grades available</option>
                    @endforelse
                </select>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        @forelse ($sections as $s)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="gs-section-card">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="gs-stat-icon gs-stat-icon-neutral">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="gs-grade-pill">Grade {{ $selectedGradeLevel }}</span>
                            @if ($s->at_risk_count > 0)
                                <span class="gs-badge gs-badge-danger">{{ $s->at_risk_count }} at-risk</span>
                            @endif
                        </div>
                    </div>
                    <p class="gs-section-card-title mb-0">{{ $s->section_name }}</p>
                    <p class="gs-section-card-meta">{{ $s->total_students }} students</p>

                    <div class="gs-metric-list mt-3">
                        <div class="gs-metric-row">
                            <span class="gs-metric-label"><i class="fa-solid fa-chart-line"></i> Avg Grade</span>
                            <span class="gs-metric-value">{{ $s->avg_grade !== null ? $s->avg_grade : '—' }}</span>
                        </div>
                        <div class="gs-metric-row">
                            <span class="gs-metric-label"><i class="fa-solid fa-user-check"></i> Passing Rate</span>
                            <span class="gs-metric-value">{{ $s->passing_rate !== null ? $s->passing_rate.'%' : '—' }}</span>
                        </div>
                        <div class="gs-metric-row">
                            <span class="gs-metric-label"><i class="fa-solid fa-calendar-check"></i> Attendance</span>
                            <span class="gs-metric-value">{{ $s->attendance_rate !== null ? $s->attendance_rate.'%' : '—' }}</span>
                        </div>
                        <div class="gs-metric-row gs-metric-row-last">
                            <span class="gs-metric-label"><i class="fa-solid fa-triangle-exclamation"></i> At-Risk</span>
                            <span class="gs-metric-value">{{ $s->at_risk_count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="gs-panel text-center text-muted py-4">
                    No sections found for this grade level.
                </div>
            </div>
        @endforelse
    </div>

    @if ($sections->isNotEmpty())
        <div class="row g-3">
            <div class="col-12 col-lg-4">
                <div class="gs-panel">
                    <p class="gs-panel-title">Section Average Grade</p>
                    <canvas id="secAvgChart" height="200"></canvas>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="gs-panel">
                    <p class="gs-panel-title">Section Attendance Rate</p>
                    <canvas id="secAttChart" height="200"></canvas>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="gs-panel">
                    <p class="gs-panel-title">Section Passing Rate</p>
                    <canvas id="secPassChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        @include('teacher-modules.partials.chart-defaults')
        <script>
            const secLabels = @json($sections->pluck('section_name'));
            new Chart(document.getElementById('secAvgChart'), {
                type: 'bar',
                data: { labels: secLabels, datasets: [{ data: @json($sections->map(fn($s) => $s->avg_grade ?? 0)), backgroundColor: '#2438b9', borderRadius: 6 }] },
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
            new Chart(document.getElementById('secAttChart'), {
                type: 'bar',
                data: { labels: secLabels, datasets: [{ data: @json($sections->map(fn($s) => $s->attendance_rate ?? 0)), backgroundColor: '#6c63ff', borderRadius: 6 }] },
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
            new Chart(document.getElementById('secPassChart'), {
                type: 'bar',
                data: { labels: secLabels, datasets: [{ data: @json($sections->map(fn($s) => $s->passing_rate ?? 0)), backgroundColor: '#0f9d58', borderRadius: 6 }] },
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
    @endif

</x-layouts.teacher>