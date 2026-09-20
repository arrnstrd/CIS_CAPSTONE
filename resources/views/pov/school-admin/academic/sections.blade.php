<div class="col mb-3 mx-2">
    <div class="bg-white rounded p-4 border">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <h3 class="fw-semibold text-dark m-0 fs-5">Section Records</h3>
            <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#addSectionModal" data-ajax-scope="#section-table-pane">
                <span>+ Add Section</span>
            </button>
        </div>
    </div>
</div>

<!-- Contextual Bulk Actions Bar (Pop Out) -->
<div class="px-2">
    <div id="sectionBulkBar" class="academic-bulk-bar d-none d-flex flex-row justify-content-between align-items-center flex-nowrap w-100">
        <div class="d-flex align-items-center gap-3 flex-nowrap">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fs-6">
                <i class="fas fa-check-double me-1"></i> <span id="sectionSelectedCount">0</span>
            </span>
            <span class="fw-semibold text-dark fs-6 text-nowrap">Section(s) Selected</span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-nowrap">
            <button type="button" id="sectionBulkArchiveBtn" class="btn btn-warning fw-semibold d-none text-nowrap">
                <i class="fas fa-archive me-2"></i> Archive Selected
            </button>
            <button type="button" id="sectionBulkRestoreBtn" class="btn btn-success fw-semibold d-none text-nowrap">
                <i class="fas fa-play me-2"></i> Restore Selected
            </button>
            <button type="button" class="btn btn-outline-secondary ms-2 text-nowrap" onclick="window.clearSectionSelection && window.clearSectionSelection()">
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
                        <input type="checkbox" id="selectAllSections" class="form-check-input academic-check-input select-all-checkbox">
                    </th>
                    <th width="15%" data-column="section" data-column-title="Section" data-column-type="text">Section</th>
                    <th width="12%" data-column="grade" data-column-title="Grade" data-column-type="grade">Grade</th>
                    <th width="15%" data-column="level" data-column-title="Level" data-column-type="categorical">Level</th>
                    <th width="20%" data-column="advisor" data-column-title="Advisor" data-column-type="advisor">Advisor</th>
                    <th width="13%" data-column="capacity" data-column-title="Capacity" data-column-type="capacity">Capacity</th>
                    <th width="11%" data-column="status" data-column-title="Status" data-column-type="status">Status</th>
                    <th width="10%">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($sections as $section)
                    @php
                        $vacancy = max(0, $section->capacity - $section->students_count);
                        $statusText = ucfirst($section->status);
                    @endphp
                    <tr data-col-section="{{ $section->name }}"
                        data-col-grade="Grade {{ $section->grade_level }}"
                        data-col-level="{{ Str::headline(str_replace('_', ' ', $section->level)) }}"
                        data-col-advisor="{{ $section->advisor?->full_name ?? 'Not Assigned' }}"
                        data-col-capacity="{{ $section->capacity }}"
                        data-col-enrolled="{{ $section->students_count }}"
                        data-col-vacant="{{ $vacancy }}"
                        data-col-status="{{ $statusText }}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input academic-check-input section-select-checkbox row-checkbox"
                                value="{{ $section->id }}" data-status="{{ $section->status }}">
                        </td>
                        <td><span class="fw-semibold text-dark">{{ $section->name }}</span></td>
                        <td><span class="badge bg-light text-secondary border">Grade {{ $section->grade_level }}</span></td>
                        <td>{{ Str::headline(str_replace('_', ' ', $section->level)) }}</td>
                        <td>
                            @if($section->advisor)
                                <span class="fw-medium text-dark">{{ $section->advisor->full_name }}</span>
                            @else
                                <span class="text-muted fst-italic">Not Assigned</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $section->students_count }} / {{ $section->capacity }}</div>
                            <div class="text-muted small" style="font-size: 0.72rem;">{{ $vacancy }} vacant</div>
                        </td>
                        <td>
                            <span class="badge-dot dot-{{ $section->status === 'active' ? 'success' : 'secondary' }}">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <button type="button" class="dropdown-item js-edit-section" data-bs-toggle="modal"
                                            data-bs-target="#editSectionModal" data-id="{{ $section->id }}"
                                            data-name="{{ $section->name }}" data-level="{{ $section->level }}"
                                            data-grade-level="{{ $section->grade_level }}"
                                            data-advisor-id="{{ $section->advisor_id ?? '' }}"
                                            data-capacity="{{ $section->capacity }}" data-status="{{ $section->status }}"
                                            data-session-type="{{ $section->session_type }}"
                                            data-ajax-scope="#section-table-pane">
                                            Edit
                                        </button>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    @if ($section->status === 'active')
                                        <li>
                                            <form action="{{ route('sections.destroy', $section->id) }}" method="POST"
                                                data-ajax-delete="section" data-ajax-scope="#section-table-pane">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-warning">Archive</button>
                                            </form>
                                        </li>
                                    @else
                                        <li>
                                            <form action="{{ route('sections.restore', $section->id) }}" method="POST"
                                                data-ajax-restore="section" data-ajax-scope="#section-table-pane">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item text-success">Restore</button>
                                            </form>
                                        </li>
                                    @endif
                                    <li>
                                        <button type="button" class="dropdown-item text-danger js-hard-delete-section"
                                            data-bs-toggle="modal" data-bs-target="#hardDeleteSectionModal"
                                            data-id="{{ $section->id }}" data-name="{{ $section->name }}"
                                            data-ajax-scope="#section-table-pane">
                                            Hard Delete
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">No sections found for the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-3 py-3">
            {{ $sections->links() }}
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function initSectionBulk() {
                const pane = document.getElementById('section-table-pane');
                if (!pane) return;
                
                const selectAll = pane.querySelector('.select-all-checkbox');
                const checkboxes = pane.querySelectorAll('.row-checkbox');
                const bulkBar = document.getElementById('sectionBulkBar');
                const countText = document.getElementById('sectionSelectedCount');
                const archiveBtn = document.getElementById('sectionBulkArchiveBtn');
                const restoreBtn = document.getElementById('sectionBulkRestoreBtn');
                
                function updateUI() {
                    const visibleCheckboxes = Array.from(pane.querySelectorAll('tbody tr:not(.d-none) .row-checkbox'));
                    const checked = visibleCheckboxes.filter(cb => cb.checked);
                    const count = checked.length;
                    
                    if (countText) countText.textContent = count;
                    if (bulkBar) bulkBar.classList.toggle('d-none', count === 0);
                    
                    pane.querySelectorAll('.row-checkbox').forEach(cb => {
                        const tr = cb.closest('tr');
                        if (tr) tr.classList.toggle('academic-selected-row', cb.checked);
                    });
                    
                    if (selectAll) {
                        selectAll.checked = visibleCheckboxes.length > 0 && count === visibleCheckboxes.length;
                    }
                    
                    const activeCount = checked.filter(cb => cb.dataset.status === 'active').length;
                    const inactiveCount = checked.filter(cb => cb.dataset.status === 'inactive').length;
                    
                    const currentArchiveBtn = document.getElementById('sectionBulkArchiveBtn');
                    const currentRestoreBtn = document.getElementById('sectionBulkRestoreBtn');
                    
                    if (currentArchiveBtn) currentArchiveBtn.classList.toggle('d-none', activeCount === 0);
                    if (currentRestoreBtn) currentRestoreBtn.classList.toggle('d-none', inactiveCount === 0);
                }
                
                checkboxes.forEach(cb => {
                    cb.removeEventListener('change', updateUI);
                    cb.addEventListener('change', updateUI);
                });
                
                window.clearSectionSelection = function() {
                    checkboxes.forEach(cb => cb.checked = false);
                    if (selectAll) selectAll.checked = false;
                    updateUI();
                };
                
                let currentBulkAction = null;
                let currentEligibleIds = [];
                
                function setupBulkModalTrigger(btn, actionType) {
                    if (!btn) return;
                    
                    $(btn).off('click').on('click', function () {
                        const checked = Array.from(checkboxes).filter(cb => cb.checked);
                        const eligible = checked.filter(cb => {
                            if (actionType === 'archive') return cb.dataset.status === 'active';
                            if (actionType === 'restore') return cb.dataset.status === 'inactive';
                            return true;
                        });
                        
                        if (eligible.length === 0) return;
                        
                        currentBulkAction = actionType;
                        currentEligibleIds = eligible.map(cb => cb.value);
                        
                        const modalEl = document.getElementById('bulkSectionConfirmModal');
                        const titleEl = modalEl?.querySelector('.modal-title');
                        const alertBox = document.getElementById('bulkSectionAlertBox');
                        const iconEl = document.getElementById('bulkSectionAlertIcon');
                        const headingEl = document.getElementById('bulkSectionAlertHeading');
                        const noticeEl = document.getElementById('bulkSectionAlertNotice');
                        const confirmBtn = document.getElementById('confirmBulkSectionBtn');
                        const errorDiv = document.getElementById('bulkSectionError');
                        
                        if (errorDiv) errorDiv.classList.add('d-none');
                        
                        if (actionType === 'archive') {
                            if (titleEl) titleEl.textContent = 'Confirm Mass Archive';
                            if (alertBox) alertBox.className = 'alert alert-warning d-flex align-items-start gap-3 mb-3';
                            if (iconEl) iconEl.className = 'fa-solid fa-box-archive fs-4 flex-shrink-0 mt-1 text-warning';
                            if (headingEl) headingEl.textContent = 'Archive Active Sections';
                            if (noticeEl) noticeEl.innerHTML = `You are about to archive <strong>${eligible.length}</strong> active section(s). They will be archived and hidden from active operations.`;
                            if (confirmBtn) {
                                confirmBtn.className = 'btn btn-warning fw-semibold';
                                confirmBtn.innerHTML = '<i class="fas fa-archive me-1"></i> Archive Selected';
                            }
                        } else {
                            if (titleEl) titleEl.textContent = 'Confirm Mass Restore';
                            if (alertBox) alertBox.className = 'alert alert-success d-flex align-items-start gap-3 mb-3';
                            if (iconEl) iconEl.className = 'fa-solid fa-rotate-left fs-4 flex-shrink-0 mt-1 text-success';
                            if (headingEl) headingEl.textContent = 'Restore Inactive Sections';
                            if (noticeEl) noticeEl.innerHTML = `You are about to restore <strong>${eligible.length}</strong> inactive section(s). They will become active again.`;
                            if (confirmBtn) {
                                confirmBtn.className = 'btn btn-success fw-semibold';
                                confirmBtn.innerHTML = '<i class="fas fa-play me-1"></i> Restore Selected';
                            }
                        }
                        
                        if (modalEl) {
                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.show();
                        }
                    });
                }
                
                setupBulkModalTrigger(archiveBtn, 'archive');
                setupBulkModalTrigger(restoreBtn, 'restore');
                
                const confirmBulkBtn = document.getElementById('confirmBulkSectionBtn');
                if (confirmBulkBtn) {
                    $(confirmBulkBtn).off('click').on('click', async function () {
                        if (!currentBulkAction || currentEligibleIds.length === 0) return;
                        
                        const $btn = $(this);
                        const errorDiv = document.getElementById('bulkSectionError');
                        if (errorDiv) errorDiv.classList.add('d-none');
                        
                        const targetUrl = currentBulkAction === 'archive' 
                            ? @json(route('sections.bulk-destroy')) 
                            : @json(route('sections.bulk-restore'));
                            
                        const formData = new FormData();
                        formData.append("_token", document.querySelector('meta[name="csrf-token"]')?.content || "");
                        currentEligibleIds.forEach(id => formData.append("ids[]", id));
                        
                        const originalHtml = $btn.html();
                        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
                        
                        try {
                            const response = await fetch(targetUrl, {
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
                            const $modal = $('#bulkSectionConfirmModal');
                            $modal.modal('hide');
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css('padding-right', '');
                            
                            $btn.prop('disabled', false).html(originalHtml);
                            window.clearSectionSelection();

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
            
            initSectionBulk();
            document.addEventListener("ajax:table-refreshed", initSectionBulk);
            document.addEventListener("ajax:content-refreshed", initSectionBulk);
        });
    </script>
@endpush

<x-modal id="addSectionModal" modalTitle="Add Section" size="modal-md">
    <form id="addSectionForm" action="{{ route('sections.store') }}" method="POST"
        data-ajax-scope="#section-table-pane">
        @csrf
        <div data-ajax-errors></div>

        {{-- 1. Step One: Grade Level Selection First --}}
        <div class="p-3 bg-light rounded-3 border mb-3">
            <label class="form-label fw-bold text-dark d-flex align-items-center gap-1.5 mb-1">
                <i class="fa-solid fa-layer-group text-primary"></i>
                <span>1. Select Grade Level <span class="text-danger">*</span></span>
            </label>
            <p class="text-muted small mb-2">You must select a Grade Level first before configuring section details.</p>
            <select name="grade_level" class="form-select border-primary" id="add_section_grade_level" required>
                <option value="" selected disabled>-- Select Grade Level First --</option>
                @for ($grade = 1; $grade <= 12; $grade++)
                    <option value="{{ $grade }}" {{ request('section_grade_level') == $grade ? 'selected' : '' }}>Grade
                        {{ $grade }}</option>
                @endfor
            </select>
        </div>

        <div id="add_section_grade_hint" class="alert alert-info py-2 px-3 small mb-3">
            <i class="fa-solid fa-circle-info me-1"></i> Please select a grade level above to enable section fields.
        </div>

        {{-- Section details (disabled until grade level selected) --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Section Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="add_section_name" class="form-control js-section-field"
                placeholder="e.g. Acacia, Diamond" required disabled>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Department Level</label>
                <select name="level" class="form-select bg-light js-section-field" id="add_section_level" required
                    readonly tabindex="-1">
                    <option value="" selected disabled>Select level</option>
                    <option value="elementary">Elementary (Grades 1-6)</option>
                    <option value="highschool">High School (Grades 7-10)</option>
                    <option value="senior_high_school">Senior High School (Grades 11-12)</option>
                </select>
                <small class="text-muted" style="font-size: 0.72rem;">Auto-assigned based on Grade Level</small>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Session Type <span class="text-danger">*</span></label>
                <select name="session_type" id="add_section_session_type" class="form-select js-section-field" required
                    disabled>
                    <option value="" disabled selected>Select session</option>
                    <option value="morning">Morning</option>
                    <option value="afternoon">Afternoon</option>
                    <option value="whole_day">Whole Day</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Adviser</label>
            <select name="advisor_id" id="add_section_advisor_id" class="form-select js-section-field" disabled>
                <option value="">Not Assigned</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Capacity <span class="text-danger">*</span></label>
                <input type="number" name="capacity" id="add_section_capacity" class="form-control js-section-field"
                    min="1" max="100" value="40" required disabled>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="add_section_status" class="form-select js-section-field" required disabled>
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-dark js-section-submit" data-loading-text="Creating..." disabled>Create
                Section</button>
        </div>
    </form>
</x-modal>

@include('pov.school-admin.academic.modals.edit-section-modal')

<x-modal id="hardDeleteSectionModal" modalTitle="Permanently Delete Section" size="modal-md">
    <form id="hardDeleteSectionForm" method="POST" data-delete-url="{{ route('sections.force-delete', ':id') }}"
        data-ajax-scope="#section-table-pane">
        @csrf
        @method('DELETE')
        <div data-ajax-errors></div>

        <div class="alert alert-danger d-flex align-items-start gap-2 mb-3">
            <i class="fa-solid fa-triangle-exclamation fs-5 flex-shrink-0 mt-1"></i>
            <div>
                <strong>Warning: Destructive Action!</strong>
                <p class="mb-0 small">You are about to permanently delete the section <strong
                        id="hard_delete_section_name"></strong>. This action is <strong>destructive and cannot be
                        undone</strong>.</p>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Confirm Password</label>
            <input type="password" name="password" id="hard_delete_password" class="form-control"
                placeholder="Enter your password to authenticate" required autocomplete="current-password">
            <div class="form-text">Your password is required to confirm this hard delete.</div>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger" data-loading-text="Deleting...">Permanently Delete</button>
        </div>
    </form>
</x-modal>

<x-modal id="bulkSectionConfirmModal" modalTitle="Confirm Section Action" size="modal-md" centered animation="modal-anim-slide">
    <div class="p-2">
        <div id="bulkSectionAlertBox" class="alert alert-warning d-flex align-items-start gap-3 mb-3">
            <i id="bulkSectionAlertIcon" class="fa-solid fa-triangle-exclamation fs-4 flex-shrink-0 mt-1"></i>
            <div>
                <h6 id="bulkSectionAlertHeading" class="fw-bold mb-1">Confirm Action</h6>
                <p id="bulkSectionAlertNotice" class="mb-0 small text-secondary">
                    You are about to modify selected sections.
                </p>
            </div>
        </div>
        <div id="bulkSectionError" class="alert alert-danger d-none small py-2 px-3 mb-2"></div>
        <p class="text-muted small mb-0">Are you sure you want to proceed?</p>
    </div>
    <div class="modal-footer px-0 pb-0">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmBulkSectionBtn" class="btn btn-warning fw-semibold">
            Confirm Action
        </button>
    </div>
</x-modal>