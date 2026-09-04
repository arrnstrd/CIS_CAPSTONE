{{-- =========================================================
     ADD COLUMN MODAL
     ========================================================= --}}
<div class="modal fade"
     id="addColumnModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="fa-solid fa-plus-circle me-1 text-primary"></i>
                    Add Assessment Column
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <input type="hidden"
                       id="addColumnCategory">

                {{-- CLASS & CONTEXT CARD --}}
                <div class="p-3 mb-3 rounded border bg-light">
                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted d-block">Subject:</span>
                            <strong class="text-dark">{{ $ta->subject->name }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Section:</span>
                            <strong class="text-dark">Grade {{ $ta->section->grade_level }} - {{ $ta->section->name }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Grading Period:</span>
                            <strong class="text-dark">Term {{ $gradingPeriods->firstWhere('id', $selectedPeriodId)?->sequence ?? '—' }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Category:</span>
                            <span id="modalCategoryBadge" class="badge bg-primary">—</span>
                        </div>
                    </div>
                </div>

                {{-- SLOT STATUS / WARNING ALERT --}}
                <div id="modalSlotStatusAlert" class="alert alert-info py-2 px-3 mb-3 small d-flex align-items-center gap-2">
                    <i class="fa-solid fa-info-circle"></i>
                    <span id="modalSlotStatusText">Calculating available slots...</span>
                </div>

                {{-- FORM FIELDS --}}
                <div id="modalFormFields">

                    <div class="mb-3">
                        <label class="gd-form-label fw-semibold">
                            Title <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="addColumnTitle"
                               placeholder="e.g. Quiz 1, Activity 2">
                    </div>

                    <div class="mb-3">
                        <label class="gd-form-label fw-semibold">
                            Total Items (Highest Possible Score) <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                               min="1"
                               class="form-control"
                               id="addColumnTotal"
                               value="10">
                    </div>

                    <div class="mb-3">
                        <label class="gd-form-label fw-semibold">
                            Date
                        </label>
                        <input type="date"
                               class="form-control"
                               id="addColumnDate">
                    </div>

                    <div class="mb-3">
                        <label class="gd-form-label fw-semibold">
                            Description
                            <span class="text-muted small fw-normal">
                                (optional)
                            </span>
                        </label>
                        <textarea class="form-control"
                                  id="addColumnDescription"
                                  rows="2"
                                  placeholder="Brief description or objective..."></textarea>
                    </div>

                </div>

                <div class="text-danger small"
                     id="addColumnError">
                </div>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button"
                        class="btn gd-btn-primary"
                        id="addColumnSubmit">
                    <span id="addColumnSpinner"
                          class="spinner-border spinner-border-sm me-1 d-none"
                          role="status">
                    </span>
                    <span id="addColumnBtnText">
                        Add Column
                    </span>
                </button>

            </div>

        </div>

    </div>

</div>
