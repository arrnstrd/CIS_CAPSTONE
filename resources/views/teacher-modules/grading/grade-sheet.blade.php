<x-layouts.teacher>
    <x-slot name="pageName">Grading System</x-slot>
    <x-slot name="subtitle">{{ $ta->section->name }} · {{ $ta->subject->name }}</x-slot>

    <div class="mb-3">
        <a href="{{ route('teacher.grading-system.dashboard') }}" class="text-decoration-none">&larr; Back</a>
        <span class="gs-panel-title ms-2">Grade Sheet</span>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-neutral">
                    <i class="fa-solid fa-users"></i>
                </span>
                <div>
                    <p class="gs-stat-label">Students</p>
                    <p class="gs-stat-value">{{ $totalStudents }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-neutral">
                    <i class="fa-solid fa-chart-simple"></i>
                </span>
                <div>
                    <p class="gs-stat-label">Grade Completion</p>
                    <p class="gs-stat-value" id="statGradeCompletion">{{ $gradeCompletionPercent !== null ? $gradeCompletionPercent . '%' : '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-neutral">
                    <i class="fa-solid fa-star-half-stroke"></i>
                </span>
                <div>
                    <p class="gs-stat-label">Class Average</p>
                    <p class="gs-stat-value" id="statClassAverage">{{ $classAverage !== null ? $classAverage : '—' }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="gs-stat-card d-flex align-items-center gap-3">
                <span class="gs-stat-icon gs-stat-icon-neutral">
                    <i class="fa-solid fa-check-double"></i>
                </span>
                <div>
                    <p class="gs-stat-label">Passing Rate</p>
                    <p class="gs-stat-value" id="statPassingRate">{{ $passingRate !== null ? $passingRate . '%' : '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="gd-layout">
        <div class="gd-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <p class="gs-panel-title mb-0">Grade {{ $ta->section->grade_level }} - {{ $ta->section->name }}</p>
                    <p class="text-muted small mb-0">{{ $ta->subject->name }} <span class="gs-badge gs-badge-info">{{ $categoryLabels['written'] }}</span></p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="showAssessmentLog()">
                        <i class="fa-solid fa-list me-1"></i> Assessment Log
                    </button>
                </div>
            </div>

            <div class="gs-tab-bar mb-3">
                @foreach ($gradingPeriods as $gp)
                    <a href="{{ route('teacher.grading-system.grade-sheet', ['teachingAssignmentId' => $ta->id, 'grading_period_id' => $gp->id]) }}"
                       class="gs-tab {{ $selectedPeriodId == $gp->id ? 'gs-tab-active' : '' }}">Term {{ $gp->sequence }}</a>
                @endforeach
            </div>

            <div class="table-panel">
                <table class="table table-bordered table-sm align-middle mb-0" id="gradeSheetTable">
                    <thead>
                        <tr>
                            <th rowspan="3" class="align-middle">Learner Name</th>
                            
                            <!-- Written Works Column -->
                            <th colspan="{{ $fixedSlots['written'] }}" class="text-center gs-group-written">
                                {{ $categoryLabels['written'] }} <button type="button" class="gs-add-col-btn gs-add-col" data-category="written">
                                <i class="fa-solid fa-plus"></i> Add
                            </button>
                            </th>
                            
                            <!-- Performance Tasks Column -->
                            <th colspan="{{ $fixedSlots['performance'] }}" class="text-center gs-group-performance">
                                {{ $categoryLabels['performance'] }} <button type="button" class="gs-add-col-btn gs-add-col" data-category="performance">
                                <i class="fa-solid fa-plus"></i> Add
                            </button>
                            </th>
                            
                            <!-- Examinations/Quarterly Assessments Column -->
                            <th colspan="{{ $schoolLevel === 'shs' ? $fixedSlots['quarterly'] : $fixedSlots['exam'] }}" class="text-center gs-group-{{ $schoolLevel === 'shs' ? 'quarterly' : 'exam' }}">
                                {{ $schoolLevel === 'shs' ? $categoryLabels['quarterly'] : $categoryLabels['exam'] }} <button type="button" class="gs-add-col-btn gs-add-col" data-category="{{ $schoolLevel === 'shs' ? 'quarterly' : 'exam' }}">
                                <i class="fa-solid fa-plus"></i> Add
                            </button>
                            </th>
                            
                            <th rowspan="3" class="align-middle text-center">Initial Grade</th>
                            <th rowspan="3" class="align-middle text-center">Transmuted</th>
                        </tr>
                        
                        <!-- Assessment Names Row -->
                        <tr>
                            <!-- Written Works -->
                            @foreach ($slotLabels['written'] as $label)
                                <th class="text-center small gs-group-written">{{ $label }}</th>
                            @endforeach
                            
                            <!-- Performance Tasks -->
                            @foreach ($slotLabels['performance'] as $label)
                                <th class="text-center small gs-group-performance">{{ $label }}</th>
                            @endforeach
                            
                            <!-- Examinations/Quarterly Assessments -->
                            @foreach ($slotLabels[$schoolLevel === 'shs' ? 'quarterly' : 'exam'] as $label)
                                <th class="text-center small gs-group-{{ $schoolLevel === 'shs' ? 'quarterly' : 'exam' }}">{{ $label }}</th>
                            @endforeach
                        </tr>
                        
                        <!-- HPS Row -->
                        <tr class="gs-hps-row">
                            <!-- Written Works HPS -->
                            @foreach ($assessmentsBySlot['written'] as $assessment)
                                <th class="text-center small gs-group-written">
                                    @if ($assessment)
                                        HPS: {{ $assessment->total_items }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </th>
                            @endforeach
                            
                            <!-- Performance Tasks HPS -->
                            @foreach ($assessmentsBySlot['performance'] as $assessment)
                                <th class="text-center small gs-group-performance">
                                    @if ($assessment)
                                        HPS: {{ $assessment->total_items }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </th>
                            @endforeach
                            
                            <!-- Examinations/Quarterly Assessments HPS -->
                            @foreach ($assessmentsBySlot[$schoolLevel === 'shs' ? 'quarterly' : 'exam'] as $assessment)
                                <th class="text-center small gs-group-{{ $schoolLevel === 'shs' ? 'quarterly' : 'exam' }}">
                                    @if ($assessment)
                                        HPS: {{ $assessment->total_items }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr data-enrollment-id="{{ $row->enrollment_id }}">
                                <td>{{ $row->student_name }}</td>
                                
                                <!-- Written Works Scores -->
                                @foreach ($assessmentsBySlot['written'] as $assessment)
                                    <td class="text-center p-1">
                                        @if ($assessment)
                                            <input type="number" min="0" max="{{ $assessment->total_items }}" step="0.01"
                                                class="form-control form-control-sm gs-score-input text-center"
                                                style="width: 70px; display: inline-block;"
                                                data-assessment-id="{{ $assessment->id }}"
                                                data-enrollment-id="{{ $row->enrollment_id }}"
                                                data-category="written"
                                                value="{{ $row->scores[$assessment->id]->score ?? '' }}">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                
                                <!-- Performance Tasks Scores -->
                                @foreach ($assessmentsBySlot['performance'] as $assessment)
                                    <td class="text-center p-1">
                                        @if ($assessment)
                                            <input type="number" min="0" max="{{ $assessment->total_items }}" step="0.01"
                                                class="form-control form-control-sm gs-score-input text-center"
                                                style="width: 70px; display: inline-block;"
                                                data-assessment-id="{{ $assessment->id }}"
                                                data-enrollment-id="{{ $row->enrollment_id }}"
                                                data-category="performance"
                                                value="{{ $row->scores[$assessment->id]->score ?? '' }}">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                
                                <!-- Examinations/Quarterly Assessments Scores -->
                                @foreach ($assessmentsBySlot[$schoolLevel === 'shs' ? 'quarterly' : 'exam'] as $assessment)
                                    <td class="text-center p-1">
                                        @if ($assessment)
                                            <input type="number" min="0" max="{{ $assessment->total_items }}" step="0.01"
                                                class="form-control form-control-sm gs-score-input text-center"
                                                style="width: 70px; display: inline-block;"
                                                data-assessment-id="{{ $assessment->id }}"
                                                data-enrollment-id="{{ $row->enrollment_id }}"
                                                data-category="{{ $schoolLevel === 'shs' ? 'quarterly' : 'exam' }}"
                                                value="{{ $row->scores[$assessment->id]->score ?? '' }}">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                
                                <td class="text-center gs-initial-cell">{{ $row->initial_grade ?? '—' }}</td>
                                <td class="text-center gs-transmuted-cell fw-bold">{{ $row->transmuted_grade ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $fixedSlots['written'] + $fixedSlots['performance'] + ($schoolLevel === 'shs' ? $fixedSlots['quarterly'] : $fixedSlots['exam']) + 2 }}" class="text-center text-muted py-4">No active learners found for this section.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Assessment Log Modal --}}
    <div class="modal fade" id="assessmentLogModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assessment Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="gd-form-label">Category</label>
                        <select class="form-select" id="logCategoryFilter">
                            <option value="all">All Categories</option>
                            <option value="written">Written Works</option>
                            <option value="performance">Performance Tasks</option>
                            <option value="exam">Examinations</option>
                            <option value="quarterly">Quarterly Assessments</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Assessment</th>
                                    <th>Category</th>
                                    <th>HPS</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Slot</th>
                                </tr>
                            </thead>
                            <tbody id="assessmentLogBody">
                                <!-- Assessment log entries will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Column modal --}}
    <div class="modal fade" id="addColumnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Assessment Column</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="addColumnCategory">
                    <div class="mb-3">
                        <label class="gd-form-label">Title</label>
                        <input type="text" class="form-control" id="addColumnTitle" placeholder="e.g. WW1">
                    </div>
                    <div class="mb-3">
                        <label class="gd-form-label">Total Items (Highest Possible Score)</label>
                        <input type="number" min="1" class="form-control" id="addColumnTotal" value="10">
                    </div>
                    <div class="mb-3">
                        <label class="gd-form-label">Date</label>
                        <input type="date" class="form-control" id="addColumnDate">
                    </div>
                    <div class="mb-3">
                        <label class="gd-form-label">Description <span class="text-muted small">(optional)</span></label>
                        <textarea class="form-control" id="addColumnDescription" rows="2"></textarea>
                    </div>
                    <div class="text-danger small" id="addColumnError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn gd-btn-primary" id="addColumnSubmit">
                        <span id="addColumnSpinner" class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                        <span id="addColumnBtnText">Add Column</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const teachingAssignmentId = {{ $ta->id }};
        const gradingPeriodId = {{ $selectedPeriodId }};
        const schoolLevel = '{{ $schoolLevel }}';

        // Assessment Log functionality
        function showAssessmentLog() {
            fetch(`/teacher/grading-system/assessments/log?teaching_assignment_id=${teachingAssignmentId}&grading_period_id=${gradingPeriodId}`)
                .then(response => response.json())
                .then(data => {
                    renderAssessmentLog(data);
                    const modal = new bootstrap.Modal(document.getElementById('assessmentLogModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error loading assessment log:', error);
                    alert('Error loading assessment log');
                });
        }

        function renderAssessmentLog(assessments) {
            const tbody = document.getElementById('assessmentLogBody');
            tbody.innerHTML = '';

            if (assessments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No assessments found</td></tr>';
                return;
            }

            assessments.forEach(assessment => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${assessment.title}</td>
                    <td>${getCategoryLabel(assessment.category)}</td>
                    <td>${assessment.total_items}</td>
                    <td>${assessment.assessment_date || '—'}</td>
                    <td>${assessment.description || '—'}</td>
                    <td>${assessment.slot_number || '—'}</td>
                `;
                tbody.appendChild(row);
            });
        }

        function getCategoryLabel(category) {
            const labels = {
                'written': 'Written Works',
                'performance': 'Performance Tasks',
                'exam': 'Examinations',
                'quarterly': 'Quarterly Assessments'
            };
            return labels[category] || category;
        }

        document.querySelectorAll('.gs-add-col').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('addColumnCategory').value = btn.dataset.category;
                document.getElementById('addColumnTitle').value = '';
                document.getElementById('addColumnTotal').value = 10;
                document.getElementById('addColumnDate').value = new Date().toISOString().split('T')[0];
                document.getElementById('addColumnDescription').value = '';
                document.getElementById('addColumnError').textContent = '';
                const modalEl = document.getElementById('addColumnModal');
                new bootstrap.Modal(modalEl).show();
                modalEl.addEventListener('shown.bs.modal', () => {
                    document.getElementById('addColumnTitle').focus();
                }, { once: true });
            });
        });

        const addColumnSubmitBtn = document.getElementById('addColumnSubmit');
        const addColumnSpinner = document.getElementById('addColumnSpinner');
        const addColumnBtnText = document.getElementById('addColumnBtnText');

        function setAddColumnLoading(isLoading) {
            addColumnSubmitBtn.disabled = isLoading;
            addColumnSpinner.classList.toggle('d-none', !isLoading);
            addColumnBtnText.textContent = isLoading ? 'Adding...' : 'Add Column';
        }

        addColumnSubmitBtn.addEventListener('click', async () => {
            const category = document.getElementById('addColumnCategory').value;
            const title = document.getElementById('addColumnTitle').value.trim();
            const total = document.getElementById('addColumnTotal').value;
            const date = document.getElementById('addColumnDate').value;
            const description = document.getElementById('addColumnDescription').value.trim();
            const errorEl = document.getElementById('addColumnError');
            errorEl.textContent = '';

            if (!title || !total || total < 1) {
                errorEl.textContent = 'Please fill in a title and a valid total items value.';
                return;
            }

            setAddColumnLoading(true);

            try {
                const res = await fetch('{{ route("teacher.grading-system.grade-sheet.assessment") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        teaching_assignment_id: teachingAssignmentId,
                        grading_period_id: gradingPeriodId,
                        category, title, total_items: total,
                        assessment_date: date || null,
                        description: description || null,
                    }),
                });
                if (!res.ok) throw new Error('Failed to add column');
                location.reload();
            } catch (e) {
                errorEl.textContent = 'Something went wrong. Please try again.';
                setAddColumnLoading(false);
            }
        });

        document.querySelectorAll('.gs-score-input').forEach(input => {
            input.addEventListener('blur', async () => {
                const assessmentId = input.dataset.assessmentId;
                const enrollmentId = input.dataset.enrollmentId;
                const score = input.value;
                if (score === '') return;

                // Frontend HPS validation
                const maxScore = parseFloat(input.max);
                const scoreValue = parseFloat(score);
                
                if (scoreValue < 0 || scoreValue > maxScore) {
                    input.style.backgroundColor = '#FCEBEB';
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'invalid-feedback d-block';
                    errorMsg.textContent = 'Invalid Score: Score must be between 0 and ' + maxScore;
                    input.parentNode.appendChild(errorMsg);
                    
                    setTimeout(() => {
                        errorMsg.remove();
                        input.style.backgroundColor = '';
                    }, 3000);
                    return;
                }

                const row = document.querySelector(`tr[data-enrollment-id="${enrollmentId}"]`);
                const originalBg = input.style.backgroundColor;
                input.style.backgroundColor = '#FAEEDA';

                try {
                    const res = await fetch('{{ route("teacher.grading-system.grade-sheet.score") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify({ assessment_id: assessmentId, enrollment_id: enrollmentId, score }),
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        input.style.backgroundColor = '#FCEBEB';
                        alert(data.error || 'Score could not be saved.');
                        return;
                    }

                    row.querySelector('.gs-initial-cell').textContent = data.initial_grade ?? '—';
                    row.querySelector('.gs-transmuted-cell').textContent = data.transmuted_grade ?? '—';
                    
                    // Update class-level statistics
                    document.getElementById('statGradeCompletion').textContent = data.grade_completion_percent !== null && data.grade_completion_percent !== undefined ? data.grade_completion_percent + '%' : '—';
                    document.getElementById('statClassAverage').textContent = data.class_average !== null && data.class_average !== undefined ? data.class_average : '—';
                    document.getElementById('statPassingRate').textContent = data.passing_rate !== null && data.passing_rate !== undefined ? data.passing_rate + '%' : '—';
                    
                    input.style.backgroundColor = '#e1f5ee';
                    setTimeout(() => { input.style.backgroundColor = originalBg; }, 800);

                    // Recompute PS for this row/category from visible inputs (client-side, matches server % logic)
                    const category = input.dataset.category;
                    const categoryInputs = row.querySelectorAll(`.gs-score-input[data-category="${category}"]`);
                    let sumScore = 0, sumTotal = 0;
                    categoryInputs.forEach(inp => {
                        if (inp.value !== '') {
                            sumScore += parseFloat(inp.value);
                            sumTotal += parseFloat(inp.max);
                        }
                    });
                    const psCell = row.querySelector(`.gs-ps-cell[data-cat="${category}"]`);
                    if (psCell) {
                        psCell.textContent = sumTotal > 0 ? ((sumScore / sumTotal) * 100).toFixed(2) : '—';
                    };
                } catch (e) {
                    input.style.backgroundColor = '#FCEBEB';
                }
            });
        });
    </script>
</x-layouts.teacher>