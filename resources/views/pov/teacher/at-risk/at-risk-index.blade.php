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


    @include('pov.teacher.at-risk.partials.risk-summary-cards')


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

        <div>
            <button type="button" 
                    id="bulkInterveneBtn" 
                    class="btn btn-sm btn-primary d-none" 
                    style="font-weight: 500;">
                <i class="fa-solid fa-paper-plane me-1"></i>
                <span id="bulkInterveneText">Intervene (0 selected)</span>
            </button>
        </div>

    </div>


    <div class="table-panel" data-tour="teacher-at-risk-table">

        <div class="table-responsive">

            <table class="table table-hover mb-0 gs-atrisk-table align-middle">

                <thead>

                    <tr>
                        <th style="width: 38px;" class="text-center">
                            <input type="checkbox" id="selectAllStudents" class="form-check-input" style="cursor: pointer;" title="Select all students">
                        </th>
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

                            {{-- Checkbox --}}
                            <td class="text-center">
                                <input type="checkbox" 
                                       class="form-check-input student-row-chk" 
                                       value="{{ $s->enrollment_id }}" 
                                       style="cursor: pointer;"
                                       data-name="{{ $s->name }}">
                            </td>

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

                            <td colspan="9"
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

@include('pov.teacher.at-risk.partials.intervention-modal')

{{-- Selection management script for bulk intervention --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllStudents');
    const rowCheckboxes = document.querySelectorAll('.student-row-chk');
    const bulkBtn = document.getElementById('bulkInterveneBtn');
    const bulkText = document.getElementById('bulkInterveneText');

    function updateBulkButton() {
        const checked = Array.from(rowCheckboxes).filter(cb => cb.checked);
        const count = checked.length;
        if (count > 0) {
            bulkBtn.classList.remove('d-none');
            bulkText.textContent = `Intervene (${count} selected)`;
            bulkBtn.disabled = false;
        } else {
            bulkBtn.classList.add('d-none');
            bulkText.textContent = `Intervene (0 selected)`;
            bulkBtn.disabled = true;
        }

        if (selectAll) {
            selectAll.checked = rowCheckboxes.length > 0 && checked.length === rowCheckboxes.length;
            selectAll.indeterminate = checked.length > 0 && checked.length < rowCheckboxes.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            rowCheckboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            updateBulkButton();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkButton);
    });

    if (bulkBtn) {
        bulkBtn.addEventListener('click', function () {
            const selectedIds = Array.from(rowCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            if (selectedIds.length === 0) return;

            if (typeof window.openInterventionModal === 'function') {
                window.openInterventionModal(selectedIds, true);
            }
        });
    }

    // Handle student deselection when removed from modal queue
    window.addEventListener('intervention-student-removed', function (e) {
        const enrollmentId = e.detail?.enrollment_id;
        if (!enrollmentId) return;
        rowCheckboxes.forEach(cb => {
            if (cb.value == enrollmentId) {
                cb.checked = false;
            }
        });
        updateBulkButton();
    });
});
</script>

</x-layouts.teacher>
