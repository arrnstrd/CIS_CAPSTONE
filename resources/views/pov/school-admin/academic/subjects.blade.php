<div class="col mb-3 mx-2">
    <div class="bg-white rounded p-4 border">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <h3 class="fw-semibold text-dark m-0 fs-5">Subject Records</h3>
            <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#addSubjectModal" data-ajax-scope="#subject-table-pane">
                <span>+ Add Subject</span>
            </button>
        </div>
    </div>
</div>

<!-- Contextual Bulk Actions Bar (Pop Out) -->
<div class="px-2">
    <div id="subjectBulkBar" class="academic-bulk-bar d-none d-flex flex-row justify-content-between align-items-center flex-nowrap w-100">
        <div class="d-flex align-items-center gap-2 flex-nowrap">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fs-6 text-nowrap">
                <i class="fas fa-check-double me-1"></i> <span id="subjectSelectedCount">0</span>
            </span>
            <span class="fw-semibold text-dark fs-6 text-nowrap">Subject(s) Selected</span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-nowrap ms-auto">
            <button type="button" id="subjectBulkDeleteBtn" class="btn btn-danger fw-semibold d-none text-nowrap" data-bs-toggle="modal" data-bs-target="#bulkDeleteSubjectModal">
                <i class="fas fa-trash me-2"></i> Delete Selected
            </button>
            <button type="button" class="btn btn-outline-secondary text-nowrap" onclick="window.clearSubjectSelection && window.clearSubjectSelection()">
                <i class="fas fa-times me-1"></i> Deselect
            </button>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="table-panel shadow-sm">


        <table class="table table-hover align-middle table-striped mb-0">
            <thead class="text-uppercase">
                <tr>
                    <th width="4%" class="text-center">
                        <input type="checkbox" id="selectAllSubjects" class="form-check-input academic-check-input select-all-checkbox">
                    </th>
                    <th width="20%" data-column="code" data-column-title="Code" data-column-type="text">Code</th>
                    <th width="42%" data-column="subject" data-column-title="Subject" data-column-type="text">Subject</th>
                    <th width="20%" data-column="level" data-column-title="Level" data-column-type="categorical">Level</th>
                    <th width="14%" class="text-center">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($subjects as $subject)
                    <tr data-col-code="{{ $subject->code }}" data-col-subject="{{ $subject->name }}" data-col-level="{{ $subject->level_label }}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input academic-check-input subject-select-checkbox row-checkbox"
                                value="{{ $subject->id }}">
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $subject->code ?: '—' }}</span>
                        </td>
                        <td>
                            <span class="fw-medium text-dark">{{ $subject->name }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border">{{ $subject->level_label }}</span>
                        </td>
                        <td class="text-center text-nowrap">
                            <div class="action-btn-group justify-content-center">
                                <button type="button" class="btn btn-sm btn-outline-primary js-edit-subject"
                                    data-bs-toggle="modal" data-bs-target="#editSubjectModal"
                                    data-id="{{ $subject->id }}"
                                    data-code="{{ $subject->code }}"
                                    data-name="{{ $subject->name }}"
                                    data-level="{{ $subject->level }}"
                                    data-update-url="{{ route('subjects.update', $subject) }}"
                                    data-ajax-scope="#subject-table-pane">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    <span>Edit</span>
                                </button>
                                <form action="{{ route('subjects.destroy', $subject) }}" method="POST"
                                    data-ajax-delete="subject" data-ajax-scope="#subject-table-pane" class="d-inline mb-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa-solid fa-trash"></i>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">No subjects found for the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-3 py-3">
            {{ $subjects->links() }}
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Generic "Select All" initialization handler using DOM traversal
            function initGenericSelectAll() {
                document.querySelectorAll('.select-all-checkbox').forEach(function (selectAllCb) {
                    const table = selectAllCb.closest('table');
                    if (!table) return;

                    const rowCheckboxes = table.querySelectorAll('.row-checkbox');

                    // Sync Select All checkbox state based on visible row checkboxes
                    function syncSelectAllState() {
                        const visibleCheckboxes = Array.from(table.querySelectorAll('tbody tr:not(.d-none) .row-checkbox'));
                        const checkedCount = visibleCheckboxes.filter(cb => cb.checked).length;
                        selectAllCb.checked = visibleCheckboxes.length > 0 && checkedCount === visibleCheckboxes.length;
                        selectAllCb.indeterminate = checkedCount > 0 && checkedCount < visibleCheckboxes.length;
                    }

                    // Attach listener to Select All checkbox
                    selectAllCb.onclick = function () {
                        const visibleCheckboxes = Array.from(table.querySelectorAll('tbody tr:not(.d-none) .row-checkbox'));
                        visibleCheckboxes.forEach(function (cb) {
                            if (!cb.disabled) {
                                cb.checked = selectAllCb.checked;
                                cb.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });
                    };

                    // Listen to each row checkbox to update Select All state
                    rowCheckboxes.forEach(function (cb) {
                        cb.addEventListener('change', syncSelectAllState);
                    });

                    syncSelectAllState();
                });
            }

            function initSubjectBulk() {
                const pane = document.getElementById('subject-table-pane');
                if (!pane) return;
                
                const selectAll = pane.querySelector('.select-all-checkbox');
                const checkboxes = pane.querySelectorAll('.row-checkbox');
                const bulkBar = document.getElementById('subjectBulkBar');
                const countText = document.getElementById('subjectSelectedCount');
                const deleteBtn = document.getElementById('subjectBulkDeleteBtn');
                
                function updateUI() {
                    const checked = Array.from(checkboxes).filter(cb => cb.checked);
                    const count = checked.length;
                    
                    if (countText) countText.textContent = count;
                    if (bulkBar) bulkBar.classList.toggle('d-none', count === 0);
                    
                    checkboxes.forEach(cb => {
                        const tr = cb.closest('tr');
                        if (tr) tr.classList.toggle('academic-selected-row', cb.checked);
                    });
                    
                    if (selectAll) {
                        selectAll.checked = checkboxes.length > 0 && count === checkboxes.length;
                    }
                    
                    const currentDeleteBtn = document.getElementById('subjectBulkDeleteBtn');
                    if (currentDeleteBtn) {
                        currentDeleteBtn.classList.toggle('d-none', count === 0);
                    }
                }
                
                checkboxes.forEach(cb => {
                    cb.removeEventListener('change', updateUI);
                    cb.addEventListener('change', updateUI);
                });
                
                window.clearSubjectSelection = function() {
                    checkboxes.forEach(cb => cb.checked = false);
                    if (selectAll) selectAll.checked = false;
                    updateUI();
                };
                
                if (deleteBtn) {
                    $(deleteBtn).off('click').on('click', function () {
                        const checked = Array.from(checkboxes).filter(cb => cb.checked);
                        if (checked.length === 0) return;
                        
                        const countEl = document.getElementById('bulkDeleteSubjectCount');
                        if (countEl) countEl.textContent = checked.length;
                        
                        const errorDiv = document.getElementById('bulkDeleteSubjectError');
                        if (errorDiv) errorDiv.classList.add('d-none');
                    });
                }
                
                const confirmDeleteBtn = document.getElementById('confirmBulkDeleteSubjectBtn');
                if (confirmDeleteBtn) {
                    $(confirmDeleteBtn).off('click').on('click', async function () {
                        const checked = Array.from(checkboxes).filter(cb => cb.checked);
                        if (checked.length === 0) return;
                        
                        const $btn = $(this);
                        const errorDiv = document.getElementById('bulkDeleteSubjectError');
                        if (errorDiv) errorDiv.classList.add('d-none');
                        
                        const formData = new FormData();
                        formData.append("_token", document.querySelector('meta[name="csrf-token"]')?.content || "");
                        checked.forEach(cb => formData.append("ids[]", cb.value));
                        
                        const originalHtml = $btn.html();
                        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...');
                        
                        try {
                            const response = await fetch(@json(route('subjects.bulk-destroy')), {
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
                                $btn.prop('disabled', false).html(originalHtml);
                                return;
                            }
                            
                            // Clean modal teardown
                            const $modal = $('#bulkDeleteSubjectModal');
                            $modal.modal('hide');
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css('padding-right', '');
                            
                            $btn.prop('disabled', false).html(originalHtml);
                            window.clearSubjectSelection();

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
                            $btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            }
            
            initGenericSelectAll();
            initSubjectBulk();
            document.addEventListener("ajax:table-refreshed", function() {
                initGenericSelectAll();
                initSubjectBulk();
            });
            document.addEventListener("ajax:content-refreshed", function() {
                initGenericSelectAll();
                initSubjectBulk();
            });
        });
    </script>
@endpush

<x-modal id="addSubjectModal" modalTitle="Add Subject" size="modal-md" centered animation="modal-anim-slide">
    <form id="addSubjectForm" action="{{ route('subjects.store') }}" method="POST" data-ajax-form="subject"
        data-ajax-scope="#subject-table-pane">
        @csrf
        <div data-ajax-errors></div>

        {{-- 1. Level Selection First --}}
        <div class="p-3 bg-light rounded-3 border mb-3">
            <label class="form-label fw-bold text-dark d-flex align-items-center gap-1.5 mb-1">
                <i class="fa-solid fa-layer-group text-primary"></i>
                <span>Level <span class="text-danger">*</span></span>
            </label>
            <select name="level" class="form-select border-primary" required>
                <option value="" selected disabled>-- Select Level --</option>
                @foreach (\App\Models\Subject::levelOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- 2. Subject Names (single or comma-separated for mass assignment) --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Subject Name(s) <span class="text-danger">*</span></label>
            <textarea name="names" class="form-control" rows="3"
                placeholder="e.g. Mathematics, English, Science&#10;Separate multiple subjects with a comma."
                required></textarea>
            <div class="form-text">Separate multiple subjects with a comma to create them all at once.</div>
        </div>

        {{-- 3. Code Mode --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Subject Code</label>
            <select name="code_mode" class="form-select">
                <option value="auto" selected>Auto-generate from name</option>
                <option value="blank">Leave blank</option>
            </select>
            <div class="form-text">Codes are optional. Auto-generated codes are derived from the subject name.</div>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-dark" data-loading-text="Creating...">Create Subject(s)</button>
        </div>
    </form>
</x-modal>

<x-modal id="editSubjectModal" modalTitle="Edit Subject" size="modal-md" centered animation="modal-anim-slide">
    <form id="editSubjectForm" method="POST" data-ajax-form="subject" data-ajax-scope="#subject-table-pane">
        @csrf
        @method('PUT')
        <div data-ajax-errors></div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Code</label>
            <input type="text" name="code" id="edit_subject_code" class="form-control">
            <div class="form-text">Optional. Leave blank to keep the subject without a code.</div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Subject Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="edit_subject_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Level <span class="text-danger">*</span></label>
            <select name="level" id="edit_subject_level" class="form-select" required>
                @foreach (\App\Models\Subject::levelOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-dark" data-loading-text="Saving...">Save Changes</button>
        </div>
    </form>
</x-modal>

<x-modal id="bulkDeleteSubjectModal" modalTitle="Confirm Mass Deletion" size="modal-md" centered animation="modal-anim-slide">
    <div class="p-2">
        <div class="alert alert-danger d-flex align-items-start gap-3 mb-3">
            <i class="fa-solid fa-triangle-exclamation fs-4 flex-shrink-0 mt-1 text-danger"></i>
            <div>
                <h6 class="fw-bold mb-1 text-danger">Destructive Action Warning</h6>
                <p class="mb-0 small text-secondary">
                    You are about to permanently delete <strong id="bulkDeleteSubjectCount" class="text-danger">0</strong> selected subject(s). This action cannot be undone and will permanently remove them from the system.
                </p>
            </div>
        </div>
        <div id="bulkDeleteSubjectError" class="alert alert-danger d-none small py-2 px-3 mb-2"></div>
        <p class="text-muted small mb-0">Are you sure you want to proceed with deleting the selected subjects?</p>
    </div>
    <div class="modal-footer px-0 pb-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmBulkDeleteSubjectBtn" class="btn btn-danger fw-semibold">
            <i class="fas fa-trash me-1"></i> Permanently Delete
        </button>
    </div>
</x-modal>