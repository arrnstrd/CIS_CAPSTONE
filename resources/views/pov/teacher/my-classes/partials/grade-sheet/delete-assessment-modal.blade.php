{{-- =========================================================
     DELETE ASSESSMENT CONFIRMATION MODAL
     ========================================================= --}}
<div class="modal fade"
     id="deleteAssessmentModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    Confirm Delete Assessment
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <input type="hidden" id="deleteAssessmentId">

                <h6 class="fw-bold text-dark mb-2" id="deleteAssessmentPrompt">
                    Delete Assessment?
                </h6>

                <p class="text-muted small mb-0">
                    This will remove this assessment and its recorded student scores. This action cannot be undone.
                </p>

                <div class="text-danger small mt-2" id="deleteAssessmentError"></div>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button"
                        class="btn btn-danger"
                        id="deleteAssessmentSubmit"
                        onclick="submitDeleteAssessment()">
                    <span id="deleteAssessmentSpinner"
                          class="spinner-border spinner-border-sm me-1 d-none"
                          role="status">
                    </span>
                    <span id="deleteAssessmentBtnText">
                        Delete Assessment
                    </span>
                </button>

            </div>

        </div>

    </div>

</div>
