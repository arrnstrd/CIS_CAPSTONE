<x-layouts.teacher>
    <x-slot name="pageName">Grading System</x-slot>
    <x-slot name="subtitle">{{ $policyName }} · {{ $schoolYear }}</x-slot>

    <div class="mb-3">
        <a href="{{ route('teacher.grading-system.dashboard') }}" class="text-decoration-none">&larr; Back</a>
        <span class="gs-panel-title ms-2">Grading Rules</span>
    </div>

    <div class="gd-layout">
        <div class="gd-content">
            <!-- Source of Grading Rules Box -->
            <div class="gs-panel mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="gs-panel-title mb-0">Source of Grading Rules</p>
                </div>
                <div class="gs-rules-section">
                    <div class="gs-rules-row">
                        <span class="gs-rules-label">Policy Name</span>
                        <span class="gs-rules-value">{{ $policyName }}</span>
                    </div>
                </div>
                <div class="gs-rules-section">
                    <div class="gs-rules-row">
                        <span class="gs-rules-label">School Year</span>
                        <span class="gs-rules-value">{{ $schoolYear }}</span>
                    </div>
                </div>
                <div class="gs-rules-section">
                    <div class="gs-rules-row">
                        <span class="gs-rules-label">Last Updated</span>
                        <span class="gs-rules-value">{{ $lastUpdated }}</span>
                    </div>
                </div>
                <div class="gs-rules-section gs-rules-section-last">
                    <div class="gs-rules-row">
                        <span class="gs-rules-label">Configured By</span>
                        <span class="gs-rules-value">{{ $configuredBy }}</span>
                    </div>
                </div>
            </div>

            <!-- Grading Components Box -->
            <div class="gs-panel mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="gs-panel-title mb-0">Grading Components</p>
                </div>
                @foreach ($gradingComponents as $key => $component)
                    <div class="gs-rules-section">
                        <div class="gs-rules-row">
                            <span class="gs-rules-label">{{ $component['name'] }}</span>
                            <div class="d-flex align-items-center gap-3">
                                <span class="gs-rules-value">{{ $component['weight'] }}%</span>
                                <div class="gd-progress-bar">
                                    <div class="gs-weight-bar" style="width: {{ $component['weight'] }}%; background-color: {{ $component['color'] }};"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Grading Formula & Example Box -->
            <div class="gs-panel mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="gs-panel-title mb-0">Grading Formula & Example</p>
                </div>
                <div class="gs-rules-section">
                    <div class="gs-rules-row">
                        <span class="gs-rules-label">Formula</span>
                        <span class="gs-rules-value">{{ $formula }}</span>
                    </div>
                </div>
                <div class="gs-rules-section">
                    <div class="gs-rules-row">
                        <span class="gs-rules-label">Worked Example</span>
                        <div class="gs-formula-example">
                                <div class="gs-formula-step">
                                    <span class="gs-formula-label">Written Work:</span>
                                    <span class="gs-formula-value">{{ $workedExample['ww_score'] }}% × {{ $gradingComponents['written_work']['weight'] / 100 }} = {{ $workedExample['ww_contribution'] }}</span>
                                </div>
                                <div class="gs-formula-step">
                                    <span class="gs-formula-label">Performance Task:</span>
                                    <span class="gs-formula-value">{{ $workedExample['pt_score'] }}% × {{ $gradingComponents['performance_task']['weight'] / 100 }} = {{ $workedExample['pt_contribution'] }}</span>
                                </div>
                                <div class="gs-formula-step">
                                    <span class="gs-formula-label">Examination:</span>
                                    <span class="gs-formula-value">{{ $workedExample['ta_score'] }}% × {{ $gradingComponents['quarterly_assessment']['weight'] / 100 }} = {{ $workedExample['ta_contribution'] }}</span>
                                </div>
                                <div class="gs-formula-step gs-formula-total">
                                    <span class="gs-formula-label">Final Grade:</span>
                                    <span class="gs-formula-value">{{ $workedExample['ww_contribution'] }} + {{ $workedExample['pt_contribution'] }} + {{ $workedExample['ta_contribution'] }} = {{ $workedExample['final_grade'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other Rules Table -->
            <div class="gs-panel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="gs-panel-title mb-0">Other Rules</p>
                </div>
                @foreach ($otherRules as $rule)
                    <div class="gs-rules-section">
                        <div class="gs-rules-row">
                            <span class="gs-rules-label">{{ $rule['rule'] }}</span>
                            <span class="gs-rules-value">{{ $rule['value'] }}</span>
                        </div>
                    </div>
                @endforeach
                <div class="gs-rules-section gs-rules-section-last"></div>
            </div>
        </div>
    </div>
</x-layouts.teacher>
