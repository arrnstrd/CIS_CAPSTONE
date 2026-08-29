<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Overview of your sections' grading performance.
    </x-slot>

    <div class="row g-3 mb-4">
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
    </div>

    <div class="gd-layout">
        <div class="gd-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="gs-panel-title mb-0">My Classes</p>
            </div>

            <div class="row g-3">
                @forelse ($classes as $class)
                    <div class="col-12 col-md-6 col-xl-4">
                        <a href="{{ route('teacher.grading-system.grade-sheet', $class->teaching_assignment_id) }}" class="text-decoration-none">
                            <div class="gs-panel gs-card-accent gs-card-accent-{{ (($loop->iteration - 1) % 4) + 1 }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <p class="gs-panel-title mb-0">Grade {{ $class->grade_level }} - {{ $class->section_name }}</p>
                                    <span class="gs-badge {{ $class->format_badge === 'SHS Format' ? 'gs-badge-warning' : 'gs-badge-success' }}">
                                        {{ $class->format_badge }}
                                    </span>
                                </div>
                                <p class="text-muted small mb-2">{{ $class->subject_name }}</p>

                                <div class="gd-progress-wrap">
                                    <div class="d-flex justify-content-between">
                                        <span class="gd-progress-label">Completion</span>
                                        <span class="gd-progress-label">
                                            {{ $class->completion_percent !== null ? $class->completion_percent . '%' : '—' }}
                                        </span>
                                    </div>
                                    <div class="gd-progress-bar">
                                        <div class="gd-progress-fill" style="width: {{ $class->completion_percent ?? 0 }}%"></div>
                                    </div>
                                </div>

                                <div class="gs-row-subtext">
                                    <span><i class="fa-solid fa-users me-1"></i>{{ $class->learner_count }} learners</span>
                                </div>

                                <div class="gs-row-subtext mt-2">
                                    <span class="me-3">Average Grade: {{ $class->avg_grade !== null ? $class->avg_grade : '—' }}</span>
                                    <span class="me-3">Passing Rate: {{ $class->passing_rate !== null ? $class->passing_rate . '%' : '—' }}</span>
                                    @php $atRiskCount = $classAtRiskCounts[$class->teaching_assignment_id] ?? 0; @endphp
                                    <span class="me-3">
                                        At-Risk: {{ $atRiskCount }}
                                        @if($atRiskCount > 0)
                                            <span class="text-muted small">({{ $classRiskReasons[$class->teaching_assignment_id] ?? 'various reasons' }})</span>
                                        @endif
                                    </span>
                                    <span class="gs-badge {{ $class->status === 'Complete' ? 'gs-badge-success' : ($class->status === 'In Progress' ? 'gs-badge-warning' : 'gs-badge') }}">
                                        {{ $class->status }}
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

    <div class="gd-panel mt-4">
        <p class="gs-panel-title mb-3">Grading Progress</p>
        <x-ui.table>
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Students</th>
                    <th>Encoded</th>
                    <th>Completion</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($classes as $class)
                    <tr>
                        <td>Grade {{ $class->grade_level }} - {{ $class->section_name }}</td>
                        <td>{{ $class->subject_name }}</td>
                        <td>{{ $class->learner_count }}</td>
                        <td>{{ $class->encoded_count }} / {{ $class->learner_count }}</td>
                        <td>
                            <div class="gd-progress-wrap">
                                <div class="d-flex justify-content-between">
                                    <span class="gd-progress-label">
                                        {{ $class->completion_percent !== null ? $class->completion_percent . '%' : '—' }}
                                    </span>
                                </div>
                                <div class="gd-progress-bar">
                                    <div class="gd-progress-fill" style="width: {{ $class->completion_percent ?? 0 }}%"></div>
                                </div>
                            </div>
                        </td>
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




</x-layouts.teacher>