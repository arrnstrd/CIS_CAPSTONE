<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-triangle-exclamation"></i>
            At-Risk Students
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">Review students who may need attention based on grades, missing work, attendance, or declining performance. Risk scores are system-generated.</span>
    </x-slot>

{{-- Filters --}}
<form method="GET"
      action="{{ route('teacher.grading-system.at-risk') }}"
      class="gs-filter-bar mb-3">

    <div class="row g-2 align-items-end">

        <div class="col-6 col-md-3">
            <label class="gs-filter-label">
                Grade Level
            </label>

            <select name="grade_level"
                    class="form-select form-select-sm"
                    onchange="this.form.submit()">

                <option value="">
                    All Grades
                </option>

                @foreach ($gradeLevels as $gl)
                    <option value="{{ $gl }}"
                        @selected($selectedGradeLevel == $gl)>
                        Grade {{ $gl }}
                    </option>
                @endforeach

            </select>
        </div>

        <div class="col-6 col-md-3">
            <label class="gs-filter-label">
                Risk Level
            </label>

            <select name="risk_level"
                    class="form-select form-select-sm"
                    onchange="this.form.submit()">

                <option value="">
                    All Risk
                </option>

                <option value="High"
                    @selected($selectedRiskLevel === 'High')}>
                    High
                </option>

                <option value="Moderate"
                    @selected($selectedRiskLevel === 'Moderate')}>
                    Moderate
                </option>

            </select>
        </div>

    </div>
</form>


{{-- Risk Summary --}}
<div class="row g-3 mb-3">

    {{-- Low Risk --}}
    <div class="col-12 col-md-4">
        <div class="gs-stat-card gs-stat-card-success d-flex align-items-center gap-3 h-100">

            <span class="gs-stat-icon gs-stat-icon-success">
                <i class="fa-solid fa-shield-halved"></i>
            </span>

            <div>
                <p class="gs-stat-label gs-stat-label-success mb-1">
                    Low Risk
                </p>

                <p class="gs-stat-value gs-stat-success mb-0">
                    {{ $stats['low'] }}
                </p>
            </div>

        </div>
    </div>


    {{-- Moderate Risk --}}
    <div class="col-12 col-md-4">
        <div class="gs-stat-card d-flex align-items-center gap-3 h-100"
             style="background-color: #FAEEDA; border-color: #f0dfb8;">

            <span class="gs-stat-icon"
                  style="background-color: #f0dfb8; color: #854F0B;">

                <i class="fa-solid fa-circle-exclamation"></i>

            </span>

            <div>
                <p class="gs-stat-label mb-1"
                   style="color: #854F0B;">

                    Moderate Risk

                </p>

                <p class="gs-stat-value mb-0"
                   style="color: #854F0B;">

                    {{ $stats['moderate'] }}

                </p>
            </div>

        </div>
    </div>


    {{-- High Risk --}}
    <div class="col-12 col-md-4">
        <div class="gs-stat-card gs-stat-card-danger d-flex align-items-center gap-3 h-100">

            <span class="gs-stat-icon gs-stat-icon-danger">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </span>

            <div>
                <p class="gs-stat-label gs-stat-label-danger mb-1">
                    High Risk
                </p>

                <p class="gs-stat-value gs-stat-danger mb-0">
                    {{ $stats['high'] }}
                </p>
            </div>

        </div>
    </div>

</div>


{{-- At-Risk Student Registry --}}
<div class="gs-panel">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">

        <div>

            <p class="gs-panel-title mb-1">
                At-Risk Student Registry
            </p>

            <p class="text-muted small mb-0">
                Students currently identified as needing academic attention.
            </p>

        </div>

    </div>


    <div class="table-panel">

        <div class="table-responsive">

            <table class="table table-hover mb-0 gs-atrisk-table align-middle">

                <thead>

                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Average</th>
                        <th>Attendance</th>
                        <th>Risk Level</th>
                        <th>Risk Score</th>
                        <th>Indicators</th>
                        <th class="text-end">Action</th>
                    </tr>

                </thead>


                <tbody>

                    @forelse ($students as $s)

                        @php

                            $riskClass = match ($s->risk_level) {

                                'High' => 'gs-badge-danger',

                                'Moderate' => 'gs-badge-warning',

                                default => 'gs-badge-success',

                            };


                            $activeIndicators = [];


                            $indicatorLabels = [

                                'low_grade' =>
                                    'Low Grade',

                                'missing_grades' =>
                                    'Missing Grades',

                                'low_attendance' =>
                                    'Low Attendance',

                                'declining_performance' =>
                                    'Declining Performance',

                            ];


                            foreach ($s->indicators as $indicator => $isTrue) {

                                if (
                                    $isTrue &&
                                    isset($indicatorLabels[$indicator])
                                ) {

                                    $activeIndicators[] =
                                        $indicatorLabels[$indicator];

                                }

                            }

                        @endphp


                        <tr>

                            {{-- Student --}}
                            <td>

                                <div class="fw-semibold">
                                    {{ $s->name }}
                                </div>

                                <div class="gs-row-subtext">
                                    {{ $s->student_number }}
                                </div>

                            </td>


                            {{-- Class --}}
                            <td>

                                <div>
                                    Grade {{ $s->grade_level }}
                                    - {{ $s->section_name }}
                                </div>

                                <small class="text-muted">
                                    {{ $s->subject_name }}
                                </small>

                            </td>


                            {{-- Average --}}
                            <td>

                                <span class="fw-semibold">

                                    {{ $s->avg_grade !== null
                                        ? $s->avg_grade
                                        : '—' }}

                                </span>

                            </td>


                            {{-- Attendance --}}
                            <td>

                                {{ $s->attendance_rate !== null
                                    ? $s->attendance_rate . '%'
                                    : '—' }}

                            </td>


                            {{-- Risk Level --}}
                            <td>

                                <span class="gs-badge {{ $riskClass }}">
                                    {{ $s->risk_level }}
                                </span>

                            </td>


                            {{-- Risk Score --}}
                            <td>

                                <span class="fw-semibold">
                                    {{ $s->risk_score }}/100
                                </span>

                            </td>


                            {{-- Indicators --}}
                            <td>

                                @if (!empty($activeIndicators))

                                    <div class="d-flex flex-wrap gap-1">

                                        @foreach ($activeIndicators as $indicator)

                                            <span class="badge bg-light text-dark border">
                                                {{ $indicator }}
                                            </span>

                                        @endforeach

                                    </div>

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- Action --}}
                            <td class="text-end">

                                {{-- IMPORTANT:
                                     This now uses the dedicated
                                     At-Risk detail route instead
                                     of the normal Student Profile. --}}

                                <a href="{{ route('teacher.grading-system.at-risk.show', [
                                    'enrollmentId' => $s->enrollment_id,
                                ]) }}"
                                   class="btn btn-sm btn-outline-secondary">

                                    <i class="fas fa-shield-halved me-1"></i>

                                    <span>
                                        View Risk Detail
                                    </span>

                                </a>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="8"
                                class="text-center text-muted py-5">

                                <div class="mb-2">

                                    <i class="fa-solid fa-shield-check fa-2x"></i>

                                </div>

                                <div class="fw-semibold">
                                    No at-risk students found.
                                </div>

                                <div class="small">
                                    Try adjusting the filters or check again later.
                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
```

</x-layouts.teacher>
