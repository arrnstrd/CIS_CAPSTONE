<x-layouts.teacher>

<x-slot name="pageName">
    <span class="page-title-icon">
        <i class="fa-solid fa-triangle-exclamation"></i>
        At-Risk Detail
    </span>
</x-slot>

<x-slot name="subtitle">
    Risk evaluation, active indicators, and intervention monitoring for this student.
</x-slot>

@include('pov.teacher.my-classes.partials.common.grading-breadcrumb', [
    'crumbs' => [
        [
            'label' => 'At-Risk Registry',
            'url'   => route('teacher.grading-system.at-risk'),
        ],
        [
            'label' => $studentName,
            'url'   => '#',
        ],
    ]
])

@if (session('success'))
    <div class="gs-note-banner mb-3" style="background-color: #e1f5ee; color: #085041;">
        <i class="fa-solid fa-circle-check me-2"></i>
        {{ session('success') }}
    </div>
@endif

@php
    $riskLevel = $riskData['risk_level'] ?? 'Low';
    $riskScore = $riskData['risk_score'] ?? 0;

    $riskBadgeClass = match ($riskLevel) {
        'High'     => 'gs-badge-danger',
        'Moderate' => 'gs-badge-warning',
        default    => 'gs-badge-success',
    };

    $headerAccentStyle = match ($riskLevel) {
        'High'     => 'border-left: 4px solid #dc3545;',
        'Moderate' => 'border-left: 4px solid #ffc107;',
        default    => 'border-left: 4px solid #198754;',
    };
@endphp


{{-- ============================================================
     SECTION 1 — AT-RISK HEADER
============================================================ --}}
<div class="gs-panel mb-3" style="{{ $headerAccentStyle }}">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">

        <div>
            <p class="mb-1" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: {{ $riskLevel === 'High' ? '#791f1f' : ($riskLevel === 'Moderate' ? '#854f0b' : '#085041') }};">
                At-Risk Monitoring
            </p>
            <p class="gs-panel-title mb-1">{{ $studentName }}</p>
            <p class="text-muted small mb-0">
                {{ $subjectName ?? 'General Subject' }}
                @if ($selectedSection || $enrollment->section)
                    <span class="mx-1">&middot;</span>
                    {{ $selectedSection ?? $enrollment->section->name }}
                @endif
                @if ($selectedGrade || $enrollment->section)
                    <span class="mx-1">&middot;</span>
                    Grade {{ $selectedGrade ?? $enrollment->section->grade_level }}
                @endif
                @if ($currentPeriod)
                    <span class="mx-1">&middot;</span>
                    {{ $currentPeriod->name ?? ('Term ' . ($currentPeriod->sequence ?? '—')) }}
                @endif
            </p>
        </div>

        <div class="d-flex align-items-center gap-3">

            <div class="text-md-end">
                <span class="gs-badge {{ $riskBadgeClass }}" style="font-size: 0.75rem; padding: 5px 12px;">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    {{ strtoupper($riskLevel) }} RISK
                </span>
                <p class="text-muted mb-0 mt-1" style="font-size: 0.75rem;">
                    Score: <span class="fw-semibold text-dark">{{ number_format((float) $riskScore, 0) }} / 100</span>
                </p>
            </div>

            <x-ui.backButton />

        </div>

    </div>

</div>


{{-- ============================================================
     SECTION 2 — RISK SNAPSHOT
============================================================ --}}
<div class="snapshot-grid mb-3">

    {{-- Risk Level --}}
    <div class="snapshot-card">
        <div class="snapshot-icon {{ $riskLevel === 'High' ? 'danger' : ($riskLevel === 'Moderate' ? 'warning' : '') }}">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="snapshot-content">
            <span class="snapshot-label">Risk Level</span>
            <strong class="{{ $riskLevel === 'High' ? 'text-danger' : ($riskLevel === 'Moderate' ? 'text-warning' : 'text-success') }}">
                {{ $riskLevel }}
            </strong>
        </div>
    </div>

    {{-- Risk Score --}}
    <div class="snapshot-card">
        <div class="snapshot-icon">
            <i class="fa-solid fa-gauge-high"></i>
        </div>
        <div class="snapshot-content">
            <span class="snapshot-label">Risk Score</span>
            <strong>{{ number_format((float) $riskScore, 0) }} / 100</strong>
        </div>
    </div>

    {{-- Current Grade --}}
    <div class="snapshot-card">
        <div class="snapshot-icon {{ ($currentGrade !== null && $currentGrade < 75) ? 'danger' : '' }}">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="snapshot-content">
            <span class="snapshot-label">Current Grade</span>
            <strong class="{{ ($currentGrade !== null && $currentGrade < 75) ? 'text-danger' : '' }}">
                {{ $currentGrade !== null ? number_format($currentGrade, 1) : '—' }}
            </strong>
            <small>{{ ($currentGrade !== null && $currentGrade >= 75) ? 'Passing' : ($currentGrade !== null ? 'Below 75' : 'No record') }}</small>
        </div>
    </div>

    {{-- Attendance Rate --}}
    <div class="snapshot-card">
        <div class="snapshot-icon {{ $attendanceRate < 85 ? 'danger' : '' }}">
            <i class="fa-solid fa-calendar-xmark"></i>
        </div>
        <div class="snapshot-content">
            <span class="snapshot-label">Attendance Rate</span>
            <strong class="{{ $attendanceRate < 85 ? 'text-danger' : '' }}">
                {{ number_format((float) $attendanceRate, 1) }}%
            </strong>
            <small>{{ $attendanceRate < 85 ? 'Low Attendance' : 'Normal' }}</small>
        </div>
    </div>

</div>


{{-- ============================================================
     SECTION 3 — RISK ANALYSIS (PRIMARY CONTENT)
============================================================ --}}
<div class="gs-panel mb-3">

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <p class="text-muted mb-1" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #791f1f;">
                Risk Analysis
            </p>
            <p class="gs-panel-title mb-1">Why is this student flagged as at-risk?</p>
            <p class="text-muted small mb-0">Active indicators currently contributing to this student's risk status.</p>
        </div>
    </div>

    @if (!empty($activeIndicators))
        <div class="risk-list">
            @foreach ($activeIndicators as $indicator)
                <div class="risk-item">
                    <div class="risk-item-icon">
                        <i class="fa-solid {{ $indicator['icon'] }}"></i>
                    </div>
                    <div class="risk-item-content">
                        <strong>
                            {{ $indicator['label'] }}
                            <span class="gs-badge gs-badge-danger ms-1" style="font-size: 0.6rem; vertical-align: middle;">ACTIVE</span>
                        </strong>
                        <p>{{ $indicator['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="no-risk">
            <div class="no-risk-check">
                <i class="fa-solid fa-check"></i>
            </div>
            <strong>No active risk indicators</strong>
            <p>No specific risk indicators are currently active for this student in this term.</p>
        </div>
    @endif

</div>


{{-- ============================================================
     SECTION 4 — ACADEMIC EVIDENCE
============================================================ --}}
<div class="performance-layout mb-3">

    {{-- Left: Term Grade Evidence --}}
    <div class="profile-card performance-card">

        <div class="profile-card-header simple">
            <div>
                <p class="gs-panel-title mb-1">Academic Evidence</p>
                <p class="text-muted small mb-0">Grade data supporting the current risk assessment.</p>
            </div>
        </div>

        <div class="academic-table-wrapper">
            <table class="academic-table">
                <thead>
                    <tr>
                        <th style="padding-left: 18px;">Term</th>
                        <th>Average</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gradeHistory as $history)
                        <tr>
                            <td style="padding-left: 18px;"><strong>{{ $history['term'] }}</strong></td>
                            <td>
                                <strong class="{{ ($history['average'] !== null && $history['average'] < 75) ? 'text-danger' : '' }}">
                                    {{ $history['average'] !== null ? number_format($history['average'], 1) : '—' }}
                                </strong>
                            </td>
                            <td>
                                @if ($history['average'] === null)
                                    <span class="grade-status missing">Unrecorded</span>
                                @elseif ($history['average'] >= 75)
                                    <span class="grade-status passing">Passing</span>
                                @else
                                    <span class="grade-status failing">Below Passing</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty-table">
                                <div class="empty-table-content">
                                    <i class="fa-solid fa-folder-open"></i>
                                    <strong>No grade history</strong>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding: 4px 18px 14px;">
            <div class="gs-rules-section">
                <div class="gs-rules-row">
                    <span class="gs-rules-label">Missing Grade Entries</span>
                    <span class="gs-rules-value {{ $missingGrades > 0 ? 'text-warning' : '' }}">{{ $missingGrades }}</span>
                </div>
            </div>
            <div class="gs-rules-section gs-rules-section-last">
                <div class="gs-rules-row">
                    <span class="gs-rules-label">Below Passing (&lt; 75)</span>
                    <span class="gs-rules-value {{ $belowPassing > 0 ? 'text-danger' : 'text-success' }}">{{ $belowPassing }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Right: Risk / Grade Trajectory --}}
    <div class="profile-card">
        <div class="profile-card-header simple">
            <div>
                <p class="gs-panel-title mb-1">
                    Grade Trajectory
                    @if ($trend === 'Declining')
                        <span class="gs-badge gs-badge-danger ms-1" style="font-size: 0.6rem; vertical-align: middle;">Declining</span>
                    @elseif ($trend === 'Improving')
                        <span class="gs-badge gs-badge-success ms-1" style="font-size: 0.6rem; vertical-align: middle;">Improving</span>
                    @endif
                </p>
                <p class="text-muted small mb-0">Risk trend based on term averages.</p>
            </div>
        </div>
        <div class="grade-chart-wrapper" style="max-height: 240px;">
            @if (!empty($gradeHistory))
                <canvas id="gradeChart"></canvas>
            @else
                <div class="chart-empty-state">
                    <i class="fa-solid fa-chart-line fa-lg"></i>
                    <strong>No trend data available</strong>
                    <span>Grade trajectory will appear once more term data is recorded.</span>
                </div>
            @endif
        </div>
    </div>

</div>


{{-- ============================================================
     SECTION 5 — ATTENDANCE MONITORING
============================================================ --}}
<div class="profile-card attendance-card mb-3">

    <div class="profile-card-header simple">
        <div>
            <p class="gs-panel-title mb-1">Attendance Monitoring</p>
            <p class="text-muted small mb-0">Attendance concerns &amp; recent scans relevant to risk status.</p>
        </div>
        <div style="font-size: 0.78rem; text-align: right;">
            <span class="text-muted">Rate:</span>
            <span class="fw-semibold ms-1 {{ $attendanceRate < 85 ? 'text-danger' : 'text-dark' }}">{{ number_format((float) $attendanceRate, 1) }}%</span>
            &nbsp;
            <span class="gs-badge {{ $attendanceRate < 85 ? 'gs-badge-danger' : 'gs-badge-success' }}">
                {{ $attendanceRate < 85 ? 'Low Attendance' : 'Normal' }}
            </span>
        </div>
    </div>

    @if ($recentAttendance->count())
        <div class="academic-table-wrapper">
            <table class="academic-table">
                <thead>
                    <tr>
                        <th style="padding-left: 18px;">Scan Date</th>
                        <th>Scan Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentAttendance as $attendance)
                        <tr>
                            <td style="padding-left: 18px;">
                                <div class="attendance-date">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ \Carbon\Carbon::parse($attendance->scan_time)->format('M d, Y') }}
                                </div>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($attendance->scan_time)->format('h:i A') }}</td>
                            <td>
                                <span class="attendance-status">
                                    <i class="fa-solid fa-circle-check"></i>
                                    {{ $attendance->scan_type }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="simple-empty-state">
            <i class="fa-regular fa-calendar-xmark"></i>
            <strong>No attendance records</strong>
            <span>No recent attendance scans found for this monitoring period.</span>
        </div>
    @endif

</div>


{{-- ============================================================
     SECTION 6 — TEACHER RISK REMARKS
============================================================ --}}
<div class="gs-panel mb-3">

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <p class="text-muted mb-1" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #791f1f;">
                Risk Remarks
            </p>
            <p class="gs-panel-title mb-1">Teacher Risk Remarks</p>
            <p class="text-muted small mb-0">Observations specifically related to this student's current risk condition.</p>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addRemarkModal">
            <i class="fa-solid fa-plus me-1"></i> Add Remark
        </button>
    </div>

    @if ($riskRemarks->count())
        <div class="risk-list">
            @foreach ($riskRemarks as $remark)
                <div class="risk-item d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-start gap-2 flex-grow-1">
                        <div class="risk-item-icon">
                            <i class="fa-solid fa-comment-dots"></i>
                        </div>
                        <div class="risk-item-content">
                            <strong>
                                {{ $remark->teacher->full_name ?? 'Teacher' }}
                                <span class="text-muted fw-normal" style="font-size: 0.7rem;">
                                    &middot; {{ $remark->created_at->format('M d, Y h:i A') }}
                                </span>
                            </strong>
                            <p class="mb-0">{{ $remark->remark }}</p>
                        </div>
                    </div>

                    @if ($remark->teacher_id === auth()->user()?->teacher?->id)
                        <div class="d-flex align-items-center gap-1 ms-2 flex-shrink-0">
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary py-1 px-2 btn-edit-remark"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editRemarkModal"
                                    data-remark-id="{{ $remark->id }}"
                                    data-remark-text="{{ $remark->remark }}"
                                    data-update-url="{{ route('teacher.grading-system.at-risk.remarks.update', ['enrollmentId' => $enrollment->id, 'remarkId' => $remark->id]) }}"
                                    title="Edit Remark">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger py-1 px-2 btn-delete-remark"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteRemarkModal"
                                    data-delete-url="{{ route('teacher.grading-system.at-risk.remarks.destroy', ['enrollmentId' => $enrollment->id, 'remarkId' => $remark->id]) }}"
                                    title="Delete Remark">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="simple-empty-state">
            <i class="fa-regular fa-comment-dots"></i>
            <strong>No risk remarks recorded</strong>
            <span>Risk-specific observation remarks will appear here once added.</span>
        </div>
    @endif

</div>

@include('pov.teacher.at-risk.partials.add-remark-modal')
@include('pov.teacher.at-risk.partials.edit-remark-modal')
@include('pov.teacher.at-risk.partials.delete-remark-modal')


{{-- ============================================================
     SECTION 7 — RECOMMENDED SUPPORT
============================================================ --}}
<div class="gs-panel mb-3">

    <p class="gs-panel-title mb-3">Recommended Support</p>

    <div class="gs-rules-section">
        <div class="gs-rules-row">
            <div>
                <div class="fw-semibold text-dark" style="font-size: 0.82rem;">
                    <i class="fa-solid fa-users text-muted me-1"></i> Parent Consultation
                </div>
                <div class="text-muted" style="font-size: 0.7rem;">Recommended Action</div>
            </div>
            <span class="gs-badge gs-badge-warning">Schedule Meeting</span>
        </div>
    </div>

    <div class="gs-rules-section">
        <div class="gs-rules-row">
            <div>
                <div class="fw-semibold text-dark" style="font-size: 0.82rem;">
                    <i class="fa-solid fa-book-reader text-muted me-1"></i> Remedial Tutoring
                </div>
                <div class="text-muted" style="font-size: 0.7rem;">Recommended Action</div>
            </div>
            <span class="gs-badge gs-badge-warning">Assign Support</span>
        </div>
    </div>

    <div class="gs-rules-section gs-rules-section-last">
        <div class="gs-rules-row">
            <div>
                <div class="fw-semibold text-dark" style="font-size: 0.82rem;">
                    <i class="fa-solid fa-clipboard-check text-muted me-1"></i> Weekly Attendance Check
                </div>
                <div class="text-muted" style="font-size: 0.7rem;">Recommended Action</div>
            </div>
            <span class="gs-badge gs-badge-warning">Active Monitoring</span>
        </div>
    </div>

</div>


{{-- ============================================================
     SECTION 8 — MONITORING FOLLOW-UP
============================================================ --}}
<div class="gs-panel">

    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <p class="text-muted mb-1" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #791f1f;">
                Intervention Tracking
            </p>
            <p class="gs-panel-title mb-1">Monitoring Follow-Up</p>
            <p class="text-muted small mb-0">Record and track manual follow-ups, interventions, and consultation actions.</p>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addFollowUpModal">
            <i class="fa-solid fa-plus me-1"></i> Add Follow-Up
        </button>
    </div>

    @if ($followUps->count())
        <div class="risk-list">
            @foreach ($followUps as $followUp)
                <div class="risk-item d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-start gap-2 flex-grow-1">
                        <div class="risk-item-icon">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                        <div class="risk-item-content">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <strong>{{ $followUp->intervention }}</strong>
                                <span class="gs-badge {{ $followUp->status === 'Completed' ? 'gs-badge-success' : 'gs-badge-warning' }}" style="font-size: 0.65rem;">
                                    {{ $followUp->status }}
                                </span>
                            </div>
                            <div class="text-muted small mb-1" style="font-size: 0.75rem;">
                                <i class="fa-regular fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($followUp->follow_up_date)->format('M d, Y') }}
                                <span class="mx-1">&middot;</span>
                                <i class="fa-solid fa-user-tie me-1"></i>{{ $followUp->teacher->full_name ?? 'Teacher' }}
                            </div>
                            @if ($followUp->notes)
                                <p class="mb-0 text-secondary" style="font-size: 0.82rem; line-height: 1.4;">{{ $followUp->notes }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="simple-empty-state">
            <i class="fa-solid fa-bars-progress"></i>
            <strong>No follow-up records available</strong>
            <span>Click Add Follow-Up to record an intervention or monitoring action for this student.</span>
        </div>
    @endif

</div>

@include('pov.teacher.at-risk.partials.add-followup-modal')


{{-- CHART SCRIPT --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartEl = document.getElementById('gradeChart');
        if (!chartEl || typeof Chart === 'undefined') return;

        const labels     = @json(array_column($gradeHistory, 'term'));
        const values     = @json(array_column($gradeHistory, 'average'));
        const isDeclining = '{{ $trend }}' === 'Declining';

        if (!labels.length || !values.length) return;

        new Chart(chartEl.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Average Grade',
                    data: values,
                    borderColor: isDeclining ? '#791f1f' : '#2438b9',
                    backgroundColor: isDeclining ? 'rgba(121,31,31,0.07)' : 'rgba(36,56,185,0.07)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: isDeclining ? '#791f1f' : '#2438b9'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        min: 60,
                        max: 100,
                        ticks: { stepSize: 10, font: { size: 11 } },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });

        // Edit Remark Modal handler
        const editModal = document.getElementById('editRemarkModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;
                const remarkText = button.getAttribute('data-remark-text');
                const updateUrl = button.getAttribute('data-update-url');

                const form = document.getElementById('editRemarkForm');
                const textarea = document.getElementById('editRemarkText');

                if (form && updateUrl) form.action = updateUrl;
                if (textarea && remarkText !== null) textarea.value = remarkText;
            });
        }

        // Delete Remark Modal handler
        const deleteModal = document.getElementById('deleteRemarkModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;
                const deleteUrl = button.getAttribute('data-delete-url');

                const form = document.getElementById('deleteRemarkForm');
                if (form && deleteUrl) form.action = deleteUrl;
            });
        }
    });
</script>

</x-layouts.teacher>