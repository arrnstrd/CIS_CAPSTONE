{{-- Intervention Modal (Teacher POV - Single & Bulk Modes) --}}
<div class="modal fade" id="interventionModal" tabindex="-1" aria-labelledby="interventionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            
            {{-- Modal Header --}}
            <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff;">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.72rem; font-weight: 600;">
                            <i class="fa-solid fa-paper-plane me-1"></i> Intervention Notice
                        </span>
                        <span id="interventionModeBadge" class="badge bg-light text-dark" style="font-size: 0.72rem;">Single Student</span>
                    </div>
                    <h5 class="modal-title fs-6 fw-bold mb-0 text-white" id="interventionModalLabel">
                        At-Risk Student Parent Communication
                    </h5>
                    <p class="text-white-50 small mb-0" id="interventionModalSubtitle" style="font-size: 0.78rem;">
                        Review actual risk indicators and preview the student-specific intervention notice before dispatch.
                    </p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4" style="background-color: #f8fafc;">
                
                {{-- Loading Spinner --}}
                <div id="interventionLoading" class="text-center py-5">
                    <div class="spinner-border text-primary mb-2" role="status" style="width: 2.2rem; height: 2.2rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted small mb-0">Analyzing risk indicators and preparing student previews...</p>
                </div>

                {{-- Error Alert --}}
                <div id="interventionError" class="alert alert-danger d-none py-2 px-3 small mb-3" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    <span id="interventionErrorMessage">Failed to load intervention data.</span>
                </div>

                {{-- Success Alert --}}
                <div id="interventionSuccess" class="alert alert-success d-none py-2 px-3 small mb-3" role="alert">
                    <i class="fa-solid fa-circle-check me-1"></i>
                    <span id="interventionSuccessMessage">Intervention email sent successfully.</span>
                </div>

                {{-- Intervention Content Container --}}
                <div id="interventionContent" class="d-none">
                    
                    {{-- Global Language Selector Option --}}
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px;">
                        <div class="card-body p-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background-color: #ffffff;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary p-1 px-2" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-language"></i>
                                </span>
                                <div>
                                    <span class="small fw-bold text-dark d-block" style="font-size: 0.8rem; line-height: 1.2;">
                                        Message Language
                                    </span>
                                    <span class="text-muted small" style="font-size: 0.72rem;">
                                        Select English or natural Tagalog for the parent notice
                                    </span>
                                </div>
                            </div>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Message Language Selection">
                                <input type="radio" class="btn-check" name="modalMessageLang" id="langOptionEn" value="en" checked autocomplete="off">
                                <label class="btn btn-outline-primary py-1 px-3 fw-semibold" for="langOptionEn" style="font-size: 0.76rem;">
                                    English
                                </label>

                                <input type="radio" class="btn-check" name="modalMessageLang" id="langOptionTl" value="tl" autocomplete="off">
                                <label class="btn btn-outline-primary py-1 px-3 fw-semibold" for="langOptionTl" style="font-size: 0.76rem;">
                                    Tagalog / Filipino
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- 1. SINGLE STUDENT VIEW --}}
                    <div id="singleStudentSection">
                        
                        {{-- Student Summary & Contact Card --}}
                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px;">
                            <div class="card-body p-3">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-7">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <h6 class="fw-bold mb-0 text-dark" id="modalStudentName">—</h6>
                                            <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.7rem;" id="modalStudentNumber">—</span>
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.78rem;">
                                            <span id="modalClassInfo">—</span> &middot; <span id="modalSubjectInfo">—</span>
                                        </div>
                                    </div>
                                    <div class="col-md-5 text-md-end">
                                        <div class="text-muted small" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600;">Recipient (Parent/Guardian)</div>
                                        <div class="fw-semibold text-dark small" id="modalGuardianName">—</div>
                                        <div class="text-secondary small font-monospace" style="font-size: 0.78rem;" id="modalGuardianEmail">—</div>
                                    </div>
                                </div>

                                {{-- Missing Guardian Email Warning --}}
                                <div id="missingEmailWarning" class="alert alert-warning py-1 px-2 mt-2 mb-0 small d-none" style="font-size: 0.76rem;">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                    <strong>No parent/guardian email on file.</strong> An email notification cannot be delivered until contact information is provided.
                                </div>
                            </div>
                        </div>

                        {{-- At-Risk Indicators Card --}}
                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em; color: #64748b;">
                                        Evaluated Risk Condition
                                    </span>
                                    <span id="modalConditionBadge" class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        Academic Risk
                                    </span>
                                </div>
                                <div class="row g-2 align-items-center">
                                    <div class="col-sm-6" id="gradeMetricCol">
                                        <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-center">
                                            <span class="text-muted small" style="font-size: 0.76rem;">Current Average:</span>
                                            <span class="fw-bold text-dark" id="modalGradeValue">—</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6" id="attendanceMetricCol">
                                        <div class="p-2 border rounded bg-light d-flex justify-content-between align-items-center">
                                            <span class="text-muted small" style="font-size: 0.76rem;">Attendance Rate:</span>
                                            <span class="fw-bold text-dark" id="modalAttendanceValue">—</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 d-flex flex-wrap gap-1" id="modalActiveIndicatorsList">
                                    {{-- Dynamic badges --}}
                                </div>
                            </div>
                        </div>

                        {{-- Realistic Email Preview Box --}}
                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px;">
                            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                <span class="fw-bold small text-dark" style="font-size: 0.78rem;">
                                    <i class="fa-regular fa-envelope me-1 text-primary"></i> Parent Email Preview
                                </span>
                                <span id="previewNoticeBanner" class="badge bg-light text-muted border font-monospace" style="font-size: 0.68rem;">Live Template Render</span>
                            </div>
                            <div class="card-body p-3" style="background-color: #ffffff; border-radius: 0 0 8px 8px;">
                                <div class="border-bottom pb-2 mb-2" style="font-size: 0.78rem; line-height: 1.5;">
                                    <div><strong class="text-muted me-1">To:</strong> <span id="previewToRecipient" class="text-dark font-monospace">—</span></div>
                                    <div><strong class="text-muted me-1">Subject:</strong> <span id="previewSubject" class="text-dark fw-semibold">—</span></div>
                                </div>

                                <div class="p-3 border rounded-2" style="background-color: #fafbfc; font-size: 0.82rem; line-height: 1.6; color: #334155;">
                                    <div class="border-bottom pb-2 mb-2 d-flex align-items-center justify-content-between">
                                        <span class="fw-bold text-primary" style="font-size: 0.8rem;">Concepcion Integrated School</span>
                                        <span id="previewHeaderBadge" class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Academic Monitoring</span>
                                    </div>
                                    
                                    <p class="mb-2"><span class="fw-semibold" id="previewSalutation">—</span>,</p>
                                    
                                    <div id="previewMessageBody" style="white-space: pre-line;" class="mb-3">
                                        —
                                    </div>

                                    <div id="previewTeacherNoteCallout" class="p-2 mb-3 border-start border-3 border-primary bg-primary-subtle d-none" style="border-radius: 4px; font-size: 0.78rem;">
                                        <div id="previewTeacherNoteLabel" class="fw-bold text-primary mb-1" style="font-size: 0.7rem; text-transform: uppercase;">Teacher's Note:</div>
                                        <span id="previewTeacherNoteText" class="text-dark"></span>
                                    </div>

                                    <div id="previewClosingBlock" class="pt-2 text-muted" style="font-size: 0.75rem; white-space: pre-line;">
                                        —
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Optional Teacher Note Input --}}
                        <div class="card border-0 shadow-sm mb-2" style="border-radius: 8px;">
                            <div class="card-body p-3">
                                <label for="singleTeacherNote" class="form-label small fw-semibold text-dark mb-1" style="font-size: 0.78rem;">
                                    <i class="fa-solid fa-pen me-1 text-secondary"></i> Optional Teacher Note to Parent:
                                </label>
                                <textarea id="singleTeacherNote" class="form-control form-control-sm" rows="2" maxlength="1000"
                                    placeholder="Add any specific guidance, schedule for consultation, or personalized message for the parent..."
                                    style="font-size: 0.8rem;"></textarea>
                                <div class="form-text text-muted" style="font-size: 0.7rem;">
                                    This note will be highlighted directly in the email sent to the parent.
                                </div>
                            </div>
                        </div>

                    </div> {{-- END singleStudentSection --}}


                    {{-- 2. BULK STUDENT REVIEW VIEW --}}
                    <div id="bulkStudentSection" class="d-none">
                        
                        {{-- Bulk Summary & Controls Bar --}}
                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px;">
                            <div class="card-body p-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #f1f5f9;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary text-white p-1 px-2" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-users"></i>
                                    </span>
                                    <span id="bulkSummaryText" class="small fw-bold text-dark">
                                        0 students selected for intervention
                                    </span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" data-bs-dismiss="modal" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Modify Selection
                                </button>
                            </div>
                        </div>

                        {{-- Bulk Student Cards Container --}}
                        <div id="bulkStudentsListContainer" class="d-flex flex-column gap-3">
                            {{-- Dynamically populated per student card --}}
                        </div>

                        {{-- Empty State (if all students removed from batch) --}}
                        <div id="bulkEmptyState" class="alert alert-secondary text-center py-4 d-none" style="border-radius: 8px;">
                            <i class="fa-solid fa-user-xmark fa-2x mb-2 text-muted"></i>
                            <p class="fw-semibold text-dark mb-1">No students remaining in the intervention queue</p>
                            <p class="text-muted small mb-3">All selected students were removed from this session.</p>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal">
                                <i class="fa-solid fa-arrow-left me-1"></i> Return to Student Registry
                            </button>
                        </div>

                    </div> {{-- END bulkStudentSection --}}

                </div> {{-- END interventionContent --}}

                {{-- Dispatch Results Summary Container (Full or Partial Batch Report) --}}
                <div id="interventionResultsSummary" class="d-none">
                    <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div id="resultsSummaryIcon" class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.25rem;">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark" id="resultsSummaryTitle">Intervention Complete</h6>
                                    <p class="text-muted small mb-0" id="resultsSummarySubtitle">All notices have been processed.</p>
                                </div>
                            </div>

                            <div id="resultsSummaryStats" class="d-flex gap-2 mb-3">
                                {{-- Badges for sent & skipped counts --}}
                            </div>

                            {{-- Delivered List --}}
                            <div id="resultsDeliveredContainer" class="mb-3 d-none">
                                <div class="text-success small fw-semibold mb-1 text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                    <i class="fa-solid fa-check me-1"></i> Successfully Sent
                                </div>
                                <div class="list-group list-group-flush border rounded" id="resultsDeliveredList" style="max-height: 160px; overflow-y: auto; font-size: 0.78rem;">
                                </div>
                            </div>

                            {{-- Skipped List --}}
                            <div id="resultsSkippedContainer" class="mb-3 d-none">
                                <div class="text-warning-emphasis small fw-semibold mb-1 text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Skipped / Needs Follow-up
                                </div>
                                <div class="list-group list-group-flush border rounded" id="resultsSkippedList" style="max-height: 160px; overflow-y: auto; font-size: 0.78rem;">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-2 border-top">
                                <button type="button" class="btn btn-sm btn-primary px-3" onclick="window.location.reload()">
                                    <i class="fa-solid fa-rotate-right me-1"></i> Return to Registry & Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div> {{-- END modal-body --}}

            {{-- Modal Footer --}}
            <div class="modal-footer py-2 px-4 bg-white border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">
                    <span id="modalCloseBtnText">Close</span>
                </button>
                <button type="button" class="btn btn-sm btn-primary px-3" id="sendInterventionBtn" disabled>
                    <span id="sendBtnSpinner" class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                    <i class="fa-solid fa-paper-plane me-1" id="sendBtnIcon"></i>
                    <span id="sendBtnText">Send Intervention Email</span>
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Inline Vanilla JS Controller for Intervention Modal (Single & Bulk Modes) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('interventionModal');
    if (!modalEl) return;

    const bsModal = new bootstrap.Modal(modalEl);
    
    // UI Elements
    const loadingEl = document.getElementById('interventionLoading');
    const contentEl = document.getElementById('interventionContent');
    const errorEl = document.getElementById('interventionError');
    const errorMessageEl = document.getElementById('interventionErrorMessage');
    const successEl = document.getElementById('interventionSuccess');
    const successMessageEl = document.getElementById('interventionSuccessMessage');
    const sendBtn = document.getElementById('sendInterventionBtn');
    const sendBtnSpinner = document.getElementById('sendBtnSpinner');
    const sendBtnIcon = document.getElementById('sendBtnIcon');
    const sendBtnText = document.getElementById('sendBtnText');
    const modalCloseBtnText = document.getElementById('modalCloseBtnText');
    const interventionModeBadge = document.getElementById('interventionModeBadge');
    const interventionModalLabel = document.getElementById('interventionModalLabel');
    const interventionModalSubtitle = document.getElementById('interventionModalSubtitle');

    // Section containers
    const singleStudentSection = document.getElementById('singleStudentSection');
    const bulkStudentSection = document.getElementById('bulkStudentSection');
    const bulkStudentsListContainer = document.getElementById('bulkStudentsListContainer');
    const bulkSummaryText = document.getElementById('bulkSummaryText');
    const bulkEmptyState = document.getElementById('bulkEmptyState');

    // Results Summary Elements
    const resultsSummaryContainer = document.getElementById('interventionResultsSummary');
    const resultsSummaryIcon = document.getElementById('resultsSummaryIcon');
    const resultsSummaryTitle = document.getElementById('resultsSummaryTitle');
    const resultsSummarySubtitle = document.getElementById('resultsSummarySubtitle');
    const resultsSummaryStats = document.getElementById('resultsSummaryStats');
    const resultsDeliveredContainer = document.getElementById('resultsDeliveredContainer');
    const resultsDeliveredList = document.getElementById('resultsDeliveredList');
    const resultsSkippedContainer = document.getElementById('resultsSkippedContainer');
    const resultsSkippedList = document.getElementById('resultsSkippedList');

    // Helper: Escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Single student fields
    const modalStudentName = document.getElementById('modalStudentName');
    const modalStudentNumber = document.getElementById('modalStudentNumber');
    const modalClassInfo = document.getElementById('modalClassInfo');
    const modalSubjectInfo = document.getElementById('modalSubjectInfo');
    const modalGuardianName = document.getElementById('modalGuardianName');
    const modalGuardianEmail = document.getElementById('modalGuardianEmail');
    const missingEmailWarning = document.getElementById('missingEmailWarning');
    const modalConditionBadge = document.getElementById('modalConditionBadge');
    const modalGradeValue = document.getElementById('modalGradeValue');
    const modalAttendanceValue = document.getElementById('modalAttendanceValue');
    const gradeMetricCol = document.getElementById('gradeMetricCol');
    const attendanceMetricCol = document.getElementById('attendanceMetricCol');
    const modalActiveIndicatorsList = document.getElementById('modalActiveIndicatorsList');

    // Single Preview fields
    const previewToRecipient = document.getElementById('previewToRecipient');
    const previewSubject = document.getElementById('previewSubject');
    const previewSalutation = document.getElementById('previewSalutation');
    const previewMessageBody = document.getElementById('previewMessageBody');
    const previewTeacherNoteCallout = document.getElementById('previewTeacherNoteCallout');
    const previewTeacherNoteText = document.getElementById('previewTeacherNoteText');
    const previewTeacherNoteLabel = document.getElementById('previewTeacherNoteLabel');
    const previewClosingBlock = document.getElementById('previewClosingBlock');
    const previewNoticeBanner = document.getElementById('previewNoticeBanner');
    const previewHeaderBadge = document.getElementById('previewHeaderBadge');
    const singleTeacherNote = document.getElementById('singleTeacherNote');

    // Language radio buttons
    const langRadios = document.querySelectorAll('input[name="modalMessageLang"]');

    // State
    let currentInterventionData = null;
    let currentInterventionStudents = [];
    let isBulkMode = false;
    let selectedLanguage = 'en';

    // Live update of single student teacher note
    if (singleTeacherNote) {
        singleTeacherNote.addEventListener('input', function () {
            const val = this.value.trim();
            if (val.length > 0) {
                previewTeacherNoteText.textContent = val;
                previewTeacherNoteCallout.classList.remove('d-none');
            } else {
                previewTeacherNoteCallout.classList.add('d-none');
            }
        });
    }

    // Language switch handler (updates both single & bulk modes)
    langRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.checked) {
                selectedLanguage = this.value;
                if (!isBulkMode && currentInterventionData) {
                    renderLanguageContent(selectedLanguage);
                } else if (isBulkMode && currentInterventionStudents.length > 0) {
                    renderBulkLanguageContent(selectedLanguage);
                }
            }
        });
    });

    /**
     * Open Modal and Load Intervention Data
     * Supports single enrollmentId or an array of enrollmentIds (bulk)
     */
    window.openInterventionModal = function (enrollmentIds, forceBulk = false) {
        // Reset states
        errorEl.classList.add('d-none');
        successEl.classList.add('d-none');
        contentEl.classList.add('d-none');
        if (resultsSummaryContainer) resultsSummaryContainer.classList.add('d-none');
        loadingEl.classList.remove('d-none');
        sendBtn.classList.remove('d-none');
        sendBtn.disabled = true;
        sendBtnSpinner.classList.add('d-none');
        sendBtnIcon.classList.remove('d-none');
        modalCloseBtnText.textContent = 'Close';
        if (singleTeacherNote) singleTeacherNote.value = '';
        if (previewTeacherNoteCallout) previewTeacherNoteCallout.classList.add('d-none');

        // Reset language to English default
        selectedLanguage = 'en';
        const defaultRadio = document.getElementById('langOptionEn');
        if (defaultRadio) defaultRadio.checked = true;

        const idsArray = Array.isArray(enrollmentIds) ? enrollmentIds : [enrollmentIds];
        isBulkMode = forceBulk || idsArray.length > 1;

        // Build query string
        const queryParams = idsArray.map(id => `enrollment_ids[]=${encodeURIComponent(id)}`).join('&');

        bsModal.show();

        fetch(`{{ route('teacher.grading-system.at-risk.intervention.prepare') }}?${queryParams}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Could not retrieve student risk intervention data.');
            }
            return response.json();
        })
        .then(data => {
            loadingEl.classList.add('d-none');
            if (!data.success || !data.students || data.students.length === 0) {
                errorMessageEl.textContent = data.message || 'No intervention data available.';
                errorEl.classList.remove('d-none');
                return;
            }

            if (!isBulkMode && data.students.length === 1) {
                // SINGLE STUDENT MODE
                currentInterventionData = data.students[0];
                currentInterventionStudents = [currentInterventionData];
                setupSingleStudentMode(currentInterventionData);
            } else {
                // BULK REVIEW MODE
                isBulkMode = true;
                currentInterventionStudents = data.students;
                currentInterventionData = null;
                setupBulkReviewMode(currentInterventionStudents);
            }

            contentEl.classList.remove('d-none');
        })
        .catch(err => {
            loadingEl.classList.add('d-none');
            errorMessageEl.textContent = err.message || 'An unexpected error occurred while preparing the intervention.';
            errorEl.classList.remove('d-none');
        });
    };

    /**
     * Setup Single Student Mode
     */
    function setupSingleStudentMode(student) {
        singleStudentSection.classList.remove('d-none');
        bulkStudentSection.classList.add('d-none');

        interventionModeBadge.textContent = 'Single Student';
        interventionModeBadge.className = 'badge bg-light text-dark';
        interventionModalLabel.textContent = 'At-Risk Student Parent Communication';
        interventionModalSubtitle.textContent = 'Review actual risk indicators and preview the student-specific intervention notice before dispatch.';
        sendBtnText.textContent = 'Send Intervention Email';
        modalCloseBtnText.textContent = 'Close';

        populateSingleStudent(student);
    }

    /**
     * Populate Single Student UI & Email Preview
     */
    function populateSingleStudent(student) {
        modalStudentName.textContent = student.student_name;
        modalStudentNumber.textContent = student.student_number;
        modalClassInfo.textContent = `Grade ${student.grade_level} - ${student.section_name}`;
        modalSubjectInfo.textContent = student.subject_name;

        modalGuardianName.textContent = student.guardian_name;
        modalGuardianEmail.textContent = student.guardian_email || 'No email provided';

        // Check guardian email status
        if (student.has_guardian_email) {
            missingEmailWarning.classList.add('d-none');
            sendBtn.disabled = false;
        } else {
            missingEmailWarning.classList.remove('d-none');
            sendBtn.disabled = true;
        }

        // Metrics
        if (student.current_grade !== null) {
            modalGradeValue.textContent = `${student.current_grade}%`;
            gradeMetricCol.classList.remove('d-none');
        } else {
            gradeMetricCol.classList.add('d-none');
        }

        if (student.attendance_rate !== null) {
            modalAttendanceValue.textContent = `${student.attendance_rate}%`;
            attendanceMetricCol.classList.remove('d-none');
        } else {
            attendanceMetricCol.classList.add('d-none');
        }

        // Active indicators
        modalActiveIndicatorsList.innerHTML = '';
        if (student.active_indicators && student.active_indicators.length > 0) {
            student.active_indicators.forEach(ind => {
                const span = document.createElement('span');
                span.className = 'badge bg-light text-dark border';
                span.style.fontSize = '0.72rem';
                span.textContent = ind;
                modalActiveIndicatorsList.appendChild(span);
            });
        }

        // To Recipient
        previewToRecipient.textContent = student.has_guardian_email 
            ? `${student.guardian_name} <${student.guardian_email}>`
            : `${student.guardian_name} (No email on file)`;

        renderLanguageContent(selectedLanguage);
    }

    /**
     * Render Single Student Preview and Condition Badge
     */
    function renderLanguageContent(lang) {
        if (!currentInterventionData) return;

        const langData = (currentInterventionData.languages && currentInterventionData.languages[lang])
            ? currentInterventionData.languages[lang]
            : {
                subject: currentInterventionData.subject,
                message_body: currentInterventionData.message_body,
                salutation: currentInterventionData.salutation,
                condition_label: currentInterventionData.risk_condition_label,
                closing: `Warm regards,\n${currentInterventionData.teacher_name}\nSubject Teacher / Faculty\nConcepcion Integrated School`,
                teacher_note_label: `Note from Teacher (${currentInterventionData.teacher_name}):`,
                status_label: `Status: ${currentInterventionData.risk_condition_label}`
            };

        // Condition badge
        modalConditionBadge.textContent = langData.condition_label;
        if (currentInterventionData.risk_condition === 'dual') {
            modalConditionBadge.className = 'badge bg-danger text-white px-2 py-1';
        } else if (currentInterventionData.risk_condition === 'attendance') {
            modalConditionBadge.className = 'badge bg-warning-subtle text-dark border border-warning px-2 py-1';
        } else {
            modalConditionBadge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1';
        }

        previewSubject.textContent = langData.subject;
        previewSalutation.textContent = langData.salutation;
        previewMessageBody.textContent = langData.message_body;
        previewTeacherNoteLabel.textContent = langData.teacher_note_label;
        previewClosingBlock.textContent = langData.closing;
        previewNoticeBanner.textContent = lang === 'tl' ? 'Tagalog Template' : 'English Template';
        previewHeaderBadge.textContent = lang === 'tl' ? 'Pansubaybay sa Pag-aaral' : 'Academic Monitoring';
    }

    /**
     * Setup Bulk Review Mode
     */
    function setupBulkReviewMode(students) {
        singleStudentSection.classList.add('d-none');
        bulkStudentSection.classList.remove('d-none');

        updateBulkHeaderAndCounts();
        renderBulkStudentCards(students);
    }

    /**
     * Update bulk headers, badges, and send button text
     */
    function updateBulkHeaderAndCounts() {
        const count = currentInterventionStudents.length;
        const withEmailCount = currentInterventionStudents.filter(s => s.has_guardian_email).length;

        interventionModeBadge.innerHTML = `<i class="fa-solid fa-users me-1"></i> Bulk Review (${count} Selected)`;
        interventionModeBadge.className = 'badge bg-primary text-white';
        interventionModalLabel.textContent = 'Batch At-Risk Parent Communication';
        interventionModalSubtitle.textContent = 'Review each student independently. Each message is tailored to that student\'s specific condition and parent.';
        modalCloseBtnText.textContent = 'Cancel';

        if (count === 0) {
            bulkSummaryText.textContent = '0 students in intervention queue';
            bulkEmptyState.classList.remove('d-none');
            bulkStudentsListContainer.classList.add('d-none');
            sendBtn.disabled = true;
            sendBtnText.textContent = 'Send Interventions (0)';
        } else {
            bulkEmptyState.classList.add('d-none');
            bulkStudentsListContainer.classList.remove('d-none');

            if (withEmailCount === 0) {
                bulkSummaryText.innerHTML = `${count} ${count === 1 ? 'student' : 'students'} in queue &middot; <span class="badge bg-warning text-dark ms-1">No parent emails on file</span>`;
                sendBtn.disabled = true;
                sendBtnText.textContent = 'No Emails on File';
            } else if (withEmailCount < count) {
                bulkSummaryText.innerHTML = `${count} students in queue &middot; <strong class="text-primary">${withEmailCount} ready to send</strong> (${count - withEmailCount} missing parent email)`;
                sendBtn.disabled = false;
                sendBtnText.textContent = `Send Interventions (${withEmailCount} ready)`;
            } else {
                bulkSummaryText.textContent = `${count} ${count === 1 ? 'student' : 'students'} in intervention queue (all parent emails verified)`;
                sendBtn.disabled = false;
                sendBtnText.textContent = `Send All Interventions (${count})`;
            }
        }
    }

    /**
     * Render all student cards in Bulk Review Mode
     */
    function renderBulkStudentCards(students) {
        bulkStudentsListContainer.innerHTML = '';

        students.forEach((student, index) => {
            const langData = (student.languages && student.languages[selectedLanguage])
                ? student.languages[selectedLanguage]
                : {
                    subject: student.subject,
                    message_body: student.message_body,
                    salutation: student.salutation,
                    condition_label: student.risk_condition_label,
                    closing: `Warm regards,\n${student.teacher_name}\nSubject Teacher / Faculty\nConcepcion Integrated School`,
                    teacher_note_label: `Note from Teacher (${student.teacher_name}):`,
                    status_label: `Status: ${student.risk_condition_label}`
                };

            let badgeClass = 'badge bg-danger-subtle text-danger border border-danger-subtle';
            if (student.risk_condition === 'dual') {
                badgeClass = 'badge bg-danger text-white';
            } else if (student.risk_condition === 'attendance') {
                badgeClass = 'badge bg-warning-subtle text-dark border border-warning';
            }

            const activeTags = (student.active_indicators || []).map(ind => 
                `<span class="badge bg-light text-dark border" style="font-size: 0.68rem;">${ind}</span>`
            ).join(' ');

            const card = document.createElement('div');
            card.className = 'card border-0 shadow-sm bulk-student-card';
            card.id = `bulkCard_${student.enrollment_id}`;
            card.style.borderRadius = '8px';
            card.style.overflow = 'hidden';

            card.innerHTML = `
                <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.68rem;">#${index + 1}</span>
                        <span class="fw-bold text-dark" style="font-size: 0.85rem;">${student.student_name}</span>
                        <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.68rem;">${student.student_number}</span>
                        <span class="text-muted small" style="font-size: 0.74rem;">Grade ${student.grade_level} - ${student.section_name} &middot; ${student.subject_name}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="${badgeClass}" id="bulkConditionBadge_${student.enrollment_id}" style="font-size: 0.72rem;">
                            ${langData.condition_label}
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 remove-bulk-student-btn" data-id="${student.enrollment_id}" style="font-size: 0.72rem;" title="Remove from batch">
                            <i class="fa-solid fa-xmark me-1"></i> Remove
                        </button>
                    </div>
                </div>

                <div class="card-body p-3" style="background-color: #fafbfc;">
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-white h-100">
                                <div class="text-muted small" style="font-size: 0.68rem; text-transform: uppercase; font-weight: 600;">At-Risk Indicators</div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    ${student.current_grade !== null ? `<span class="badge bg-light text-dark border" style="font-size: 0.72rem;">Grade: <strong>${student.current_grade}%</strong></span>` : ''}
                                    ${student.attendance_rate !== null ? `<span class="badge bg-light text-dark border" style="font-size: 0.72rem;">Attendance: <strong>${student.attendance_rate}%</strong></span>` : ''}
                                </div>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    ${activeTags}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 border rounded bg-white h-100">
                                <div class="text-muted small" style="font-size: 0.68rem; text-transform: uppercase; font-weight: 600;">Recipient (Parent/Guardian)</div>
                                <div class="fw-semibold text-dark small mt-1">${student.guardian_name}</div>
                                <div class="text-secondary small font-monospace" style="font-size: 0.75rem;">${student.guardian_email || 'No email on file'}</div>
                                ${!student.has_guardian_email ? `<span class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;">⚠️ No parent email - message cannot be delivered</span>` : ''}
                            </div>
                        </div>
                    </div>

                    <div class="border rounded bg-white p-3 mb-2" style="font-size: 0.8rem; line-height: 1.5;">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-2">
                            <span class="fw-bold text-primary" style="font-size: 0.75rem;">
                                <i class="fa-regular fa-envelope me-1"></i> Parent Email Preview
                            </span>
                            <span class="text-dark fw-semibold" style="font-size: 0.75rem;" id="bulkPreviewSubject_${student.enrollment_id}">
                                ${langData.subject}
                            </span>
                        </div>
                        <div class="p-2 rounded bg-light border" style="font-size: 0.78rem; line-height: 1.5; color: #334155;">
                            <p class="mb-1 fw-semibold" id="bulkPreviewSalutation_${student.enrollment_id}">
                                ${langData.salutation},
                            </p>
                            <div id="bulkPreviewMessageBody_${student.enrollment_id}" style="white-space: pre-line;" class="mb-2">
                                ${langData.message_body}
                            </div>
                            <div id="bulkTeacherNoteCallout_${student.enrollment_id}" class="p-2 mb-2 border-start border-3 border-primary bg-primary-subtle d-none" style="border-radius: 4px; font-size: 0.75rem;">
                                <div class="fw-bold text-primary mb-1" id="bulkTeacherNoteLabel_${student.enrollment_id}" style="font-size: 0.68rem; text-transform: uppercase;">
                                    ${langData.teacher_note_label}
                                </div>
                                <span id="bulkTeacherNoteText_${student.enrollment_id}" class="text-dark"></span>
                            </div>
                            <div class="text-muted" style="font-size: 0.72rem; white-space: pre-line;" id="bulkPreviewClosing_${student.enrollment_id}">
                                ${langData.closing}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label small fw-semibold text-secondary mb-1" style="font-size: 0.74rem;">
                            <i class="fa-solid fa-pen me-1"></i> Optional Note for ${student.student_name}'s Guardian:
                        </label>
                        <textarea class="form-control form-control-sm bulk-student-note" data-enrollment-id="${student.enrollment_id}" rows="1" maxlength="1000" placeholder="Optional personal note to include in this student's email..."></textarea>
                    </div>
                </div>
            `;

            bulkStudentsListContainer.appendChild(card);
        });

        // Wire live note sync for bulk cards
        document.querySelectorAll('.bulk-student-note').forEach(txt => {
            txt.addEventListener('input', function () {
                const eid = this.getAttribute('data-enrollment-id');
                const callout = document.getElementById(`bulkTeacherNoteCallout_${eid}`);
                const noteText = document.getElementById(`bulkTeacherNoteText_${eid}`);
                const val = this.value.trim();

                if (val.length > 0) {
                    noteText.textContent = val;
                    callout.classList.remove('d-none');
                } else {
                    callout.classList.add('d-none');
                }
            });
        });

        // Wire student card removal
        document.querySelectorAll('.remove-bulk-student-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const eid = this.getAttribute('data-id');
                removeStudentFromBatch(eid);
            });
        });
    }

    /**
     * Remove a single student from the active batch queue
     */
    function removeStudentFromBatch(enrollmentId) {
        currentInterventionStudents = currentInterventionStudents.filter(s => s.enrollment_id != enrollmentId);

        const card = document.getElementById(`bulkCard_${enrollmentId}`);
        if (card) {
            card.remove();
        }

        // Notify table to uncheck this student's checkbox
        window.dispatchEvent(new CustomEvent('intervention-student-removed', {
            detail: { enrollment_id: enrollmentId }
        }));

        updateBulkHeaderAndCounts();
    }

    /**
     * Update bulk review preview texts when language changes
     */
    function renderBulkLanguageContent(lang) {
        currentInterventionStudents.forEach(student => {
            const langData = (student.languages && student.languages[lang])
                ? student.languages[lang]
                : {
                    subject: student.subject,
                    message_body: student.message_body,
                    salutation: student.salutation,
                    condition_label: student.risk_condition_label,
                    closing: `Warm regards,\n${student.teacher_name}\nSubject Teacher / Faculty\nConcepcion Integrated School`,
                    teacher_note_label: `Note from Teacher (${student.teacher_name}):`,
                    status_label: `Status: ${student.risk_condition_label}`
                };

            const condBadge = document.getElementById(`bulkConditionBadge_${student.enrollment_id}`);
            const subj = document.getElementById(`bulkPreviewSubject_${student.enrollment_id}`);
            const salut = document.getElementById(`bulkPreviewSalutation_${student.enrollment_id}`);
            const msg = document.getElementById(`bulkPreviewMessageBody_${student.enrollment_id}`);
            const noteLbl = document.getElementById(`bulkTeacherNoteLabel_${student.enrollment_id}`);
            const closing = document.getElementById(`bulkPreviewClosing_${student.enrollment_id}`);

            if (condBadge) condBadge.textContent = langData.condition_label;
            if (subj) subj.textContent = langData.subject;
            if (salut) salut.textContent = `${langData.salutation},`;
            if (msg) msg.textContent = langData.message_body;
            if (noteLbl) noteLbl.textContent = langData.teacher_note_label;
            if (closing) closing.textContent = langData.closing;
        });
    }

    /**
     * Send Intervention Email(s)
     */
    sendBtn.addEventListener('click', function () {
        const studentsToSend = isBulkMode ? currentInterventionStudents : (currentInterventionData ? [currentInterventionData] : []);
        if (studentsToSend.length === 0) return;

        sendBtn.disabled = true;
        sendBtnSpinner.classList.remove('d-none');
        sendBtnIcon.classList.add('d-none');
        sendBtnText.textContent = isBulkMode ? 'Sending Batch...' : 'Sending...';
        errorEl.classList.add('d-none');

        let payloadInterventions = [];

        if (isBulkMode) {
            payloadInterventions = studentsToSend.map(s => {
                const noteEl = document.querySelector(`.bulk-student-note[data-enrollment-id="${s.enrollment_id}"]`);
                return {
                    enrollment_id: s.enrollment_id,
                    teacher_note: noteEl && noteEl.value.trim().length > 0 ? noteEl.value.trim() : null,
                    language: selectedLanguage
                };
            });
        } else {
            payloadInterventions = [
                {
                    enrollment_id: currentInterventionData.enrollment_id,
                    teacher_note: singleTeacherNote ? singleTeacherNote.value.trim() : null,
                    language: selectedLanguage
                }
            ];
        }

        fetch(`{{ route('teacher.grading-system.at-risk.intervention.send') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ interventions: payloadInterventions })
        })
        .then(response => {
            if (!response.ok && response.status !== 422) {
                throw new Error('A server issue occurred during intervention dispatch. Please try again.');
            }
            return response.json();
        })
        .then(res => {
            sendBtnSpinner.classList.add('d-none');
            sendBtnIcon.classList.remove('d-none');
            sendBtnText.textContent = isBulkMode ? `Send All Interventions (${studentsToSend.length})` : 'Send Intervention Email';

            if (res.success && res.failed_count === 0 && !isBulkMode) {
                // Single student full success
                successMessageEl.textContent = res.message || 'Intervention email sent successfully.';
                successEl.classList.remove('d-none');
                sendBtn.disabled = true;
                sendBtnText.textContent = 'Sent Successfully';
                
                setTimeout(() => {
                    window.location.reload();
                }, 1400);
            } else if (res.sent_count > 0 || res.failed_count > 0) {
                // Batch dispatch or single student with skipped/partial status
                contentEl.classList.add('d-none');
                errorEl.classList.add('d-none');
                successEl.classList.add('d-none');
                loadingEl.classList.add('d-none');

                if (resultsSummaryContainer) {
                    resultsSummaryContainer.classList.remove('d-none');
                    resultsSummarySubtitle.textContent = res.message;

                    if (res.failed_count === 0) {
                        // 100% batch success
                        resultsSummaryIcon.className = 'd-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success flex-shrink-0';
                        resultsSummaryIcon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
                        resultsSummaryTitle.textContent = 'Intervention Emails Dispatched Successfully';
                        resultsSummaryStats.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 font-monospace" style="font-size: 0.75rem;"><i class="fa-solid fa-check me-1"></i> ${res.sent_count} Delivered</span>`;
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 2500);
                    } else if (res.sent_count > 0) {
                        // Partial success (some sent, some skipped)
                        resultsSummaryIcon.className = 'd-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning-emphasis flex-shrink-0';
                        resultsSummaryIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
                        resultsSummaryTitle.textContent = 'Batch Processed with Skipped Students';
                        resultsSummaryStats.innerHTML = `
                            <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 font-monospace" style="font-size: 0.75rem;"><i class="fa-solid fa-check me-1"></i> ${res.sent_count} Delivered</span>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-1 px-2 font-monospace" style="font-size: 0.75rem;"><i class="fa-solid fa-exclamation me-1"></i> ${res.failed_count} Skipped</span>
                        `;
                    } else {
                        // Zero sent (e.g. all selected had missing parent emails)
                        resultsSummaryIcon.className = 'd-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger flex-shrink-0';
                        resultsSummaryIcon.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
                        resultsSummaryTitle.textContent = 'Intervention Notices Could Not Be Sent';
                        resultsSummaryStats.innerHTML = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2 font-monospace" style="font-size: 0.75rem;"><i class="fa-solid fa-xmark me-1"></i> ${res.failed_count} Skipped</span>`;
                    }

                    // Populate delivered list
                    if (res.sent_students && res.sent_students.length > 0) {
                        resultsDeliveredContainer.classList.remove('d-none');
                        resultsDeliveredList.innerHTML = res.sent_students.map(s => `
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                <div>
                                    <strong class="text-dark">${escapeHtml(s.student_name)}</strong>
                                    <div class="text-muted small" style="font-size: 0.72rem;">To: <span class="font-monospace">${escapeHtml(s.guardian_email)}</span></div>
                                </div>
                                <span class="badge bg-light text-secondary border" style="font-size: 0.68rem;">${escapeHtml(s.condition)}</span>
                            </div>
                        `).join('');
                    } else {
                        resultsDeliveredContainer.classList.add('d-none');
                    }

                    // Populate skipped list
                    if (res.skipped_students && res.skipped_students.length > 0) {
                        resultsSkippedContainer.classList.remove('d-none');
                        resultsSkippedList.innerHTML = res.skipped_students.map(s => `
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                <div>
                                    <strong class="text-dark">${escapeHtml(s.student_name)}</strong>
                                    <div class="text-danger small" style="font-size: 0.72rem;">${escapeHtml(s.reason)}</div>
                                </div>
                                <span class="badge bg-warning-subtle text-dark border border-warning" style="font-size: 0.68rem;">Action Required</span>
                            </div>
                        `).join('');
                    } else {
                        resultsSkippedContainer.classList.add('d-none');
                    }

                    sendBtn.classList.add('d-none');
                    modalCloseBtnText.textContent = 'Close';
                }
            } else {
                sendBtn.disabled = false;
                errorMessageEl.textContent = res.message || 'Could not send intervention.';
                errorEl.classList.remove('d-none');
            }
        })
        .catch(err => {
            sendBtn.disabled = false;
            sendBtnSpinner.classList.add('d-none');
            sendBtnIcon.classList.remove('d-none');
            sendBtnText.textContent = isBulkMode ? `Send All Interventions (${studentsToSend.length})` : 'Send Intervention Email';
            errorMessageEl.textContent = 'A temporary connection issue occurred while dispatching the intervention notice. Please try again.';
            errorEl.classList.remove('d-none');
        });
    });

    // Wire up buttons with data-intervene-enrollment attribute
    document.querySelectorAll('[data-intervene-enrollment]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.getAttribute('data-intervene-enrollment');
            if (id) {
                window.openInterventionModal(id, false);
            }
        });
    });
});
</script>
