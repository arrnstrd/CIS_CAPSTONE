<x-layouts.teacher>


<x-slot name="pageName">
    <span class="page-title-icon">
        <i class="fa-solid fa-scale-balanced"></i>
        Grading Rules
    </span>
</x-slot>

<x-slot name="subtitle">
    <span class="page-title-subtitle">{{ $policyName }} {!! "&middot;" !!} {{ $schoolYear }}</span>
</x-slot>

@if (session('success'))
    <div class="gs-note-banner mb-3" style="background-color: #e1f5ee; color: #085041;">
        <i class="fa-solid fa-circle-check"></i>
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="gs-note-banner mb-3" style="background-color: #fde8e8; color: #9b1c1c;">
        <i class="fa-solid fa-circle-exclamation"></i>
        {{ session('error') }}
    </div>
@endif

@if ($activeAssignments->count() > 1)
    <form method="GET" action="{{ route('teacher.grading-system.grading-rules') }}" class="gs-filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-6 col-lg-4">
                <label for="teachingAssignmentSelect" class="gs-filter-label">Class / Section &amp; Subject</label>
                <select id="teachingAssignmentSelect" name="teaching_assignment_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach ($activeAssignments as $ta)
                        <option value="{{ $ta->id }}" @selected($selectedAssignment && $selectedAssignment->id == $ta->id)>
                            Grade {{ $ta->section->grade_level }} - {{ $ta->section->name }} &middot; {{ $ta->subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
@endif

<div class="gd-layout">

    <div class="gd-content">

        <!-- 1. SUMMARY STRIP -->
        <div class="row g-3 gr-summary-strip">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="gr-summary-card">
                    <span class="gs-stat-icon gs-stat-icon-neutral">
                        <i class="fa-solid fa-file-shield"></i>
                    </span>
                    <div>
                        <p class="gs-stat-label">Policy Name</p>
                        <p class="gr-summary-value">{{ $policyName }}</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="gr-summary-card">
                    <span class="gs-stat-icon gs-stat-icon-neutral">
                        <i class="fa-solid fa-calendar"></i>
                    </span>
                    <div>
                        <p class="gs-stat-label">School Year</p>
                        <p class="gr-summary-value">{{ $schoolYear }}</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="gr-summary-card">
                    <span class="gs-stat-icon gs-stat-icon-neutral">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </span>
                    <div>
                        <p class="gs-stat-label">Last Updated</p>
                        <p class="gr-summary-value">{{ $lastUpdated }}</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="gr-summary-card">
                    <span class="gs-stat-icon gs-stat-icon-neutral">
                        <i class="fa-solid fa-user-tie"></i>
                    </span>
                    <div>
                        <p class="gs-stat-label">Configured By</p>
                        <p class="gr-summary-value">{{ $configuredBy }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. GRADING CONFIGURATION PANEL -->
        <div class="gs-panel mb-3" id="gradingConfigurationPanel" data-tour="teacher-grading-rules-weights">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="gs-panel-title mb-0">Grading Configuration</p>

                @if ($selectedAssignment)
                    <div class="d-flex gap-2">
                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            id="editFormulaBtn"
                        >
                            <i class="fa-solid fa-pen me-1"></i>
                            Edit
                        </button>

                        <form
                            method="POST"
                            action="{{ route('teacher.grading-system.grading-rules.restore-defaults') }}"
                            id="restoreDefaultsForm"
                            class="mb-0"
                        >
                            @csrf

                            <input
                                type="hidden"
                                name="teaching_assignment_id"
                                value="{{ $selectedAssignment->id }}"
                            >

                            <button type="submit" class="btn btn-outline-secondary btn-sm">
                                <i class="fa-solid fa-rotate-left me-1"></i>
                                Restore Default
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            @if ($selectedAssignment)
                <form
                    method="POST"
                    action="{{ route('teacher.grading-system.grading-rules.update') }}"
                    id="gradingRulesForm"
                >
                    @csrf
                    @method('PUT')

                    <input
                        type="hidden"
                        name="teaching_assignment_id"
                        value="{{ $selectedAssignment->id }}"
                    >

                    <!-- Sub-section: Grading Components -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="gs-rules-heading mb-0">Grading Components</span>
                        </div>

                        <div class="gr-component-row">
                            <p class="gr-component-name mb-0">
                                <span
                                    class="gr-component-dot"
                                    style="background-color: {{ $gradingComponents['written_work']['color'] }};"
                                ></span>
                                Written Work (WW)
                            </p>

                            <div class="gr-component-weight-wrap">
                                <div class="gr-progress-bar">
                                    <div
                                        class="gr-weight-bar js-weight-bar"
                                        data-component="written_work"
                                        style="width: {{ $gradingComponents['written_work']['weight'] }}%; background-color: {{ $gradingComponents['written_work']['color'] }};"
                                    ></div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <input
                                        type="number"
                                        name="written_work"
                                        class="form-control form-control-sm weight-input"
                                        value="{{ $gradingComponents['written_work']['weight'] }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        style="width: 90px;"
                                        required
                                    >
                                    <span>%</span>
                                </div>
                            </div>
                        </div>

                        <div class="gr-component-row">
                            <p class="gr-component-name mb-0">
                                <span
                                    class="gr-component-dot"
                                    style="background-color: {{ $gradingComponents['performance_task']['color'] }};"
                                ></span>
                                Performance Task (PT)
                            </p>

                            <div class="gr-component-weight-wrap">
                                <div class="gr-progress-bar">
                                    <div
                                        class="gr-weight-bar js-weight-bar"
                                        data-component="performance_task"
                                        style="width: {{ $gradingComponents['performance_task']['weight'] }}%; background-color: {{ $gradingComponents['performance_task']['color'] }};"
                                    ></div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <input
                                        type="number"
                                        name="performance_task"
                                        class="form-control form-control-sm weight-input"
                                        value="{{ $gradingComponents['performance_task']['weight'] }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        style="width: 90px;"
                                        required
                                    >
                                    <span>%</span>
                                </div>
                            </div>
                        </div>

                        <div class="gr-component-row">
                            <p class="gr-component-name mb-0">
                                <span
                                    class="gr-component-dot"
                                    style="background-color: {{ $gradingComponents['term_assessment']['color'] }};"
                                ></span>
                                Term Assessment (EX)
                            </p>

                            <div class="gr-component-weight-wrap">
                                <div class="gr-progress-bar">
                                    <div
                                        class="gr-weight-bar js-weight-bar"
                                        data-component="term_assessment"
                                        style="width: {{ $gradingComponents['term_assessment']['weight'] }}%; background-color: {{ $gradingComponents['term_assessment']['color'] }};"
                                    ></div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <input
                                        type="number"
                                        name="term_assessment"
                                        class="form-control form-control-sm weight-input"
                                        value="{{ $gradingComponents['term_assessment']['weight'] }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        style="width: 90px;"
                                        required
                                    >
                                    <span>%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Divider separating components from formula & example -->
                    <div class="border-top my-4"></div>

                    <!-- Sub-section: Grading Formula & Example -->
                    <div class="mb-3" id="formulaExamplePanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="gs-rules-heading mb-0">Grading Formula &amp; Example</span>
                        </div>

                        <div class="gs-rules-section mb-3 pb-0">
                            <div class="gs-rules-row">
                                <span class="gs-rules-label">Formula</span>
                            </div>

                            <div class="gr-formula-box">
                                <div class="gr-formula-view gr-formula-content font-monospace">
                                    {{ $formula }}
                                </div>
                            </div>
                        </div>

                        <div class="gr-formula-highlight">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-calculator text-primary small"></i>
                                    <span class="fw-bold small text-dark">Worked Example</span>
                                </div>
                            </div>

                            <div
                                class="gr-example-box"
                                data-ww-score="{{ $workedExample['ww_score'] ?? 85 }}"
                                data-pt-score="{{ $workedExample['pt_score'] ?? 90 }}"
                                data-ta-score="{{ $workedExample['ta_score'] ?? 85 }}"
                            >
                                <div class="gr-example-row">
                                    <span class="gr-example-label">Written Work:</span>
                                    <span class="gr-example-value">
                                        <span class="gr-example-view">
                                            {{ $workedExample['ww_score'] ?? 85 }}%
                                            {!! '&times;' !!}
                                            {{ isset($gradingComponents['written_work']['weight']) ? $gradingComponents['written_work']['weight'] / 100 : 0 }}
                                            =
                                            {{ $workedExample['ww_contribution'] ?? 0 }}
                                        </span>
                                    </span>
                                </div>

                                <div class="gr-example-row">
                                    <span class="gr-example-label">Performance Task:</span>
                                    <span class="gr-example-value">
                                        <span class="gr-example-view">
                                            {{ $workedExample['pt_score'] ?? 90 }}%
                                            {!! '&times;' !!}
                                            {{ isset($gradingComponents['performance_task']['weight']) ? $gradingComponents['performance_task']['weight'] / 100 : 0 }}
                                            =
                                            {{ $workedExample['pt_contribution'] ?? 0 }}
                                        </span>
                                    </span>
                                </div>

                                <div class="gr-example-row">
                                    <span class="gr-example-label">Term Assessment:</span>
                                    <span class="gr-example-value">
                                        <span class="gr-example-view">
                                            {{ $workedExample['ta_score'] ?? 85 }}%
                                            {!! '&times;' !!}
                                            {{ isset($gradingComponents['term_assessment']['weight']) ? $gradingComponents['term_assessment']['weight'] / 100 : 0 }}
                                            =
                                            {{ $workedExample['ta_contribution'] ?? 0 }}
                                        </span>
                                    </span>
                                </div>

                                <div class="gr-example-row gr-example-total">
                                    <span class="gr-example-label">Final Grade:</span>
                                    <span class="gr-example-value">
                                        {{ $workedExample['ww_contribution'] ?? 0 }}
                                        +
                                        {{ $workedExample['pt_contribution'] ?? 0 }}
                                        +
                                        {{ $workedExample['ta_contribution'] ?? 0 }}
                                        =
                                        {{ $workedExample['final_grade'] ?? 0 }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div>
                            <strong>Total:</strong>
                            <span id="weightTotal">100.00%</span>
                            <span id="weightTotalStatus" class="ms-2 small"></span>
                        </div>

                        <div class="d-flex gap-2">
                            <button
                                type="submit"
                                class="btn btn-primary btn-sm"
                                id="saveGradingRulesBtn"
                            >
                                <i class="fa-solid fa-floppy-disk me-1"></i>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            @else
                <div class="text-muted">
                    Select an assignment to configure grading weights.
                </div>
            @endif
        </div>

        <!-- 3. OTHER RULES PANEL -->
        <div class="gs-panel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="gs-panel-title mb-0">Other Rules</p>
            </div>

            <div class="gr-rules-grid">
                @foreach ($otherRules as $rule)
                    <div class="gr-rule-card">
                        <div class="gr-rule-header">
                            <i class="fa-solid fa-circle-info"></i>
                            <span class="gr-rule-label">{{ $rule['rule'] }}</span>
                        </div>

                        <p class="gr-rule-value">{{ $rule['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wwInput = document.querySelector('input[name="written_work"]');
        const ptInput = document.querySelector('input[name="performance_task"]');
        const taInput = document.querySelector('input[name="term_assessment"]');

        const formulaView = document.querySelector('.gr-formula-content') || document.querySelector('.gr-formula-view');
        const exampleViews = document.querySelectorAll('.gr-example-view');
        const finalExample = document.querySelector('.gr-example-total .gr-example-value');
        const exampleBox = document.querySelector('.gr-example-box');

        const totalElement = document.getElementById('weightTotal');
        const totalStatusElement = document.getElementById('weightTotalStatus');
        const saveButton = document.getElementById('saveGradingRulesBtn');
        const editFormulaBtn = document.getElementById('editFormulaBtn');

        function getWeight(input, fallback = 0) {
            if (!input) {
                return fallback;
            }

            const value = parseFloat(input.value);

            return Number.isFinite(value) ? value : fallback;
        }

        function updateFormulaAndExample() {
            const wwPercent = getWeight(wwInput);
            const ptPercent = getWeight(ptInput);
            const taPercent = getWeight(taInput);

            const wwWeight = wwPercent / 100;
            const ptWeight = ptPercent / 100;
            const taWeight = taPercent / 100;

            const total = wwPercent + ptPercent + taPercent;

            if (totalElement) {
                totalElement.textContent = total.toFixed(2) + '%';
            }

            if (totalStatusElement) {
                if (Math.abs(total - 100) < 0.01) {
                    totalStatusElement.textContent = 'Valid';
                    totalStatusElement.className = 'ms-2 small text-success';
                } else {
                    totalStatusElement.textContent = 'Must equal 100%';
                    totalStatusElement.className = 'ms-2 small text-danger';
                }
            }

            if (saveButton) {
                saveButton.disabled = Math.abs(total - 100) >= 0.01;
            }

            document.querySelectorAll('.js-weight-bar').forEach(function (bar) {
                const component = bar.dataset.component;

                if (component === 'written_work') {
                    bar.style.width = Math.max(0, Math.min(100, wwPercent)) + '%';
                } else if (component === 'performance_task') {
                    bar.style.width = Math.max(0, Math.min(100, ptPercent)) + '%';
                } else if (component === 'term_assessment') {
                    bar.style.width = Math.max(0, Math.min(100, taPercent)) + '%';
                }
            });

            if (formulaView) {
                formulaView.textContent =
                    'Final Grade = (WW Average ' + '\u00D7' + ' ' +
                    wwWeight.toFixed(2) +
                    ') + (PT Average ' + '\u00D7' + ' ' +
                    ptWeight.toFixed(2) +
                    ') + (TA Average ' + '\u00D7' + ' ' +
                    taWeight.toFixed(2) +
                    ')';
            }

            const wwScore = exampleBox ? parseFloat(exampleBox.dataset.wwScore || 85) : 85;
            const ptScore = exampleBox ? parseFloat(exampleBox.dataset.ptScore || 90) : 90;
            const taScore = exampleBox ? parseFloat(exampleBox.dataset.taScore || 85) : 85;

            const wwContribution = wwScore * wwWeight;
            const ptContribution = ptScore * ptWeight;
            const taContribution = taScore * taWeight;

            const finalGrade = wwContribution + ptContribution + taContribution;

            if (exampleViews[0]) {
                exampleViews[0].textContent =
                    wwScore.toFixed(2).replace(/\.00$/, '') +
                    '% ' + '\u00D7' + ' ' +
                    wwWeight.toFixed(2) +
                    ' = ' +
                    wwContribution.toFixed(2);
            }

            if (exampleViews[1]) {
                exampleViews[1].textContent =
                    ptScore.toFixed(2).replace(/\.00$/, '') +
                    '% ' + '\u00D7' + ' ' +
                    ptWeight.toFixed(2) +
                    ' = ' +
                    ptContribution.toFixed(2);
            }

            if (exampleViews[2]) {
                exampleViews[2].textContent =
                    taScore.toFixed(2).replace(/\.00$/, '') +
                    '% ' + '\u00D7' + ' ' +
                    taWeight.toFixed(2) +
                    ' = ' +
                    taContribution.toFixed(2);
            }

            if (finalExample) {
                finalExample.textContent =
                    wwContribution.toFixed(2) +
                    ' + ' +
                    ptContribution.toFixed(2) +
                    ' + ' +
                    taContribution.toFixed(2) +
                    ' = ' +
                    finalGrade.toFixed(2);
            }
        }

        if (editFormulaBtn) {
            editFormulaBtn.addEventListener('click', function () {
                if (wwInput) {
                    wwInput.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    setTimeout(function () {
                        wwInput.focus();
                        wwInput.select();
                    }, 300);
                }
            });
        }

        [wwInput, ptInput, taInput].forEach(function (input) {
            if (input) {
                input.addEventListener('input', updateFormulaAndExample);
            }
        });

        updateFormulaAndExample();
    });
</script>

</x-layouts.teacher>