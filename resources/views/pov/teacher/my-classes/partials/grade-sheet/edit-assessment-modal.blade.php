{{-- =========================================================
     EDIT ASSESSMENT MODAL
     ========================================================= --}}
<div class="modal fade"
     id="editAssessmentModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="fa-solid fa-pen-to-square me-1 text-primary"></i>
                    Edit Assessment Details
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <input type="hidden" id="editAssessmentId">

                {{-- CONTEXT CARD --}}
                <div class="p-3 mb-3 rounded border bg-light">
                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted d-block">Slot:</span>
                            <span id="editModalSlotBadge" class="badge bg-dark font-monospace">—</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Category:</span>
                            <span id="editModalCategoryBadge" class="badge bg-primary">—</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Subject:</span>
                            <strong class="text-dark">{{ $ta->subject->name }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Section:</span>
                            <strong class="text-dark">Grade {{ $ta->section->grade_level }} - {{ $ta->section->name }}</strong>
                        </div>
                    </div>
                </div>

                {{-- FORM FIELDS --}}
                <div class="mb-3">
                    <label class="gd-form-label fw-semibold">
                        Title <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           class="form-control"
                           id="editAssessmentTitle"
                           placeholder="e.g. Quiz 1, Activity 2">
                </div>

                <div class="mb-3">
                    <label class="gd-form-label fw-semibold">
                        Total Items (Highest Possible Score) <span class="text-danger">*</span>
                    </label>
                    <input type="number"
                           min="1"
                           class="form-control"
                           id="editAssessmentTotal">
                </div>

                <div class="mb-3">
                    <label class="gd-form-label fw-semibold">
                        Date
                    </label>
                    <input type="date"
                           class="form-control"
                           id="editAssessmentDate">
                </div>

                <div class="mb-3">
                    <label class="gd-form-label fw-semibold">
                        Description
                        <span class="text-muted small fw-normal">
                            (optional)
                        </span>
                    </label>
                    <textarea class="form-control"
                              id="editAssessmentDescription"
                              rows="2"
                              placeholder="Brief description or objective..."></textarea>
                </div>

                <div class="text-danger small" id="editAssessmentError"></div>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button"
                        class="btn gd-btn-primary"
                        id="editAssessmentSubmit"
                        onclick="submitEditAssessment()">
                    <span id="editAssessmentSpinner"
                          class="spinner-border spinner-border-sm me-1 d-none"
                          role="status">
                    </span>
                    <span id="editAssessmentBtnText">
                        Save Changes
                    </span>
                </button>

            </div>

        </div>

    </div>

</div>
