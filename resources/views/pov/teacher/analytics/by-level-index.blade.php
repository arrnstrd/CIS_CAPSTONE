<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Performance summaries grouped by grade, section, subject, or term.
    </x-slot>

    @include('pov.teacher.my-classes.partials.common.grading-tabs', ['activeTab' => 'bylevel'])

    <div class="gs-filter-bar mb-3">
        <div class="gs-pill-group">
            <button type="button" class="gs-pill-btn gs-pill-active" data-bylevel-tab="grade">Grade</button>
            <button type="button" class="gs-pill-btn" data-bylevel-tab="section">Section</button>
            <button type="button" class="gs-pill-btn" data-bylevel-tab="subject">Subject</button>
            <button type="button" class="gs-pill-btn" data-bylevel-tab="term">Term</button>
        </div>
    </div>

    <div id="bylevel-grade" class="bylevel-panel">
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="gs-panel">
                    <p class="gs-panel-title">Average Grade by Grade Level</p>
                    <canvas id="gradeAvgChart" height="200"></canvas>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="gs-panel">
                    <p class="gs-panel-title">Passing Rate by Grade Level</p>
                    <canvas id="gradePassingChart" height="200"></canvas>
                </div>
            </div>
            <div class="col-12">
                <div class="gs-panel">
                    <p class="gs-panel-title">At-Risk Students by Grade Level</p>
                    <canvas id="gradeAtRiskChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div id="bylevel-section" class="bylevel-panel" style="display:none;">
        <div class="gs-panel">
            <p class="gs-panel-title">Section Performance Summary</p>
            <div class="table-panel">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Students</th>
                            <th>Avg Grade</th>
                            <th>Passing Rate</th>
                            <th>Attendance</th>
                            <th>At-Risk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sectionRows as $row)
                            <tr>
                                <td>
                                    <div>{{ $row->section_name }}</div>
                                    <div class="gs-grade-subtext">Grade {{ $row->grade_level }}</div>
                                </td>
                                <td>{{ $row->total_students }}</td>
                                <td>{{ $row->avg_grade !== null ? $row->avg_grade : '—' }}</td>
                                <td>{{ $row->passing_rate !== null ? $row->passing_rate.'%' : '—' }}</td>
                                <td>{{ $row->attendance_rate !== null ? $row->attendance_rate.'%' : '—' }}</td>
                                <td>{{ $row->at_risk_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No active teaching assignments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="bylevel-subject" class="bylevel-panel" style="display:none;">
        <div class="gs-panel">
            <p class="gs-panel-title">Subject Performance Summary</p>
            <div class="table-panel">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Avg Grade</th>
                            <th>Passing Rate</th>
                            <th>Failing Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subjectRows as $row)
                            <tr>
                                <td>{{ $row->subject_name }}</td>
                                <td>{{ $row->avg_grade !== null ? $row->avg_grade : '—' }}</td>
                                <td>{{ $row->passing_rate !== null ? $row->passing_rate.'%' : '—' }}</td>
                                <td>{{ $row->failing_rate !== null ? $row->failing_rate.'%' : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No active teaching assignments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="bylevel-term" class="bylevel-panel" style="display:none;">
        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="gs-panel">
                    <p class="gs-panel-title">Average Grade by Term</p>
                    <canvas id="termAvgChart" height="200"></canvas>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="gs-panel">
                    <p class="gs-panel-title">Passing Rate by Term</p>
                    <canvas id="termPassingChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @include('pov.teacher.my-classes.partials.common.chart-defaults')
    <script>
        new Chart(document.getElementById('gradeAvgChart'), {
            type: 'bar',
            data: { labels: @json($gradeData['labels']), datasets: [{ label: 'Average Grade', data: @json($gradeData['avgGrade']), backgroundColor: '#2438b9', borderRadius: 6 }] },
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
        new Chart(document.getElementById('gradePassingChart'), {
            type: 'bar',
            data: { labels: @json($gradeData['labels']), datasets: [{ label: 'Passing Rate', data: @json($gradeData['passingRate']), backgroundColor: '#6c63ff', borderRadius: 6 }] },
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
        new Chart(document.getElementById('gradeAtRiskChart'), {
            type: 'bar',
            data: { labels: @json($gradeData['labels']), datasets: [{ label: 'At-Risk Students', data: @json($gradeData['atRisk']), backgroundColor: '#f5a623', borderRadius: 6 }] },
            options: { 
                plugins: { legend: { display: false } }, 
                scales: { 
                    x: { grid: { display: false } },
                    y: { 
                        grid: { color: 'rgba(243, 244, 246, 1)' }, 
                        beginAtZero: true 
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
        new Chart(document.getElementById('termAvgChart'), {
            type: 'bar',
            data: { labels: @json($termData['labels']), datasets: [{ label: 'Average Grade', data: @json($termData['avgGrade']), backgroundColor: '#2438b9', borderRadius: 4 }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, suggestedMax: 100 } } }
        });
        new Chart(document.getElementById('termPassingChart'), {
            type: 'bar',
            data: { labels: @json($termData['labels']), datasets: [{ label: 'Passing Rate', data: @json($termData['passingRate']), backgroundColor: '#0f9d58', borderRadius: 4 }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, suggestedMax: 100 } } }
        });

        document.querySelectorAll('[data-bylevel-tab]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('[data-bylevel-tab]').forEach(b => b.classList.remove('gs-pill-active'));
                this.classList.add('gs-pill-active');
                document.querySelectorAll('.bylevel-panel').forEach(p => p.style.display = 'none');
                document.getElementById('bylevel-' + this.dataset.bylevelTab).style.display = 'block';
            });
        });
    </script>

</x-layouts.teacher>