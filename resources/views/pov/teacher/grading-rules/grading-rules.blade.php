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

        <!-- 2. GRADING COMPONENTS PANEL -->
        <div class="gs-panel mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="gs-panel-title mb-0">Grading Components</p>

                @if ($selectedAssignment)
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

        <!-- 3. GRADING FORMULA & EXAMPLE PANEL -->
        <div class="gs-panel mb-3" id="formulaExamplePanel">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="gs-panel-title mb-0">Grading Formula & Example</p>

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

                        <button
                            type="submit"
                            form="restoreDefaultsForm"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            <i class="fa-solid fa-rotate-left me-1"></i>
                            Restore Default
                        </button>
                    </div>
                @endif
            </div>

            @if ($selectedAssignment)

                <div class="gs-rules-section mb-3 pb-0">
                    <div class="gs-rules-row">
                        <span class="gs-rules-label">Formula</span>
                    </div>

                    <div class="gr-formula-box">
                        <div class="gr-formula-view gr-formula-content font-monospace">
                            {{ $formula }}
                        </div>

                        <div class="gr-formula-edit d-none">
                            <div class="row g-2">

                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">
                                        Written Work Weight
                                    </label>

                                    <div class="input-group input-group-sm">
                                        <input
                                            type="number"
                                            class="form-control js-formula-weight"
                                            data-weight="written_work"
                                            value="{{ $gradingComponents['written_work']['weight'] }}"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                        >
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">
                                        Performance Task Weight
                                    </label>

                                    <div class="input-group input-group-sm">
                                        <input
                                            type="number"
                                            class="form-control js-formula-weight"
                                            data-weight="performance_task"
                                            value="{{ $gradingComponents['performance_task']['weight'] }}"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                        >
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">
                                        Term Assessment Weight
                                    </label>

                                    <div class="input-group input-group-sm">
                                        <input
                                            type="number"
                                            class="form-control js-formula-weight"
                                            data-weight="term_assessment"
                                            value="{{ $gradingComponents['term_assessment']['weight'] }}"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                        >
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                            </div>

                            <div class="mt-3">
                                <small class="text-muted">
                                    These weights are synchronized with the existing
                                    Grading Components configuration.
                                </small>
                            </div>
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

                    <div class="gr-example-box">

                        <div class="gr-example-row">
                            <span class="gr-example-label">Written Work:</span>

                            <span class="gr-example-value">
                                <span class="gr-example-view">
                                    {{ $workedExample['ww_score'] }}%
                                    {!! '&times;' !!}
                                    {{ $gradingComponents['written_work']['weight'] / 100 }}
                                    =
                                    {{ $workedExample['ww_contribution'] }}
                                </span>

                                <span class="gr-example-edit d-none">
                                    <input
                                        type="number"
                                        class="form-control form-control-sm js-example-score"
                                        data-score="ww"
                                        value="{{ $workedExample['ww_score'] }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                    >
                                </span>
                            </span>
                        </div>

                        <div class="gr-example-row">
                            <span class="gr-example-label">Performance Task:</span>

                            <span class="gr-example-value">
                                <span class="gr-example-view">
                                    {{ $workedExample['pt_score'] }}%
                                    {!! '&times;' !!}
                                    {{ $gradingComponents['performance_task']['weight'] / 100 }}
                                    =
                                    {{ $workedExample['pt_contribution'] }}
                                </span>

                                <span class="gr-example-edit d-none">
                                    <input
                                        type="number"
                                        class="form-control form-control-sm js-example-score"
                                        data-score="pt"
                                        value="{{ $workedExample['pt_score'] }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                    >
                                </span>
                            </span>
                        </div>

                        <div class="gr-example-row">
                            <span class="gr-example-label">Term Assessment:</span>

                            <span class="gr-example-value">
                                <span class="gr-example-view">
                                    {{ $workedExample['ta_score'] }}%
                                    {!! '&times;' !!}
                                    {{ $gradingComponents['term_assessment']['weight'] / 100 }}
                                    =
                                    {{ $workedExample['ta_contribution'] }}
                                </span>

                                <span class="gr-example-edit d-none">
                                    <input
                                        type="number"
                                        class="form-control form-control-sm js-example-score"
                                        data-score="ta"
                                        value="{{ $workedExample['ta_score'] }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                    >
                                </span>
                            </span>
                        </div>

                        <div class="gr-example-row gr-example-total">
                            <span class="gr-example-label">Final Grade:</span>

                            <span class="gr-example-value">
                                {{ $workedExample['ww_contribution'] }}
                                +
                                {{ $workedExample['pt_contribution'] }}
                                +
                                {{ $workedExample['ta_contribution'] }}
                                =
                                {{ $workedExample['final_grade'] }}
                            </span>
                        </div>

                    </div>

                    <div
                        class="d-flex justify-content-end gap-2 mt-3 d-none"
                        id="formulaEditActions"
                    >
                        <button
                            type="button"
                            class="btn btn-outline-secondary btn-sm"
                            id="cancelFormulaBtn"
                        >
                            Cancel
                        </button>

                        <button
                            type="button"
                            class="btn btn-primary btn-sm"
                            id="saveFormulaBtn"
                        >
                            <i class="fa-solid fa-floppy-disk me-1"></i>
                            Save
                        </button>
                    </div>
                </div>

            @else

                <div class="text-muted">
                    Select an assignment to view and configure the grading formula and worked example.
                </div>

            @endif

        </div>

        <!-- 4. OTHER RULES PANEL -->
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

        const formulaView = document.querySelector('.gr-formula-view');
        const formulaEdit = document.querySelector('.gr-formula-edit');
        const editFormulaBtn = document.getElementById('editFormulaBtn');
        const cancelFormulaBtn = document.getElementById('cancelFormulaBtn');
        const saveFormulaBtn = document.getElementById('saveFormulaBtn');
        const formulaEditActions = document.getElementById('formulaEditActions');

        const formulaWeightInputs = document.querySelectorAll('.js-formula-weight');
        const exampleScoreInputs = document.querySelectorAll('.js-example-score');
        const exampleViews = document.querySelectorAll('.gr-example-view');
        const finalExample = document.querySelector('.gr-example-total .gr-example-value');

        function getNumber(input, fallback = 0) {
            if (!input) {
                return fallback;
            }

            const value = parseFloat(input.value);

            return Number.isFinite(value) ? value : fallback;
        }

        function getFormulaWeight(component) {
            const input = document.querySelector(
                '.js-formula-weight[data-weight="' + component + '"]'
            );

            return getNumber(input);
        }

        function getExampleScore(component) {
            const input = document.querySelector(
                '.js-example-score[data-score="' + component + '"]'
            );

            return getNumber(input);
        }

        function updateFormulaExample() {
            const wwPercent = getFormulaWeight('written_work');
            const ptPercent = getFormulaWeight('performance_task');
            const taPercent = getFormulaWeight('term_assessment');

            const wwWeight = wwPercent / 100;
            const ptWeight = ptPercent / 100;
            const taWeight = taPercent / 100;

            const wwScore = getExampleScore('ww');
            const ptScore = getExampleScore('pt');
            const taScore = getExampleScore('ta');

            const wwContribution = wwScore * wwWeight;
            const ptContribution = ptScore * ptWeight;
            const taContribution = taScore * taWeight;

            const finalGrade = wwContribution + ptContribution + taContribution;

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

        function enterEditMode() {
            const currentWeights = {
                written_work: getNumber(wwInput),
                performance_task: getNumber(ptInput),
                term_assessment: getNumber(taInput)
            };

            formulaWeightInputs.forEach(function (input) {
                const component = input.dataset.weight;

                if (Object.prototype.hasOwnProperty.call(currentWeights, component)) {
                    input.value = currentWeights[component];
                }
            });

            if (formulaView) {
                formulaView.classList.add('d-none');
            }

            if (formulaEdit) {
                formulaEdit.classList.remove('d-none');
            }

            document.querySelectorAll('.gr-example-view').forEach(function (element) {
                element.classList.add('d-none');
            });

            document.querySelectorAll('.gr-example-edit').forEach(function (element) {
                element.classList.remove('d-none');
            });

            if (formulaEditActions) {
                formulaEditActions.classList.remove('d-none');
            }

            if (editFormulaBtn) {
                editFormulaBtn.classList.add('d-none');
            }

            updateFormulaExample();
        }

        function exitEditMode() {
            if (formulaView) {
                formulaView.classList.remove('d-none');
            }

            if (formulaEdit) {
                formulaEdit.classList.add('d-none');
            }

            document.querySelectorAll('.gr-example-view').forEach(function (element) {
                element.classList.remove('d-none');
            });

            document.querySelectorAll('.gr-example-edit').forEach(function (element) {
                element.classList.add('d-none');
            });

            if (formulaEditActions) {
                formulaEditActions.classList.add('d-none');
            }

            if (editFormulaBtn) {
                editFormulaBtn.classList.remove('d-none');
            }
        }

        if (editFormulaBtn) {
            editFormulaBtn.addEventListener('click', enterEditMode);
        }

        if (cancelFormulaBtn) {
            cancelFormulaBtn.addEventListener('click', function () {
                formulaWeightInputs.forEach(function (input) {
                    const component = input.dataset.weight;

                    if (component === 'written_work' && wwInput) {
                        input.value = wwInput.value;
                    }

                    if (component === 'performance_task' && ptInput) {
                        input.value = ptInput.value;
                    }

                    if (component === 'term_assessment' && taInput) {
                        input.value = taInput.value;
                    }
                });

                exitEditMode();
            });
        }

        if (saveFormulaBtn) {
            saveFormulaBtn.addEventListener('click', function () {
                const wwWeight = getFormulaWeight('written_work');
                const ptWeight = getFormulaWeight('performance_task');
                const taWeight = getFormulaWeight('term_assessment');

                const total = wwWeight + ptWeight + taWeight;

                if (Math.abs(total - 100) >= 0.01) {
                    alert('The sum of all grading weights must equal exactly 100%.');
                    return;
                }

                if (wwInput) {
                    wwInput.value = wwWeight;
                }

                if (ptInput) {
                    ptInput.value = ptWeight;
                }

                if (taInput) {
                    taInput.value = taWeight;
                }

                const gradingForm = document.getElementById('gradingRulesForm');

                if (gradingForm) {
                    gradingForm.requestSubmit();
                }
            });
        }

        formulaWeightInputs.forEach(function (input) {
            input.addEventListener('input', updateFormulaExample);
        });

        exampleScoreInputs.forEach(function (input) {
            input.addEventListener('input', updateFormulaExample);
        });

        updateFormulaExample();
    });
</script>

</x-layouts.teacher>