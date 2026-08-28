<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Student profile overview.
    </x-slot>

    @include('teacher-modules.partials.grading-breadcrumb', ['crumbs' => [
        ['label' => 'Student Profile', 'url' => route('teacher.grading-system.student-profile')],
        ['label' => $enrollment->student->first_name . ' ' . $enrollment->student->last_name, 'url' => '#'],
    ]])

    <!-- Header Section -->
    <div class="gs-panel mb-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="gs-profile-avatar">
                    {{ strtoupper(substr($enrollment->student->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($enrollment->student->last_name ?? '?', 0, 1)) }}
                </span>
                <div>
                    <p class="gs-section-card-title mb-1">{{ $enrollment->student->full_name }}</p>
                    <p class="gs-section-card-meta mb-0">
                        {{ $enrollment->student->lrn }} · Grade {{ $enrollment->section->grade_level }} - {{ $enrollment->section->name }} · SY 2025-2026
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                @if ($riskData)
                    @php
                        $riskClass = ['Low' => 'gs-badge-success', 'Moderate' => 'gs-badge-warning', 'High' => 'gs-badge-danger'][$riskData['risk_level']];
                    @endphp
                    <span class="gs-badge {{ $riskClass }}" style="font-size: 0.8rem; padding: 6px 14px;">{{ $riskData['risk_level'] }} Risk</span>
                @endif
                <a href="#" class="btn btn-sm btn-outline-secondary" disabled title="Change History feature coming soon">
                    <i class="fa-solid fa-clock-rotate-left"></i> View Change History
                </a>
            </div>
        </div>
    </div>

    <!-- 5 Stat Cards -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-neutral"><i class="fa-solid fa-chart-line"></i></span>
                <div>
                    <p class="gs-stat-label">Current Average</p>
                    <p class="gs-stat-value">{{ $overallAvg !== null ? $overallAvg : '—' }}</p>
                    <p class="gs-stat-subtext">Term {{ $currentPeriod?->sequence ?? '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-neutral"><i class="fa-solid fa-calendar-check"></i></span>
                <div>
                    <p class="gs-stat-label">Overall Attendance</p>
                    <p class="gs-stat-value">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</p>
                    <p class="gs-stat-subtext">{{ $presentCount }} scans</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-warning"><i class="fa-solid fa-exclamation-circle"></i></span>
                <div>
                    <p class="gs-stat-label">Missing Grades</p>
                    <p class="gs-stat-value text-warning">{{ $missingGradesCount }}</p>
                    <p class="gs-stat-subtext">across all subjects</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-danger"><i class="fa-solid fa-xmark-circle"></i></span>
                <div>
                    <p class="gs-stat-label">Below Passing</p>
                    <p class="gs-stat-value text-danger">{{ $belowPassingCount }}</p>
                    <p class="gs-stat-subtext">&lt; 75% scores</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-neutral"><i class="fa-solid fa-triangle-exclamation"></i></span>
                <div>
                    <p class="gs-stat-label">Risk Level</p>
                    <p class="gs-stat-value">{{ $riskData['risk_level'] ?? '—' }}</p>
                    <p class="gs-stat-subtext">Score: {{ $riskData['risk_score'] ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Academic Grades Table -->
    <div class="gs-panel mb-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="gs-panel-title mb-0">Academic Grades</p>
            <div class="gs-tab-bar">
                <a href="#" class="gs-tab {{ !request('term') ? 'gs-tab-active' : '' }}" data-term="all">All Terms</a>
                <a href="#" class="gs-tab {{ request('term') == '1' ? 'gs-tab-active' : '' }}" data-term="1">Term 1</a>
                <a href="#" class="gs-tab {{ request('term') == '2' ? 'gs-tab-active' : '' }}" data-term="2">Term 2</a>
                <a href="#" class="gs-tab {{ request('term') == '3' ? 'gs-tab-active' : '' }}" data-term="3">Term 3</a>
            </div>
        </div>
        <div class="table-panel">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Grade Level</th>
                        <th>Section</th>
                        <th>School Year</th>
                        <th>Term</th>
                        <th>WW%</th>
                        <th>PT%</th>
                        <th>TA%</th>
                        <th>Final Grade</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subjects as $subject)
                        @php
                            $byTerm = $subject->periods->keyBy('term_label');
                        @endphp
                        <tr>
                            <td>{{ $subject->subject_name }}</td>
                            <td>{{ $subject->grade_level }}</td>
                            <td>{{ $subject->section_name }}</td>
                            <td>2025-2026</td>
                            <td>
                                @foreach ($subject->periods as $period)
                                    <div class="small">{{ $period->term_label }}</div>
                                @endforeach
                            </td>
                            <td>
                                @foreach ($subject->periods as $period)
                                    <div class="small">—</div>
                                @endforeach
                            </td>
                            <td>
                                @foreach ($subject->periods as $period)
                                    <div class="small">—</div>
                                @endforeach
                            </td>
                            <td>
                                @foreach ($subject->periods as $period)
                                    <div class="small">—</div>
                                @endforeach
                            </td>
                            <td>
                                @foreach ($subject->periods as $period)
                                    <div class="fw-small">{{ $period->grade !== null ? $period->grade : '—' }}</div>
                                @endforeach
                            </td>
                            <td>
                                @foreach ($subject->periods as $period)
                                    <div class="small">
                                        @if ($period->grade !== null)
                                            @if ($period->grade >= 75)
                                                <span class="gs-badge-success">Passing</span>
                                            @else
                                                <span class="gs-badge-danger">Failing</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">No subjects found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3">
        <!-- Grade Changes Over Time Chart -->
        <div class="col-12 col-lg-8">
            <div class="gs-panel">
                <p class="gs-panel-title">Grade Changes Over Time</p>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="gradeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Risk Indicators Summary -->
        <div class="col-12 col-lg-4">
            <div class="gs-panel">
                <p class="gs-panel-title">Why this student may need attention</p>
                @if ($riskData && !empty($riskData['indicators']))
                    <div class="risk-indicators">
                        @foreach ($riskData['indicators'] as $indicator => $isTrue)
                            @if ($isTrue)
                                @php
                                    $indicatorLabels = [
                                        'low_grade' => 'Low grades in current term',
                                        'missing_grades' => 'Missing assignments or assessments',
                                        'low_attendance' => 'Poor attendance record',
                                        'declining_performance' => 'Declining academic performance',
                                    ];
                                @endphp
                                <div class="alert alert-warning d-flex align-items-center gap-2 mb-2">
                                    <i class="fa-solid fa-exclamation-triangle"></i>
                                    <span class="small">{{ $indicatorLabels[$indicator] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-success d-flex align-items-center gap-2">
                        <i class="fa-solid fa-check-circle"></i>
                        <span class="small">No major risk indicators flagged</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Teacher Remarks and Interventions -->
    <div class="row g-3">
        <div class="col-6">
            <div class="gs-panel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="gs-panel-title mb-0">Teacher Remarks</p>
                    <button class="btn btn-sm btn-outline-primary" disabled title="Add Remark feature coming soon">
                        <i class="fa-solid fa-plus"></i> Add Remark
                    </button>
                </div>
                <div class="text-center text-muted py-4">
                    <i class="fa-solid fa-comment fa-2x mb-2"></i>
                    <p class="small">No teacher remarks yet</p>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="gs-panel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="gs-panel-title mb-0">Interventions</p>
                    <button class="btn btn-sm btn-outline-primary" disabled title="Add Intervention feature coming soon">
                        <i class="fa-solid fa-plus"></i> Add Intervention
                    </button>
                </div>
                <div class="text-center text-muted py-4">
                    <i class="fa-solid fa-hand-holding-heart fa-2x mb-2"></i>
                    <p class="small">No interventions have been recorded yet</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js and Grade Chart Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/chart-defaults.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('gradeChart').getContext('2d');
            
            const gradeData = {
                labels: @json(array_column($gradeHistory, 'term')),
                datasets: [{
                    label: 'Average Grade',
                    data: @json(array_column($gradeHistory, 'average')),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            };

            new Chart(ctx, {
                type: 'line',
                data: gradeData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Average: ' + (context.parsed.y || '—');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });

            // Tab functionality
            document.querySelectorAll('.gs-tab').forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    const term = this.dataset.term;
                    // Update active state
                    document.querySelectorAll('.gs-tab').forEach(t => t.classList.remove('gs-tab-active'));
                    this.classList.add('gs-tab-active');
                    // Here you would typically filter the table data based on term
                    console.log('Filtering by term:', term);
                });
            });
        });
    </script>
</x-layouts.teacher>