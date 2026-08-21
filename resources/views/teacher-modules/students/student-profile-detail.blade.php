<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Student profile overview.
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'studentprofile'])

    @include('teacher-modules.partials.grading-breadcrumb', ['crumbs' => [
        ['label' => 'Student Profile', 'url' => route('teacher.grading-system.student-profile')],
        ['label' => $enrollment->student->first_name . ' ' . $enrollment->student->last_name, 'url' => '#'],
    ]])

    <div class="gs-panel mb-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="gs-profile-avatar">
                    {{ strtoupper(substr($enrollment->student->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($enrollment->student->last_name ?? '?', 0, 1)) }}
                </span>
                <div>
                    <p class="gs-section-card-title mb-1">{{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}</p>
                    <p class="gs-section-card-meta mb-0">
                        {{ $enrollment->student->student_number }} · Grade {{ $enrollment->section->grade_level }} - {{ $enrollment->section->name }}
                    </p>
                </div>
            </div>
            @if ($riskLevel)
                @php
                    $riskClass = ['Low' => 'gs-badge-success', 'Moderate' => 'gs-badge-warning', 'High' => 'gs-badge-danger'][$riskLevel];
                @endphp
                <span class="gs-badge {{ $riskClass }}" style="font-size: 0.8rem; padding: 6px 14px;">{{ $riskLevel }} Risk</span>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-neutral"><i class="fa-solid fa-chart-line"></i></span>
                <div>
                    <p class="gs-stat-label">Overall Average</p>
                    <p class="gs-stat-value">{{ $overallAvg !== null ? $overallAvg : '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-neutral"><i class="fa-solid fa-calendar-check"></i></span>
                <div>
                    <p class="gs-stat-label">Scans Recorded</p>
                    <p class="gs-stat-value">{{ $presentCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-neutral"><i class="fa-solid fa-book"></i></span>
                <div>
                    <p class="gs-stat-label">Subjects</p>
                    <p class="gs-stat-value">{{ $subjects->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="gs-panel">
                <p class="gs-panel-title">Grades by Subject</p>
                <div class="table-panel">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Term 1</th>
                                <th>Term 2</th>
                                <th>Term 3</th>
                                <th>Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subjects as $subject)
                                @php
                                    $byTerm = $subject->periods->keyBy('term_label');
                                @endphp
                                <tr>
                                    <td>{{ $subject->subject_name }}</td>
                                    <td>{{ $byTerm->get('Term 1')->grade ?? '—' }}</td>
                                    <td>{{ $byTerm->get('Term 2')->grade ?? '—' }}</td>
                                    <td>{{ $byTerm->get('Term 3')->grade ?? '—' }}</td>
                                    <td class="fw-bold">{{ $subject->average !== null ? round($subject->average, 1) : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No subjects found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="gs-panel">
                <p class="gs-panel-title">Recent Scans</p>
                @forelse ($recentScans as $scan)
                    <div class="gs-metric-row">
                        <span class="gs-metric-label"><i class="fa-solid fa-qrcode"></i> {{ $scan->scan_time?->format('M d, Y') }}</span>
                        <span class="gs-metric-value">{{ $scan->scan_time?->format('h:i A') }}</span>
                    </div>
                @empty
                    <p class="gs-row-subtext mb-0">No scans recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>

</x-layouts.teacher>