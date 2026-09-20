<div class="col mb-3 mx-2">
    <div class="bg-white rounded p-4 border">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <h3 class="fw-semibold text-dark m-0 fs-5">Teaching Assignment Records</h3>
            <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#academicAssignmentModal"
                onclick="openAcademicCreateAssignmentModal()">
                <i class="fas fa-plus fa-sm"></i>
                <span>Add Assignment</span>
            </button>
        </div>
    </div>
</div>

<!-- Contextual Bulk Actions Bar (Pop Out) -->
<div class="px-2">
    <div id="assignmentBulkBar" class="academic-bulk-bar d-none d-flex flex-row justify-content-between align-items-center flex-nowrap w-100">
        <div class="d-flex align-items-center gap-3 flex-nowrap">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fs-6">
                <i class="fas fa-check-double me-1"></i> <span id="assignmentSelectedCount">0</span>
            </span>
            <span class="fw-semibold text-dark fs-6 text-nowrap">Assignment(s) Selected</span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-nowrap">
            <button type="button" id="assignmentBulkDeleteBtn" class="btn btn-danger fw-semibold d-none text-nowrap">
                <i class="fas fa-trash me-2"></i> Delete Selected
            </button>
            <button type="button" class="btn btn-outline-secondary ms-2 text-nowrap" onclick="window.clearAssignmentSelection && window.clearAssignmentSelection()">
                <i class="fas fa-times me-1"></i> Deselect
            </button>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="table-panel border bg-white shadow-sm">


        <table class="table table-hover align-middle table-striped mb-0">
            <thead class="text-uppercase">
                <tr>
                    <th width="4%" class="text-center">
                        <input type="checkbox" id="selectAllAssignments" class="form-check-input academic-check-input select-all-checkbox">
                    </th>
                    <th width="18%" data-column="teacher" data-column-title="Teacher" data-column-type="text">Teacher</th>
                    <th width="18%" data-column="subject" data-column-title="Subject" data-column-type="text">Subject</th>
                    <th width="12%" data-column="grade" data-column-title="Grade" data-column-type="grade">Grade</th>
                    <th width="15%" data-column="section" data-column-title="Section" data-column-type="text">Section</th>
                    <th width="13%" data-column="schoolYear" data-column-title="School Year" data-column-type="categorical">School Year</th>
                    <th width="10%" data-column="status" data-column-title="Status" data-column-type="status">Status</th>
                    <th width="10%" class="text-center">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($teachingAssignments as $assignment)
                    @php
                        $assignStatus = strtolower($assignment->status ?? 'active');
                        $statusText = ucfirst($assignStatus);
                        $gradeText = $assignment->section?->grade_level ? 'Grade ' . $assignment->section->grade_level : '—';
                        $teacherName = $assignment->teacher?->full_name ?? '—';
                        $subjectName = $assignment->subject?->name ?? '—';
                        $sectionName = $assignment->section?->name ?? '—';
                        $syText = $assignment->schoolYear?->school_year ?? '—';
                    @endphp
                    <tr data-assignment-id="{{ $assignment->id }}"
                        data-col-teacher="{{ $teacherName }}"
                        data-col-subject="{{ $subjectName }}"
                        data-col-grade="{{ $gradeText }}"
                        data-col-section="{{ $sectionName }}"
                        data-col-school-year="{{ $syText }}"
                        data-col-status="{{ $statusText }}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input academic-check-input assignment-select-checkbox row-checkbox"
                                value="{{ $assignment->id }}">
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $teacherName }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $subjectName }}</div>
                            @if($assignment->subject?->code)
                                <div class="text-muted small" style="font-size: 0.72rem;">Code: {{ $assignment->subject->code }}</div>
                            @endif
                        </td>
                        <td>
                            @if($assignment->section?->grade_level)
                                <span class="badge bg-light text-secondary border">
                                    {{ $gradeText }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $sectionName }}</span>
                        </td>
                        <td>
                            <span class="text-secondary small fw-medium">
                                {{ $syText }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-dot dot-{{ $assignStatus === 'active' ? 'success' : 'secondary' }}">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td class="text-center text-nowrap">
                            <div class="action-btn-group justify-content-center">
                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal"
                                    data-bs-target="#academicAssignmentModal"
                                    onclick="openAcademicEditAssignmentModal({{ $assignment->id }}, {{ $assignment->teacher_id }}, {{ $assignment->subject_id }}, {{ $assignment->section_id }}, {{ $assignment->school_year_id }}, '{{ $assignment->status }}')">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>
                                <form action="{{ route('teaching-assignments.destroy', $assignment->id) }}"
                                    method="POST" data-ajax-delete="assignment" class="d-inline mb-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete this teaching assignment?');">
                                        <i class="fa-solid fa-trash"></i>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 opacity-50 d-block"></i>
                            No teaching assignments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="p-3 border-top">
            {{ $teachingAssignments->links() }}
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function initAssignmentBulk() {
                const pane = document.getElementById('assignment-table-pane');
                if (!pane) return;
                
                const selectAll = pane.querySelector('#selectAllAssignments');
                const checkboxes = pane.querySelectorAll('.assignment-select-checkbox');
                const bulkBar = document.getElementById('assignmentBulkBar');
                const countText = document.getElementById('assignmentSelectedCount');
                const deleteBtn = document.getElementById('assignmentBulkDeleteBtn');
                
                function updateUI() {
                    const visibleCheckboxes = Array.from(pane.querySelectorAll('tbody tr:not(.d-none) .assignment-select-checkbox'));
                    const checked = visibleCheckboxes.filter(cb => cb.checked);
                    const count = checked.length;
                    
                    if (countText) countText.textContent = count;
                    if (bulkBar) bulkBar.classList.toggle('d-none', count === 0);
                    
                    checkboxes.forEach(cb => {
                        const tr = cb.closest('tr');
                        if (tr) tr.classList.toggle('academic-selected-row', cb.checked);
                    });
                    
                    if (selectAll) {
                        selectAll.checked = visibleCheckboxes.length > 0 && count === visibleCheckboxes.length;
                    }
                    
                    const currentDeleteBtn = document.getElementById('assignmentBulkDeleteBtn');
                    if (currentDeleteBtn) {
                        currentDeleteBtn.classList.toggle('d-none', count === 0);
                    }
                }
                
                if (selectAll) {
                    selectAll.addEventListener('change', function () {
                        const visibleCheckboxes = Array.from(pane.querySelectorAll('tbody tr:not(.d-none) .assignment-select-checkbox'));
                        visibleCheckboxes.forEach(cb => {
                            if (!cb.disabled) cb.checked = selectAll.checked;
                        });
                        updateUI();
                    });
                }
                
                checkboxes.forEach(cb => {
                    cb.addEventListener('change', updateUI);
                });
                
                window.clearAssignmentSelection = function() {
                    checkboxes.forEach(cb => cb.checked = false);
                    if (selectAll) selectAll.checked = false;
                    updateUI();
                };
                
                if (deleteBtn) {
                    const newDeleteBtn = deleteBtn.cloneNode(true);
                    deleteBtn.parentNode.replaceChild(newDeleteBtn, deleteBtn);
                    
                    newDeleteBtn.addEventListener('click', function () {
                        const checked = Array.from(checkboxes).filter(cb => cb.checked);
                        if (checked.length === 0) return;
                        
                        const countEl = document.getElementById('bulkDeleteAssignmentCount');
                        if (countEl) countEl.textContent = checked.length;
                        
                        const errorDiv = document.getElementById('bulkDeleteAssignmentError');
                        if (errorDiv) errorDiv.classList.add('d-none');
                        
                        const modalEl = document.getElementById('bulkDeleteAssignmentModal');
                        if (modalEl) {
                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.show();
                        }
                    });
                }
                
                const confirmDeleteBtn = document.getElementById('confirmBulkDeleteAssignmentBtn');
                if (confirmDeleteBtn) {
                    const newConfirmBtn = confirmDeleteBtn.cloneNode(true);
                    confirmDeleteBtn.parentNode.replaceChild(newConfirmBtn, confirmDeleteBtn);
                    
                    newConfirmBtn.addEventListener('click', async function () {
                        const checked = Array.from(checkboxes).filter(cb => cb.checked);
                        if (checked.length === 0) return;
                        
                        const errorDiv = document.getElementById('bulkDeleteAssignmentError');
                        if (errorDiv) errorDiv.classList.add('d-none');
                        
                        const formData = new FormData();
                        formData.append("_token", document.querySelector('meta[name="csrf-token"]')?.content || "");
                        checked.forEach(cb => formData.append("ids[]", cb.value));
                        
                        const originalHtml = newConfirmBtn.innerHTML;
                        newConfirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';
                        newConfirmBtn.disabled = true;
                        
                        try {
                            const response = await fetch(@json(route('teaching-assignments.bulk-destroy')), {
                                method: "POST",
                                headers: {
                                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
                                    Accept: "application/json",
                                },
                                body: formData,
                            });
                            
                            const data = await response.json().catch(() => ({}));
                            if (!response.ok) {
                                if (errorDiv) {
                                    errorDiv.textContent = data.message || "Action failed. Please try again.";
                                    errorDiv.classList.remove('d-none');
                                }
                                newConfirmBtn.innerHTML = originalHtml;
                                newConfirmBtn.disabled = false;
                                return;
                            }
                            
                            const modalEl = document.getElementById('bulkDeleteAssignmentModal');
                            if (modalEl) {
                                const modal = bootstrap.Modal.getInstance(modalEl);
                                if (modal) modal.hide();
                            }
                            
                            window.clearAssignmentSelection();
                            if (window.ajaxCrud && typeof window.ajaxCrud.refreshTables === 'function') {
                                window.ajaxCrud.refreshTables();
                            } else {
                                window.location.reload();
                            }
                        } catch (err) {
                            if (errorDiv) {
                                errorDiv.textContent = "An error occurred while processing your request.";
                                errorDiv.classList.remove('d-none');
                            }
                            newConfirmBtn.innerHTML = originalHtml;
                            newConfirmBtn.disabled = false;
                        }
                    });
                }
            }
            
            initAssignmentBulk();
            document.addEventListener("ajax:table-refreshed", initAssignmentBulk);
            document.addEventListener("ajax:content-refreshed", initAssignmentBulk);
        });
    </script>
@endpush

{{-- Academic Setup Teaching Assignment Modal --}}
<x-modal id="academicAssignmentModal" modalTitle="Teaching Assignment" size="modal-lg">
    <form id="academicAssignmentForm" method="POST" data-ajax-form>
        @csrf
        <input type="hidden" name="_method" value="POST" id="academicAssignmentFormMethod">
        <div data-ajax-errors></div>

        <div class="row g-3">
            {{-- Teacher Selection --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">Teacher <span class="text-danger">*</span></label>
                <select name="teacher_id" id="acad_field_teacher_id" class="form-select" required>
                    <option value="">Select Teacher</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- School Year Selection --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                <select name="school_year_id" id="acad_field_school_year_id" class="form-select" required>
                    <option value="">Select School Year</option>
                    @foreach ($allSchoolYears as $sy)
                        <option value="{{ $sy->id }}" data-active="{{ $sy->is_active ? '1' : '0' }}">{{ $sy->school_year }}{{ $sy->is_active ? ' (Active)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Section Selection --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">Section & Grade Level <span class="text-danger">*</span></label>
                <select name="section_id" id="acad_field_section_id" class="form-select" required>
                    <option value="">Select Section</option>
                    @foreach ($allSections as $section)
                        <option value="{{ $section->id }}" data-grade-level="{{ $section->grade_level }}" data-level="{{ $section->level }}">{{ $section->name }} (Grade {{ $section->grade_level }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Selection --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="acad_field_status" class="form-select" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            {{-- Single Subject Selection (Grade 4+ and Edit mode) --}}
            <div class="col-12" id="acad_single_subject_container">
                <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                <select name="subject_id" id="acad_field_subject_id" class="form-select" required>
                    <option value="">Select Subject</option>
                    @foreach ($allSubjects as $subject)
                        <option value="{{ $subject->id }}" data-level="{{ $subject->level }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Mass Subject Field (Grade 1-3) --}}
            <div class="col-12 d-none" id="acad_mass_subject_container">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <label class="form-label fw-semibold mb-0">Grade 1–3 Subjects <span class="text-danger">*</span></label>
                        <div class="text-muted small" style="font-size: 0.75rem;">Batch assign core elementary subjects for this section.</div>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="acad_btnSelectAllSubjects" style="font-size: 0.75rem;">Select All</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="acad_btnDeselectAllSubjects" style="font-size: 0.75rem;">Deselect All</button>
                    </div>
                </div>
                <div class="border rounded-3 p-3 bg-light" id="acad_mass_subjects_list" style="max-height: 220px; overflow-y: auto;">
                    <div class="text-muted small text-center py-2" id="acad_mass_subjects_empty">
                        Select a section and school year to load grade-specific subjects.
                    </div>
                </div>
                <div class="form-text small text-muted mt-1" style="font-size: 0.75rem;">
                    <i class="fas fa-info-circle me-1"></i> Subjects already assigned to another teacher are disabled.
                </div>
            </div>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-dark" data-loading-text="Saving..." id="academicAssignmentSubmitBtn">
                <span id="academicAssignmentSubmitText">Save Assignment</span>
            </button>
        </div>
    </form>
</x-modal>

<x-modal id="bulkDeleteAssignmentModal" modalTitle="Confirm Mass Deletion" size="modal-md" centered animation="modal-anim-slide">
    <div class="p-2">
        <div class="alert alert-danger d-flex align-items-start gap-3 mb-3">
            <i class="fa-solid fa-triangle-exclamation fs-4 flex-shrink-0 mt-1 text-danger"></i>
            <div>
                <h6 class="fw-bold mb-1 text-danger">Destructive Action Warning</h6>
                <p class="mb-0 small text-secondary">
                    You are about to permanently delete <strong id="bulkDeleteAssignmentCount" class="text-danger">0</strong> selected teaching assignment(s). This action cannot be undone.
                </p>
            </div>
        </div>
        <div id="bulkDeleteAssignmentError" class="alert alert-danger d-none small py-2 px-3 mb-2"></div>
        <p class="text-muted small mb-0">Are you sure you want to proceed with deleting the selected assignments?</p>
    </div>
    <div class="modal-footer px-0 pb-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmBulkDeleteAssignmentBtn" class="btn btn-danger fw-semibold">
            <i class="fas fa-trash me-1"></i> Permanently Delete
        </button>
    </div>
</x-modal>

@push('scripts')
    <script>
        function acadEscapeHtml(text) {
            if (!text) return "";
            const div = document.createElement("div");
            div.textContent = text;
            return div.innerHTML;
        }

        function setAcadSingleSubjectMode() {
            const single = document.getElementById('acad_single_subject_container');
            const mass = document.getElementById('acad_mass_subject_container');
            const subj = document.getElementById('acad_field_subject_id');
            const list = document.getElementById('acad_mass_subjects_list');

            if (single) single.classList.remove('d-none');
            if (subj) {
                subj.disabled = false;
                subj.required = true;
            }
            if (mass) mass.classList.add('d-none');
            if (list) {
                list.innerHTML = '<div class="text-muted small text-center py-2">Select a section and school year to load grade-specific subjects.</div>';
            }
        }

        function setAcadMassSubjectMode() {
            const single = document.getElementById('acad_single_subject_container');
            const mass = document.getElementById('acad_mass_subject_container');
            const subj = document.getElementById('acad_field_subject_id');

            if (single) single.classList.add('d-none');
            if (subj) {
                subj.disabled = true;
                subj.required = false;
            }
            if (mass) mass.classList.remove('d-none');
        }

        function renderAcadMassSubjects(subjects) {
            const list = document.getElementById('acad_mass_subjects_list');
            if (!list) return;

            if (!subjects || subjects.length === 0) {
                list.innerHTML = '<div class="text-muted small text-center py-2">No subjects found for Grade 1–3 (elementary level).</div>';
                return;
            }

            list.innerHTML = "";
            const grid = document.createElement("div");
            grid.className = "row g-2";

            subjects.forEach((subject) => {
                const col = document.createElement("div");
                col.className = "col-md-6";

                let badgeHtml = "";
                let isChecked = false;
                let isDisabled = false;

                if (subject.is_assigned) {
                    isDisabled = true;
                    if (subject.is_current_teacher) {
                        isChecked = true;
                        badgeHtml = '<span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">Current Teacher</span>';
                    } else {
                        const teacherName = subject.assigned_teacher_name || "Another Teacher";
                        badgeHtml = `<span class="badge bg-warning text-dark border ms-1" style="font-size: 0.65rem;" title="Assigned to ${acadEscapeHtml(teacherName)}">Assigned: ${acadEscapeHtml(teacherName)}</span>`;
                    }
                } else {
                    isChecked = true;
                }

                col.innerHTML = `
                    <div class="form-check p-2 rounded border bg-white h-100 d-flex align-items-center">
                        <input class="form-check-input mass-subject-checkbox ms-1 me-2" type="checkbox" 
                            name="subject_ids[]" 
                            value="${subject.id}" 
                            id="acad_chk_subj_${subject.id}" 
                            ${isChecked ? "checked" : ""} 
                            ${isDisabled ? "disabled" : ""}>
                        <label class="form-check-label small flex-grow-1 user-select-none mb-0 text-truncate" for="acad_chk_subj_${subject.id}">
                            <span class="fw-semibold">${acadEscapeHtml(subject.name)}</span>
                            ${subject.code ? `<span class="text-muted" style="font-size: 0.75rem;">(${acadEscapeHtml(subject.code)})</span>` : ""}
                            ${badgeHtml}
                        </label>
                    </div>
                `;
                grid.appendChild(col);
            });

            list.appendChild(grid);
        }

        async function loadAcadSectionContext() {
            const method = document.getElementById('academicAssignmentFormMethod');
            if (method && method.value !== 'POST') {
                setAcadSingleSubjectMode();
                return;
            }

            const secSelect = document.getElementById('acad_field_section_id');
            const opt = secSelect?.selectedOptions[0];
            if (!opt || !opt.value) {
                setAcadSingleSubjectMode();
                return;
            }

            const gradeLevel = parseInt(opt.dataset.gradeLevel, 10);
            if (![1, 2, 3].includes(gradeLevel)) {
                setAcadSingleSubjectMode();
                return;
            }

            setAcadMassSubjectMode();

            const secId = opt.value;
            const syId = document.getElementById('acad_field_school_year_id')?.value || '';
            const tId = document.getElementById('acad_field_teacher_id')?.value || '';
            const list = document.getElementById('acad_mass_subjects_list');

            if (list) {
                list.innerHTML = '<div class="text-center py-3 text-muted small"><span class="spinner-border spinner-border-sm me-2"></span>Loading subjects...</div>';
            }

            try {
                const params = new URLSearchParams({ section_id: secId });
                if (syId) params.set('school_year_id', syId);
                if (tId) params.set('teacher_id', tId);

                const res = await fetch(@json(route('teaching-assignments.section-context')) + '?' + params.toString(), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) throw new Error('Network response was not ok');
                const data = await res.json();
                renderAcadMassSubjects(data.subjects || []);
            } catch (err) {
                if (list) list.innerHTML = '<div class="text-danger small text-center py-2">Failed to load subjects. Please try again.</div>';
            }
        }

        function openAcademicCreateAssignmentModal() {
            const form = document.getElementById('academicAssignmentForm');
            const method = document.getElementById('academicAssignmentFormMethod');
            const submitText = document.getElementById('academicAssignmentSubmitText');

            form.action = @json(route('teaching-assignments.store'));
            method.value = 'POST';
            submitText.textContent = 'Save Assignment';

            ['acad_field_teacher_id', 'acad_field_subject_id', 'acad_field_section_id', 'acad_field_school_year_id', 'acad_field_status'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.selectedIndex = 0;
            });

            // Pre-select active school year
            const sySelect = document.getElementById('acad_field_school_year_id');
            if (sySelect) {
                const activeOpt = sySelect.querySelector('option[data-active="1"]');
                if (activeOpt) sySelect.value = activeOpt.value;
            }

            setAcadSingleSubjectMode();
        }

        function openAcademicEditAssignmentModal(assignmentId, teacherId, subjectId, sectionId, schoolYearId, status) {
            const form = document.getElementById('academicAssignmentForm');
            const method = document.getElementById('academicAssignmentFormMethod');
            const submitText = document.getElementById('academicAssignmentSubmitText');

            const updateUrlTemplate = @json(route('teaching-assignments.update', ':id'));
            form.action = updateUrlTemplate.replace(':id', assignmentId);
            method.value = 'PUT';
            submitText.textContent = 'Save Changes';

            const setVal = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.value = val;
            };

            setVal('acad_field_teacher_id', teacherId);
            setVal('acad_field_section_id', sectionId);
            setVal('acad_field_school_year_id', schoolYearId);
            setVal('acad_field_status', status);

            setAcadSingleSubjectMode();
            setVal('acad_field_subject_id', subjectId);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const secSelect = document.getElementById('acad_field_section_id');
            const sySelect = document.getElementById('acad_field_school_year_id');
            const tSelect = document.getElementById('acad_field_teacher_id');

            secSelect?.addEventListener('change', loadAcadSectionContext);

            sySelect?.addEventListener('change', () => {
                const opt = secSelect?.selectedOptions[0];
                const gradeLevel = parseInt(opt?.dataset?.gradeLevel, 10);
                if ([1, 2, 3].includes(gradeLevel)) {
                    loadAcadSectionContext();
                }
            });

            tSelect?.addEventListener('change', () => {
                const opt = secSelect?.selectedOptions[0];
                const gradeLevel = parseInt(opt?.dataset?.gradeLevel, 10);
                if ([1, 2, 3].includes(gradeLevel)) {
                    loadAcadSectionContext();
                }
            });

            document.getElementById('acad_btnSelectAllSubjects')?.addEventListener('click', () => {
                document.querySelectorAll('#acad_mass_subjects_list input[type="checkbox"]:not(:disabled)').forEach(chk => {
                    chk.checked = true;
                });
            });

            document.getElementById('acad_btnDeselectAllSubjects')?.addEventListener('click', () => {
                document.querySelectorAll('#acad_mass_subjects_list input[type="checkbox"]:not(:disabled)').forEach(chk => {
                    chk.checked = false;
                });
            });
        });
    </script>
@endpush