<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-scale-balanced"></i>
            Grading System
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">School-wide computation rules and academic criteria.</span>
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'comprules'])

    @if (session('success'))
        <div class="gs-note-banner mb-3" style="background-color: #e1f5ee; color: #085041;">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('teacher.grading-system.comp-rules.update') }}" id="compRulesForm">
        @csrf
        @method('PUT')

        <div class="gs-panel mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="gs-panel-title mb-0">Computation Rules & Academic Criteria</p>
                <div>
                    <button type="button" id="editBtn" class="btn-view-history">Edit</button>
                    <button type="button" id="cancelBtn" class="btn btn-outline-secondary btn-sm" style="display:none;">Cancel</button>
                    <button type="submit" id="saveBtn" class="btn btn-success btn-sm" style="display:none;">Save Changes</button>
                </div>
            </div>

            <div class="gs-rules-section">
                <p class="gs-rules-heading">Passing Grade</p>
                <div class="gs-rules-row">
                    <span class="gs-rules-label">Passing Grade (Minimum)</span>
                    <span class="gs-rules-value" data-display>{{ $settings['passing_grade'] }}</span>
                    <input type="number" step="0.01" name="passing_grade" value="{{ $settings['passing_grade'] }}" class="form-control form-control-sm gs-rules-input" style="display:none;">
                </div>
            </div>

            <div class="gs-rules-section">
                <p class="gs-rules-heading">General Weighted Average (GWA)</p>
                <div class="gs-rules-row">
                    <span class="gs-rules-label">GWA Formula / Description</span>
                    <span class="gs-rules-value" data-display>{{ $settings['gwa_formula'] }}</span>
                    <input type="text" name="gwa_formula" value="{{ $settings['gwa_formula'] }}" class="form-control form-control-sm gs-rules-input" style="display:none;">
                </div>
            </div>

            <div class="gs-rules-section">
                <p class="gs-rules-heading">Term Weights</p>
                <div class="gs-rules-row">
                    <span class="gs-rules-label">Term 1 Weight (%)</span>
                    <span class="gs-rules-value" data-display>{{ $settings['term_1_weight'] }}</span>
                    <input type="number" step="0.01" name="term_1_weight" value="{{ $settings['term_1_weight'] }}" class="form-control form-control-sm gs-rules-input" style="display:none;">
                </div>
                <div class="gs-rules-row">
                    <span class="gs-rules-label">Term 2 Weight (%)</span>
                    <span class="gs-rules-value" data-display>{{ $settings['term_2_weight'] }}</span>
                    <input type="number" step="0.01" name="term_2_weight" value="{{ $settings['term_2_weight'] }}" class="form-control form-control-sm gs-rules-input" style="display:none;">
                </div>
                <div class="gs-rules-row">
                    <span class="gs-rules-label">Term 3 Weight (%)</span>
                    <span class="gs-rules-value" data-display>{{ $settings['term_3_weight'] }}</span>
                    <input type="number" step="0.01" name="term_3_weight" value="{{ $settings['term_3_weight'] }}" class="form-control form-control-sm gs-rules-input" style="display:none;">
                </div>
            </div>

            <div class="gs-rules-section">
                <p class="gs-rules-heading">Academic Honors Criteria</p>
                <div class="gs-rules-row">
                    <span class="gs-rules-label">With Highest Honors</span>
                    <span class="gs-rules-value" data-display>{{ $settings['honors_highest'] }}</span>
                    <input type="text" name="honors_highest" value="{{ $settings['honors_highest'] }}" class="form-control form-control-sm gs-rules-input" style="display:none;">
                </div>
                <div class="gs-rules-row">
                    <span class="gs-rules-label">With High Honors</span>
                    <span class="gs-rules-value" data-display>{{ $settings['honors_high'] }}</span>
                    <input type="text" name="honors_high" value="{{ $settings['honors_high'] }}" class="form-control form-control-sm gs-rules-input" style="display:none;">
                </div>
                <div class="gs-rules-row">
                    <span class="gs-rules-label">With Honors</span>
                    <span class="gs-rules-value" data-display>{{ $settings['honors_with'] }}</span>
                    <input type="text" name="honors_with" value="{{ $settings['honors_with'] }}" class="form-control form-control-sm gs-rules-input" style="display:none;">
                </div>
            </div>

            <div class="gs-rules-section gs-rules-section-last">
                <p class="gs-rules-heading">Grade Computation Rules</p>
                <div class="gs-rules-row">
                    <span class="gs-rules-label">Grading Scale</span>
                    <span class="gs-rules-value" data-display>{{ $settings['grading_scale'] }}</span>
                    <input type="text" name="grading_scale" value="{{ $settings['grading_scale'] }}" class="form-control form-control-sm gs-rules-input" style="display:none;">
                </div>
                <div class="gs-rules-row">
                    <span class="gs-rules-label">Incomplete Grade Policy</span>
                    <span class="gs-rules-value" data-display>{{ $settings['incomplete_policy'] }}</span>
                    <input type="text" name="incomplete_policy" value="{{ $settings['incomplete_policy'] }}" class="form-control form-control-sm gs-rules-input" style="display:none;">
                </div>
            </div>
        </div>
    </form>

    <script>
        const editBtn = document.getElementById('editBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const saveBtn = document.getElementById('saveBtn');

        function toggleEditMode(editing) {
            document.querySelectorAll('[data-display]').forEach(el => el.style.display = editing ? 'none' : 'inline');
            document.querySelectorAll('.gs-rules-input').forEach(el => el.style.display = editing ? 'block' : 'none');
            editBtn.style.display = editing ? 'none' : 'inline-block';
            cancelBtn.style.display = editing ? 'inline-block' : 'none';
            saveBtn.style.display = editing ? 'inline-block' : 'none';
        }

        editBtn.addEventListener('click', () => toggleEditMode(true));
        cancelBtn.addEventListener('click', () => {
            document.getElementById('compRulesForm').reset();
            toggleEditMode(false);
        });
    </script>

</x-layouts.teacher>