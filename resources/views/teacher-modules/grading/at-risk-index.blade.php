<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Review students who may need attention based on grades, missing work, attendance, or declining performance. Risk scores are system-generated.
    </x-slot>

    <form method="GET" action="{{ route('teacher.grading-system.at-risk') }}" class="gs-filter-bar mb-3">
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
                <label class="gs-filter-label">Risk Level</label>
                <select name="risk_level" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Risk</option>
                    <option value="High" @selected($selectedRiskLevel === 'High')">High</option>
                    <option value="Moderate" @selected($selectedRiskLevel === 'Moderate')">Moderate</option>
                </select>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4">
            <div class="gs-stat-card gs-stat-card-success d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-success">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
                <div>
                    <p class="gs-stat-label gs-stat-label-success">Low Risk</p>
                    <p class="gs-stat-value gs-stat-success">{{ $stats['low'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="gs-stat-card" style="background-color: #FAEEDA; border-color: #f0dfb8;">
                <div class="d-flex align-items-center gap-3">
                    <span class="gs-stat-icon" style="background-color: #f0dfb8; color: #854F0B;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                    </span>
                    <div>
                        <p class="gs-stat-label" style="color: #854F0B;">Moderate Risk</p>
                        <p class="gs-stat-value" style="color: #854F0B;">{{ $stats['moderate'] }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="gs-stat-card gs-stat-card-danger d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
                <div>
                    <p class="gs-stat-label gs-stat-label-danger">High Risk</p>
                    <p class="gs-stat-value gs-stat-danger">{{ $stats['high'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="gs-panel">
        <p class="gs-panel-title">At-Risk Student Registry</p>
        <div class="table-panel">
            <table class="table table-hover mb-0 gs-atrisk-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Average (grade)</th>
                        <th>Overall Attendance</th>
                        <th>Risk Level</th>
                        <th>Risk Score</th>
                        <th>Indicators</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $s)
                        <tr>
                            <td>{{ $s->name }} <span class="gs-row-subtext">· {{ $s->student_number }}</span></td>
                            <td>{{ $s->grade_level }} - {{ $s->section_name }}<br><small class="text-muted">{{ $s->subject_name }}</small></td>
                            <td>{{ $s->avg_grade !== null ? $s->avg_grade : '—' }}</td>
                            <td>{{ $s->attendance_rate !== null ? $s->attendance_rate . '%' : '—' }}</td>
                            <td>
                                @php
                                    $riskClass = $s->risk_level === 'High' ? 'gs-badge-danger' : 'gs-badge-warning';
                                @endphp
                                <span class="gs-badge {{ $riskClass }}">{{ $s->risk_level }}</span>
                            </td>
                            <td>{{ $s->risk_score }}/100</td>
                            <td>
                                @php
                                    $activeIndicators = [];
                                    $indicatorLabels = [
                                        'low_grade' => 'Low Grade',
                                        'missing_grades' => 'Missing Grades',
                                        'low_attendance' => 'Low Attendance',
                                        'declining_performance' => 'Declining Performance'
                                    ];
                                    
                                    foreach ($s->indicators as $indicator => $isTrue) {
                                        if ($isTrue) {
                                            $activeIndicators[] = $indicatorLabels[$indicator];
                                        }
                                    }
                                @endphp
                                {{ !empty($activeIndicators) ? implode(', ', $activeIndicators) : '—' }}
                            </td>
                            <td>
                                <a href="{{ route('teacher.grading-system.student-profile.show', $s->enrollment_id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-user"></i>
                                    <span>View Profile</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No at-risk students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.teacher>