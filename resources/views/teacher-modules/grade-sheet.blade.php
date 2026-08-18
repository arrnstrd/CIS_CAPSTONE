<x-layouts.teacher>
    <x-slot name="pageName">Grading System</x-slot>
    <x-slot name="subtitle">{{ $ta->section->name }} · {{ $ta->subject->name }}</x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'grading'])

    <div class="gd-layout">
        @include('teacher-modules.partials.grading-dashboard-sidebar', ['gdActive' => 'grade-sheet'])

        <div class="gd-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <p class="gs-panel-title mb-0">Grade {{ $ta->section->grade_level }} - {{ $ta->section->name }}</p>
                    <p class="text-muted small mb-0">{{ $ta->subject->name }}</p>
                </div>
                <a href="{{ route('teacher.grading-system.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
                </a>
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
                            <th rowspan="2" class="align-middle">Learner Name</th>
                            <th colspan="{{ $assessmentsByCategory['written']->count() + 1 }}" class="text-center">
                                Written Works <button type="button" class="btn btn-sm btn-link p-0 ms-1 gs-add-col" data-category="written">+ Add</button>
                            </th>
                            <th colspan="{{ $assessmentsByCategory['performance']->count() + 1 }}" class="text-center">
                                Performance Tasks <button type="button" class="btn btn-sm btn-link p-0 ms-1 gs-add-col" data-category="performance">+ Add</button>
                            </th>
                            <th colspan="{{ $assessmentsByCategory['quarterly']->count() + 1 }}" class="text-center">
                                Quarterly Assessment <button type="button" class="btn btn-sm btn-link p-0 ms-1 gs-add-col" data-category="quarterly">+ Add</button>
                            </th>
                            <th rowspan="2" class="align-middle text-center">Initial Grade</th>
                            <th rowspan="2" class="align-middle text-center">Transmuted</th>
                        </tr>
                        <tr>
                            @foreach (['written', 'performance', 'quarterly'] as $cat)
                                @foreach ($assessmentsByCategory[$cat] as $a)
                                    <th class="text-center small">{{ $a->title }}<br><span class="text-muted">/{{ $a->total_items }}</span></th>
                                @endforeach
                                <th class="text-center small">PS</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr data-enrollment-id="{{ $row->enrollment_id }}">
                                <td>{{ $row->student_name }}</td>
                                @foreach (['written', 'performance', 'quarterly'] as $cat)
                                    @foreach ($assessmentsByCategory[$cat] as $a)
                                        <td class="text-center p-1">
                                            <input type="number" min="0" max="{{ $a->total_items }}" step="0.01"
                                                class="form-control form-control-sm gs-score-input text-center"
                                                style="width: 70px; display: inline-block;"
                                                data-assessment-id="{{ $a->id }}"
                                                data-enrollment-id="{{ $row->enrollment_id }}"
                                                value="{{ $row->scores[$a->id]->score ?? '' }}">
                                        </td>
                                    @endforeach
                                    <td class="text-center gs-ps-cell" data-cat="{{ $cat }}">{{ $row->category_totals[$cat]['ps'] ?? '—' }}</td>
                                @endforeach
                                <td class="text-center gs-initial-cell">{{ $row->initial_grade ?? '—' }}</td>
                                <td class="text-center gs-transmuted-cell fw-bold">{{ $row->transmuted_grade ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No active learners found for this section.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
                    <div class="text-danger small" id="addColumnError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn gd-btn-primary" id="addColumnSubmit">Add Column</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const teachingAssignmentId = {{ $ta->id }};
        const gradingPeriodId = {{ $selectedPeriodId }};

        document.querySelectorAll('.gs-add-col').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('addColumnCategory').value = btn.dataset.category;
                document.getElementById('addColumnError').textContent = '';
                new bootstrap.Modal(document.getElementById('addColumnModal')).show();
            });
        });

        document.getElementById('addColumnSubmit').addEventListener('click', async () => {
            const category = document.getElementById('addColumnCategory').value;
            const title = document.getElementById('addColumnTitle').value.trim();
            const total = document.getElementById('addColumnTotal').value;
            const errorEl = document.getElementById('addColumnError');

            if (!title || !total || total < 1) {
                errorEl.textContent = 'Please fill in a title and a valid total items value.';
                return;
            }

            try {
                const res = await fetch('{{ route("teacher.grading-system.grade-sheet.assessment") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        teaching_assignment_id: teachingAssignmentId,
                        grading_period_id: gradingPeriodId,
                        category, title, total_items: total,
                    }),
                });
                if (!res.ok) throw new Error('Failed to add column');
                location.reload();
            } catch (e) {
                errorEl.textContent = 'Something went wrong. Please try again.';
            }
        });

        document.querySelectorAll('.gs-score-input').forEach(input => {
            input.addEventListener('blur', async () => {
                const assessmentId = input.dataset.assessmentId;
                const enrollmentId = input.dataset.enrollmentId;
                const score = input.value;
                if (score === '') return;

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
                    input.style.backgroundColor = '#e1f5ee';
                    setTimeout(() => { input.style.backgroundColor = originalBg; }, 800);

                    // Recompute PS for this row/category from visible inputs (client-side, matches server % logic)
                    const category = input.closest('td').previousElementSibling ? null : null;
                } catch (e) {
                    input.style.backgroundColor = '#FCEBEB';
                }
            });
        });
    </script>
</x-layouts.teacher>