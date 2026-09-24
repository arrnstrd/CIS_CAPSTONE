<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-briefcase"></i>
            My Classes
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">Overview of your sections' grading performance.</span>
    </x-slot>

    <div class="gd-dashboard-wrapper {{ ($dashboardPreferences?->dashboard_density ?? 'comfortable') === 'compact' ? 'dashboard-density-compact' : 'dashboard-density-comfortable' }}">

        {{-- Summary Cards --}}
        @if ($dashboardPreferences?->show_summary_cards ?? true)
            <div class="row g-3 mb-4" data-tour="myclasses-stats">
                <div class="col-6 col-md-3">
                    <div class="gs-stat-card d-flex align-items-center gap-3">
                        <span class="gs-stat-icon gs-stat-icon-neutral">
                            <i class="fa-solid fa-chalkboard"></i>
                        </span>
                        <div>
                            <p class="gs-stat-label">Total Classes</p>
                            <p class="gs-stat-value">{{ $totalClasses }}</p>
                        </div>
                    </div>
                </div>

                @if ($dashboardPreferences?->show_student_counts ?? true)
                    <div class="col-6 col-md-3">
                        <div class="gs-stat-card d-flex align-items-center gap-3">
                            <span class="gs-stat-icon gs-stat-icon-neutral">
                                <i class="fa-solid fa-users"></i>
                            </span>
                            <div>
                                <p class="gs-stat-label">Total Students</p>
                                <p class="gs-stat-value">{{ $totalStudents }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-6 col-md-3">
                    <div class="gs-stat-card d-flex align-items-center gap-3">
                        <span class="gs-stat-icon gs-stat-icon-neutral">
                            <i class="fa-solid fa-calendar-days"></i>
                        </span>
                        <div>
                            <p class="gs-stat-label">Current Term</p>
                            <p class="gs-stat-value">{{ $currentTermLabel }}</p>
                        </div>
                    </div>
                </div>

                @if ($dashboardPreferences?->show_at_risk ?? true)
                    <div class="col-6 col-md-3">
                        <div class="gs-stat-card gs-stat-card-danger d-flex align-items-center gap-3">
                            <span class="gs-stat-icon gs-stat-icon-danger">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </span>
                            <div>
                                <p class="gs-stat-label gs-stat-label-danger">Students At Risk</p>
                                <p class="gs-stat-value">{{ $totalAtRisk ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- My Classes Section --}}
        <div class="gd-layout">
            <div class="gd-content">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="gs-panel-title mb-0">My Classes</p>
                </div>

                <div class="row g-3" data-tour="myclasses-cards">
                    @forelse ($classes as $class)
                        <div class="col-12 col-md-6 col-xl-4">
                            <a href="{{ route('teacher.grading-system.grade-sheet', ['teachingAssignmentId' => $class->teaching_assignment_id, 'grading_period_id' => $currentPeriod->id]) }}" class="text-decoration-none">
                                <div class="gs-panel gs-class-card">
                                    <div class="gs-class-card-header">
                                        <div class="gs-class-card-id">
                                            <div class="gs-grade-avatar">{{ $class->grade_level }}</div>
                                            <div>
                                                <p class="gs-panel-title mb-0">{{ $class->section_name }}</p>
                                                <p class="gs-class-card-subtitle mb-0">{{ $class->format_badge }} - {{ $class->subject_name }}</p>
                                            </div>
                                        </div>
                                        <span class="gs-badge {{ $class->status === 'Complete' ? 'gs-badge-success' : ($class->status === 'In Progress' ? 'gs-badge-warning' : 'gs-badge') }}">
                                            {{ $class->status }}
                                        </span>
                                    </div>

                                    @if ($dashboardPreferences?->show_progress_indicators ?? true)
                                        <div class="gd-progress-wrap">
                                            <div class="d-flex justify-content-between">
                                                <span class="gd-progress-label">Completion</span>
                                                <span class="gd-progress-label">
                                                    {{ $class->completion_percent !== null ? $class->completion_percent . '%' : '-' }}
                                                </span>
                                            </div>
                                            <div class="gd-progress-bar">
                                                <div class="gd-progress-fill" style="width: {{ $class->completion_percent ?? 0 }}%"></div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($dashboardPreferences?->show_student_counts ?? true)
                                        <div class="gs-row-subtext">
                                            <span><i class="fa-solid fa-users me-1"></i>{{ $class->learner_count }} learners</span>
                                        </div>
                                    @endif

                                    @if (($dashboardPreferences?->show_class_health ?? true) || ($dashboardPreferences?->show_at_risk ?? true))
                                        <div class="gs-stat-group">
                                            @if ($dashboardPreferences?->show_class_health ?? true)
                                                <span class="gs-stat-chip"><i class="fa-solid fa-chart-line me-1"></i>Avg: {{ $class->avg_grade !== null ? $class->avg_grade : '-' }}</span>
                                                <span class="gs-stat-chip"><i class="fa-solid fa-square-check me-1"></i>Pass: {{ $class->passing_rate !== null ? $class->passing_rate . '%' : '-' }}</span>
                                            @endif

                                            @if ($dashboardPreferences?->show_at_risk ?? true)
                                                @php $atRiskCount = $classAtRiskCounts[$class->teaching_assignment_id] ?? 0; @endphp
                                                <span class="gs-stat-chip gs-stat-chip-risk">
                                                    <span class="gs-stat-chip-risk-main"><i class="fa-solid fa-triangle-exclamation me-1"></i>At-Risk: {{ $atRiskCount }}</span>
                                                    @if($atRiskCount > 0 && isset($classRiskReasons[$class->teaching_assignment_id]))
                                                        <span class="gs-stat-chip-risk-reason">{{ $classRiskReasons[$class->teaching_assignment_id] }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="gs-class-card-footer">
                                        <span class="gs-class-open-btn">
                                            Open Grade Sheet <i class="fa-solid fa-arrow-right ms-1"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="gs-chart-empty">
                                <i class="fa-solid fa-chalkboard gs-chart-empty-icon"></i>
                                <p class="mb-0">No active classes found yet.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Grading Progress Section --}}
        @if ($dashboardPreferences?->show_grading_progress ?? true)
            <div class="gd-panel mt-4">
                <p class="gs-panel-title mb-3">Grading Progress</p>
                <x-ui.table>
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Subject</th>
                            @if ($dashboardPreferences?->show_student_counts ?? true)
                                <th>Students</th>
                                <th>Encoded</th>
                            @endif
                            @if ($dashboardPreferences?->show_progress_indicators ?? true)
                                <th>Completion</th>
                            @endif
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($classes as $class)
                            <tr>
                                <td>Grade {{ $class->grade_level }} - {{ $class->section_name }}</td>
                                <td>{{ $class->subject_name }}</td>
                                @if ($dashboardPreferences?->show_student_counts ?? true)
                                    <td>{{ $class->learner_count }}</td>
                                    <td>{{ $class->encoded_count }} / {{ $class->learner_count }}</td>
                                @endif
                                @if ($dashboardPreferences?->show_progress_indicators ?? true)
                                    <td>
                                        <div class="gd-progress-wrap">
                                            <div class="d-flex justify-content-between">
                                                <span class="gd-progress-label">
                                                    {{ $class->completion_percent !== null ? $class->completion_percent . '%' : 'â€”' }}
                                                </span>
                                            </div>
                                            <div class="gd-progress-bar">
                                                <div class="gd-progress-fill" style="width: {{ $class->completion_percent ?? 0 }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                @endif
                                <td>
                                    <span class="gs-badge {{ $class->status === 'Complete' ? 'gs-badge-success' : ($class->status === 'In Progress' ? 'gs-badge-warning' : 'gs-badge') }}">
                                        {{ $class->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No active classes found yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </x-ui.table>
            </div>
        @endif


    </div>
</x-layouts.teacher>
