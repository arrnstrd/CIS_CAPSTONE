{{-- Intervention Modal (Teacher POV) --}}
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
                    <p class="text-white-50 small mb-0" style="font-size: 0.78rem;">
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
                    <p class="text-muted small mb-0">Analyzing risk indicators and preparing preview...</p>
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
                    
                    {{-- SINGLE STUDENT VIEW --}}
                    <div id="singleStudentSection">
                        
                        {{-- 1. Student Summary & Contact Card --}}
                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px;">
                            <div class="card-body p-3">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-7">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <h6 class="fw-bold mb-0 text-dark" id="modalStudentName">—</h6>
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.7rem;" id="modalStudentNumber">—</span>
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

                        {{-- 2. At-Risk Indicators Card --}}
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
                                    {{-- Active Indicator badges dynamically inserted here --}}
                                </div>
                            </div>
                        </div>

                        {{-- 3. Language Selector Option --}}
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
                                            Select English or natural Tagalog for the guardian
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

                        {{-- 4. Realistic Email Preview Box --}}
                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px;">
                            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                                <span class="fw-bold small text-dark" style="font-size: 0.78rem;">
                                    <i class="fa-regular fa-envelope me-1 text-primary"></i> Parent Email Preview
                                </span>
                                <span id="previewNoticeBanner" class="badge bg-light text-muted border font-monospace" style="font-size: 0.68rem;">Live Template Render</span>
                            </div>
                            <div class="card-body p-3" style="background-color: #ffffff; border-radius: 0 0 8px 8px;">
                                {{-- Email Metadata Header --}}
                                <div class="border-bottom pb-2 mb-2" style="font-size: 0.78rem; line-height: 1.5;">
                                    <div><strong class="text-muted me-1">To:</strong> <span id="previewToRecipient" class="text-dark font-monospace">—</span></div>
                                    <div><strong class="text-muted me-1">Subject:</strong> <span id="previewSubject" class="text-dark fw-semibold">—</span></div>
                                </div>

                                {{-- Email Body Preview Container --}}
                                <div class="p-3 border rounded-2" style="background-color: #fafbfc; font-size: 0.82rem; line-height: 1.6; color: #334155;">
                                    <div class="border-bottom pb-2 mb-2 d-flex align-items-center justify-content-between">
                                        <span class="fw-bold text-primary" style="font-size: 0.8rem;">Concepcion Integrated School</span>
                                        <span id="previewHeaderBadge" class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Academic Monitoring</span>
                                    </div>
                                    
                                    <p class="mb-2"><span class="fw-semibold" id="previewSalutation">—</span>,</p>
                                    
                                    <div id="previewMessageBody" style="white-space: pre-line;" class="mb-3">
                                        —
                                    </div>

                                    {{-- Teacher Note Live Preview Callout --}}
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

                        {{-- 5. Optional Teacher Note Input --}}
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

                </div> {{-- END interventionContent --}}

            </div> {{-- END modal-body --}}

            {{-- Modal Footer --}}
            <div class="modal-footer py-2 px-4 bg-white border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">
                    Close
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

{{-- Inline Vanilla JS Controller for Intervention Modal --}}
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

    // Preview fields
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
    let selectedLanguage = 'en';

    // Live update of teacher note preview
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

    // Language switch handler
    langRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.checked) {
                selectedLanguage = this.value;
                if (currentInterventionData) {
                    renderLanguageContent(selectedLanguage);
                }
            }
        });
    });

    /**
     * Open Modal and Load Intervention Data for an Enrollment ID
     */
    window.openInterventionModal = function (enrollmentId) {
        // Reset states
        errorEl.classList.add('d-none');
        successEl.classList.add('d-none');
        contentEl.classList.add('d-none');
        loadingEl.classList.remove('d-none');
        sendBtn.disabled = true;
        sendBtnSpinner.classList.add('d-none');
        sendBtnIcon.classList.remove('d-none');
        sendBtnText.textContent = 'Send Intervention Email';
        if (singleTeacherNote) singleTeacherNote.value = '';
        if (previewTeacherNoteCallout) previewTeacherNoteCallout.classList.add('d-none');

        // Reset language to English default
        selectedLanguage = 'en';
        const defaultRadio = document.getElementById('langOptionEn');
        if (defaultRadio) defaultRadio.checked = true;

        bsModal.show();

        fetch(`{{ route('teacher.grading-system.at-risk.intervention.prepare') }}?enrollment_id=${encodeURIComponent(enrollmentId)}`, {
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

            currentInterventionData = data.students[0];
            populateSingleStudent(currentInterventionData);
            contentEl.classList.remove('d-none');
        })
        .catch(err => {
            loadingEl.classList.add('d-none');
            errorMessageEl.textContent = err.message || 'An unexpected error occurred while preparing the intervention.';
            errorEl.classList.remove('d-none');
        });
    };

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

        // Render preview content based on active language
        renderLanguageContent(selectedLanguage);
    }

    /**
     * Render Preview and Condition Badge according to selected language
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

        // Email preview fields
        previewSubject.textContent = langData.subject;
        previewSalutation.textContent = langData.salutation;
        previewMessageBody.textContent = langData.message_body;
        previewTeacherNoteLabel.textContent = langData.teacher_note_label;
        previewClosingBlock.textContent = langData.closing;
        previewNoticeBanner.textContent = lang === 'tl' ? 'Tagalog Template' : 'English Template';
        previewHeaderBadge.textContent = lang === 'tl' ? 'Pansubaybay sa Pag-aaral' : 'Academic Monitoring';
    }

    /**
     * Send Intervention Email
     */
    sendBtn.addEventListener('click', function () {
        if (!currentInterventionData || !currentInterventionData.enrollment_id) return;

        sendBtn.disabled = true;
        sendBtnSpinner.classList.remove('d-none');
        sendBtnIcon.classList.add('d-none');
        sendBtnText.textContent = 'Sending...';
        errorEl.classList.add('d-none');

        const payload = {
            interventions: [
                {
                    enrollment_id: currentInterventionData.enrollment_id,
                    teacher_note: singleTeacherNote ? singleTeacherNote.value.trim() : null,
                    language: selectedLanguage
                }
            ]
        };

        fetch(`{{ route('teacher.grading-system.at-risk.intervention.send') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(res => {
            sendBtnSpinner.classList.add('d-none');
            sendBtnIcon.classList.remove('d-none');
            sendBtnText.textContent = 'Send Intervention Email';

            if (res.success) {
                successMessageEl.textContent = res.message || 'Intervention email sent successfully.';
                successEl.classList.remove('d-none');
                sendBtn.disabled = true;
                
                // Refresh follow-up list on the page if function exists or reload after delay
                setTimeout(() => {
                    window.location.reload();
                }, 1400);
            } else {
                sendBtn.disabled = false;
                errorMessageEl.textContent = (res.errors && res.errors.length) ? res.errors.join(' ') : (res.message || 'Could not send intervention.');
                errorEl.classList.remove('d-none');
            }
        })
        .catch(err => {
            sendBtn.disabled = false;
            sendBtnSpinner.classList.add('d-none');
            sendBtnIcon.classList.remove('d-none');
            sendBtnText.textContent = 'Send Intervention Email';
            errorMessageEl.textContent = 'Network or server error while dispatching email. Please try again.';
            errorEl.classList.remove('d-none');
        });
    });

    // Wire up buttons with data-intervene-enrollment attribute
    document.querySelectorAll('[data-intervene-enrollment]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.getAttribute('data-intervene-enrollment');
            if (id) {
                window.openInterventionModal(id);
            }
        });
    });
});
</script>
