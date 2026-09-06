<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-scale-balanced"></i>
            Grading Rules
        </span>
    </x-slot>
    <x-slot name="subtitle">
        <span class="page-title-subtitle">{{ $policyName }} · {{ $schoolYear }}</span>
    </x-slot>

    <div class="mb-3">
        <a href="{{ route('teacher.grading-system.dashboard') }}" class="gr-back-btn">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
        <span class="gs-panel-title ms-2">Grading Rules</span>
    </div>

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
                </div>
                @foreach ($gradingComponents as $key => $component)
                    <div class="gr-component-row">
                        <p class="gr-component-name">
                            <span class="gr-component-dot" style="background-color: {{ $component['color'] }};"></span>
                            {{ $component['name'] }}
                        </p>
                        <div class="gr-component-weight-wrap">
                            <div class="gr-progress-bar">
                                <div class="gr-weight-bar" style="width: {{ $component['weight'] }}%; background-color: {{ $component['color'] }};"></div>
                            </div>
                            <span class="gr-component-percent">{{ $component['weight'] }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- 3. GRADING FORMULA & EXAMPLE PANEL -->
            <div class="gs-panel mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="gs-panel-title mb-0">Grading Formula & Example</p>
                </div>
                <div class="gs-rules-section mb-0 pb-0 border-0">
                    <div class="gs-rules-row">
                        <span class="gs-rules-label">Formula</span>
                        <span class="gs-rules-value font-monospace">{{ $formula }}</span>
                    </div>
                </div>
                <div class="gr-formula-highlight">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-calculator text-primary small"></i>
                        <span class="fw-bold small text-dark">Worked Example</span>
                    </div>
                    <div class="gr-formula-step">
                        <span class="gr-formula-label">Written Work:</span>
                        <span class="gr-formula-value">{{ $workedExample['ww_score'] }}% × {{ $gradingComponents['written_work']['weight'] / 100 }} = {{ $workedExample['ww_contribution'] }}</span>
                    </div>
                    <div class="gr-formula-step">
                        <span class="gr-formula-label">Performance Task:</span>
                        <span class="gr-formula-value">{{ $workedExample['pt_score'] }}% × {{ $gradingComponents['performance_task']['weight'] / 100 }} = {{ $workedExample['pt_contribution'] }}</span>
                    </div>
                    <div class="gr-formula-step">
                        <span class="gr-formula-label">Examination:</span>
                        <span class="gr-formula-value">{{ $workedExample['ta_score'] }}% × {{ $gradingComponents['term_assessment']['weight'] / 100 }} = {{ $workedExample['ta_contribution'] }}</span>
                    </div>
                    <div class="gr-formula-step gr-formula-total">
                        <span class="gr-formula-label">Final Grade:</span>
                        <span class="gr-formula-value">{{ $workedExample['ww_contribution'] }} + {{ $workedExample['pt_contribution'] }} + {{ $workedExample['ta_contribution'] }} = {{ $workedExample['final_grade'] }}</span>
                    </div>
                </div>
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
</x-layouts.teacher>
