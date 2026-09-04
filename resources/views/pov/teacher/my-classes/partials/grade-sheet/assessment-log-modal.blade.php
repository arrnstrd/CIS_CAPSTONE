{{-- =========================================================
     ASSESSMENT LOG MODAL
     ========================================================= --}}
<div class="modal fade"
     id="assessmentLogModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    <i class="fa-solid fa-layer-group me-2 text-warning-emphasis"></i>
                    Additional Assessments
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <div class="alert alert-info py-2 px-3 mb-3 small" role="alert">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    <strong>Assessment Guide:</strong>
                    Assessments beyond the visible Grade Sheet columns (WW1–WW5, PT1–PT5, EX1–EX3) are listed here.
                    Student names are shown only for those with a recorded score. These are valid assessments and do not affect existing grading calculations.
                </div>

                <div id="assessmentLogContent" style="max-height: 60vh; overflow-y: auto;">
                    <!-- Assessment log entries will be loaded here -->
                </div>

            </div>

            <div class="modal-footer">

                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Close
                </button>

            </div>

        </div>

    </div>

</div>
