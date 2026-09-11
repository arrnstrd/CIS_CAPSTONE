<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Performance summary across the subjects you teach.
    </x-slot>

    @include('pov.teacher.my-classes.partials.common.grading-tabs', ['activeTab' => 'subjects'])

    <form method="GET" action="{{ route('teacher.grading-system.subjects') }}" class="gs-filter-bar mb-3">
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
                    @foreach ($sections as $sec)
                        <option value="{{ $sec->id }}" @selected($selectedSectionId == $sec->id)>{{ $sec->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="gs-panel mb-3">
        <p class="gs-panel-title">Subject Performance Summary</p>
        <div class="table-panel">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Average</th>
                        <th>Passing Rate</th>
                        <th>Failing Rate</th>
                        <th>Highest</th>
                        <th>Lowest</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subjectRows as $row)
                        <tr>
                            <td>
                                <span class="gs-subject-icon"><i class="fa-solid fa-book-open"></i></span>
                                {{ $row->subject_name }}
                            </td>
                            <td class="fw-bold">{{ $row->avg_grade !== null ? $row->avg_grade : '—' }}</td>
                            <td>
                                @if ($row->passing_rate !== null)
                                    <span class="gs-badge gs-badge-success">{{ $row->passing_rate }}%</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($row->failing_rate !== null)
                                    <span class="gs-badge gs-badge-danger">{{ $row->failing_rate }}%</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="gs-row-subtext">{{ $row->highest !== null ? $row->highest : '—' }}</td>
                            <td class="gs-row-subtext">{{ $row->lowest !== null ? $row->lowest : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No active teaching assignments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="gs-panel">
        <p class="gs-panel-title">Subject Performance Trend (All Terms)</p>
        @if (collect($trendData['datasets'])->isNotEmpty())
            <canvas id="subjectTrendChart" height="200"></canvas>
        @else
            <div class="gs-chart-empty" style="height: 200px;">
                <i class="fa-solid fa-chart-line gs-chart-empty-icon"></i>
                <p class="mb-0">No subjects found for this filter.</p>
            </div>
        @endif
    </div>

    <div class="gs-panel mt-3">
        <p class="gs-panel-title">Analysis Overview by Grade Level</p>
        <p class="gs-row-subtext mb-2">Average grade per subject, across the grade levels you teach.</p>
        @if (collect($divisionChart['datasets'])->isNotEmpty())
            <canvas id="divisionChart" height="220"></canvas>
        @else
            <div class="gs-chart-empty" style="height: 220px;">
                <i class="fa-solid fa-chart-simple gs-chart-empty-icon"></i>
                <p class="mb-0">No teaching assignments found.</p>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @include('pov.teacher.my-classes.partials.common.chart-defaults')
    <script>
        @if (collect($trendData['datasets'])->isNotEmpty())
            new Chart(document.getElementById('subjectTrendChart'), {
                type: 'line',
                data: {
                    labels: @json($trendData['labels']),
                    datasets: @json($trendData['datasets'])
                },
                options: {
                    plugins: { legend: { position: 'bottom' } },
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
        @endif

        @if (collect($divisionChart['datasets'])->isNotEmpty())
            new Chart(document.getElementById('divisionChart'), {
                type: 'bar',
                data: {
                    labels: @json($divisionChart['labels']),
                    datasets: @json($divisionChart['datasets'])
                },
                options: {
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true, suggestedMax: 100 } }
                }
            });
        @endif
    </script>

</x-layouts.teacher>