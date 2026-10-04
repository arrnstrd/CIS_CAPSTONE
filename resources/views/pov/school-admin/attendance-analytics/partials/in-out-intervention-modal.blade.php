{{-- ════════════════════════════════════════════════════════════════════════
     IN/OUT INTERVENTION MODAL (SCHOOL ADMIN)
     Modern, compact, situation-matched attendance intervention workflow
     ════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="inOutInterventionModal" tabindex="-1" aria-labelledby="inOutInterventionModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            
            {{-- Modal Header --}}
            <div class="modal-header py-3 px-4 text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded-circle" style="width: 34px; height: 34px;">
                        <i class="fas fa-paper-plane text-white" style="font-size: 0.95rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="inOutInterventionModalLabel" style="font-size: 1.05rem;">
                            School Admin IN/OUT Intervention
                        </h5>
                        <span class="small text-white-50" style="font-size: 0.72rem;">
                            Targeted attendance follow-up for Class Adviser &amp; Parent / Guardian
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4" style="background: #f8fafc; max-height: calc(85vh - 130px); overflow-y: auto;">
                
                {{-- Loading State --}}
                <div id="interventionLoadingState" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 2.2rem; height: 2.2rem;">
                        <span class="visually-hidden">Loading student records...</span>
                    </div>
                    <div class="mt-2 text-muted small fw-medium">Resolving student IN/OUT records &amp; contacts...</div>
                </div>

                {{-- Intervention Content --}}
                <div id="interventionFormContent" class="d-none">
                    
                    {{-- 1. Student & Concern Summary Card --}}
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px; background: #ffffff;">
                        <div class="card-body p-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-7 border-end-md">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold"
                                             style="width: 44px; height: 44px; background: #e0f2fe; font-size: 1.1rem;" id="intervStudentAvatar">
                                            ST
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 1rem;" id="intervStudentName">—</div>
                                            <div class="text-muted small" style="font-size: 0.75rem;">
                                                <span id="intervStudentNumber" class="font-monospace">—</span>
                                                <span class="mx-1">•</span>
                                                <span id="intervGradeSection" class="fw-semibold text-secondary">—</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5 ps-md-3">
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-muted small" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Concern Type:</span>
                                            <span id="intervConcernBadge" class="badge bg-warning text-dark fw-bold" style="font-size: 0.7rem;">Late Arrival</span>
                                        </div>
                                        <div id="intervPatternDesc" class="text-dark small fw-medium" style="font-size: 0.78rem; line-height: 1.3;">
                                            Consecutive pattern recorded.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Recorded Log Dates Chips --}}
                            <div class="mt-2 pt-2 border-top border-light">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="text-muted small fw-semibold" style="font-size: 0.7rem; text-transform: uppercase;">
                                        <i class="fas fa-calendar-alt text-primary me-1"></i> Recorded Logs / Evidence
                                    </span>
                                    <span class="text-muted small" id="intervLogCountBadge" style="font-size: 0.68rem;">—</span>
                                </div>
                                <div id="intervLogChips" class="d-flex flex-wrap gap-1" style="max-height: 60px; overflow-y: auto;">
                                    {{-- Dynamically populated chips --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Recipient Selection Section --}}
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px; background: #ffffff;">
                        <div class="card-body p-3">
                            <label class="form-label text-dark fw-bold mb-2 d-flex align-items-center gap-1" style="font-size: 0.82rem;">
                                <i class="fas fa-users text-primary"></i> Target Recipients <span class="text-danger">*</span>
                                <span class="badge bg-light text-muted fw-normal ms-auto" style="font-size: 0.68rem;">Select one or both</span>
                            </label>

                            <div class="row g-2">
                                {{-- Class Adviser Recipient --}}
                                <div class="col-md-6">
                                    <label class="p-2 border rounded-3 d-flex align-items-start gap-2 w-100 cursor-pointer h-100"
                                           id="advisorRecipientBox" style="background: #fafafa; transition: all 0.2s ease;">
                                        <input class="form-check-input mt-1 flex-shrink-0" type="checkbox" id="recipientAdvisorCheck" checked
                                               onchange="toggleRecipientOption('advisor')">
                                        <div class="small flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <strong class="text-dark" style="font-size: 0.8rem;">Class Adviser</strong>
                                                <span id="advisorEmailStatusBadge" class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.62rem;">Ready</span>
                                            </div>
                                            <div class="text-secondary fw-semibold" style="font-size: 0.75rem;" id="intervAdvisorName">—</div>
                                            <div class="text-muted" style="font-size: 0.7rem; word-break: break-all;" id="intervAdvisorEmail">—</div>
                                        </div>
                                    </label>
                                </div>

                                {{-- Parent / Guardian Recipient --}}
                                <div class="col-md-6">
                                    <label class="p-2 border rounded-3 d-flex align-items-start gap-2 w-100 cursor-pointer h-100"
                                           id="guardianRecipientBox" style="background: #fafafa; transition: all 0.2s ease;">
                                        <input class="form-check-input mt-1 flex-shrink-0" type="checkbox" id="recipientParentCheck" checked
                                               onchange="toggleRecipientOption('parent')">
                                        <div class="small flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <strong class="text-dark" style="font-size: 0.8rem;">Parent / Guardian</strong>
                                                <span id="guardianEmailStatusBadge" class="badge bg-success bg-opacity-10 text-success" style="font-size: 0.62rem;">Ready</span>
                                            </div>
                                            <div class="text-secondary fw-semibold" style="font-size: 0.75rem;" id="intervGuardianName">—</div>
                                            <div class="text-muted" style="font-size: 0.7rem; word-break: break-all;" id="intervGuardianEmail">—</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Language Selection for Parent Message --}}
                            <div class="mt-2 pt-2 border-top border-light d-flex align-items-center justify-content-between" id="parentLanguageRow">
                                <span class="text-muted small" style="font-size: 0.72rem;">
                                    <i class="fas fa-language text-secondary me-1"></i> Parent / Guardian Notice Language:
                                </span>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Parent Language Toggle">
                                    <input type="radio" class="btn-check" name="intervParentLang" id="intervLangEn" value="en" checked onchange="switchParentLanguage('en')">
                                    <label class="btn btn-outline-primary py-0 px-2" for="intervLangEn" style="font-size: 0.72rem;">English</label>

                                    <input type="radio" class="btn-check" name="intervParentLang" id="intervLangTl" value="tl" onchange="switchParentLanguage('tl')">
                                    <label class="btn btn-outline-primary py-0 px-2" for="intervLangTl" style="font-size: 0.72rem;">Filipino / Tagalog</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Optional Admin Remarks / Custom Directives --}}
                    <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px; background: #ffffff;">
                        <div class="card-body p-3">
                            <label for="intervAdminNotes" class="form-label text-dark fw-bold mb-1 d-flex align-items-center gap-1" style="font-size: 0.82rem;">
                                <i class="fas fa-comment-dots text-primary"></i> Administrative Remarks / Directives
                                <span class="badge bg-light text-muted fw-normal ms-1" style="font-size: 0.65rem;">Optional</span>
                            </label>
                            <textarea id="intervAdminNotes" class="form-control" rows="2"
                                      placeholder="Add specific instructions, e.g., 'Student informed admin about transport strike on Tuesday' or 'Coordinate with Guidance Office'..."
                                      style="font-size: 0.78rem; border-color: #cbd5e1;" oninput="updateLivePreviewNotes()"></textarea>
                            <span class="text-muted" style="font-size: 0.68rem;">Included cleanly in the dispatched memo/notice for contextual clarity.</span>
                        </div>
                    </div>

                    {{-- 4. Live Message Preview Box with Separate Tabs --}}
                    <div class="card border-0 shadow-sm" style="border-radius: 10px; background: #ffffff;">
                        <div class="card-header bg-white border-bottom p-2 px-3 d-flex align-items-center justify-content-between">
                            <div class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 0.82rem;">
                                <i class="fas fa-eye text-primary"></i> Live Message Preview
                            </div>
                            
                            {{-- Preview Tab Navs --}}
                            <ul class="nav nav-pills nav-fill gap-1" id="intervPreviewTabs" role="tablist">
                                <li class="nav-item" role="presentation" id="tabAdvisorNavItem">
                                    <button class="nav-link active py-1 px-2" id="previewTabAdvisorBtn" data-bs-toggle="tab"
                                            data-bs-target="#previewTabAdvisor" type="button" role="tab" style="font-size: 0.72rem;">
                                        <i class="fas fa-chalkboard-teacher me-1"></i> Class Adviser Memo
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation" id="tabParentNavItem">
                                    <button class="nav-link py-1 px-2" id="previewTabParentBtn" data-bs-toggle="tab"
                                            data-bs-target="#previewTabParent" type="button" role="tab" style="font-size: 0.72rem;">
                                        <i class="fas fa-home me-1"></i> Parent / Guardian Notice
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body p-3">
                            <div class="tab-content" id="intervPreviewTabContent">
                                
                                {{-- Class Adviser Message Preview --}}
                                <div class="tab-pane fade show active" id="previewTabAdvisor" role="tabpanel">
                                    <div class="border rounded p-3" style="background: #f1f5f9; font-size: 0.76rem; font-family: monospace, sans-serif; white-space: pre-wrap; max-height: 220px; overflow-y: auto; color: #1e293b;" id="intervAdvisorBodyPreview">
                                        Loading adviser preview...
                                    </div>
                                    <div class="text-muted small mt-1" style="font-size: 0.68rem;">
                                        <i class="fas fa-lock text-secondary me-1"></i> Official internal memorandum template for school staff.
                                    </div>
                                </div>

                                {{-- Parent / Guardian Message Preview --}}
                                <div class="tab-pane fade" id="previewTabParent" role="tabpanel">
                                    <div class="border rounded p-3" style="background: #fffbeb; font-size: 0.76rem; font-family: sans-serif; white-space: pre-wrap; max-height: 220px; overflow-y: auto; color: #451a03;" id="intervParentBodyPreview">
                                        Loading parent preview...
                                    </div>
                                    <div class="text-muted small mt-1" style="font-size: 0.68rem;">
                                        <i class="fas fa-hand-holding-heart text-amber me-1"></i> Respectful and advisory tone for home collaboration.
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Feedback / Error Alert within modal --}}
                    <div id="intervModalAlert" class="alert alert-danger d-none mt-3 mb-0 py-2 px-3 small" role="alert" style="font-size: 0.78rem;"></div>

                </div>

            </div>

            {{-- Modal Footer Actions --}}
            <div class="modal-footer py-2 px-4 bg-light d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal" id="intervCancelBtn" style="font-size: 0.8rem;">
                    Cancel
                </button>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm d-flex align-items-center gap-2"
                            id="intervSubmitBtn" onclick="submitInOutIntervention()" style="font-size: 0.82rem;">
                        <span id="intervSubmitBtnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <i class="fas fa-paper-plane" id="intervSubmitBtnIcon"></i>
                        <span id="intervSubmitBtnText">Dispatch Intervention</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(function() {
    let currentInterventionData = null;
    let selectedParentLang = 'en';

    window.openInOutInterventionModal = function (enrollmentId, studentName, reasonType, count) {
        const modalEl = document.getElementById('inOutInterventionModal');
        if (!modalEl) return;

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        const loadingEl = document.getElementById('interventionLoadingState');
        const contentEl = document.getElementById('interventionFormContent');
        const alertEl   = document.getElementById('intervModalAlert');
        const submitBtn = document.getElementById('intervSubmitBtn');

        loadingEl.classList.remove('d-none');
        contentEl.classList.add('d-none');
        alertEl.classList.add('d-none');
        submitBtn.disabled = true;

        // Fetch prepared intervention metadata from controller
        const prepareUrl = `{{ route('school_admin.in-out-intervention.prepare') }}?enrollment_id=${enrollmentId}&reason_type=${encodeURIComponent(reasonType)}&count=${encodeURIComponent(count || 1)}`;

        fetch(prepareUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Could not load student intervention details.');
            return res.json();
        })
        .then(res => {
            if (!res.success) throw new Error(res.message || 'Intervention preparation failed.');
            currentInterventionData = res;
            populateInterventionModal(res);
            loadingEl.classList.add('d-none');
            contentEl.classList.remove('d-none');
            submitBtn.disabled = false;
        })
        .catch(err => {
            loadingEl.classList.add('d-none');
            alertEl.textContent = err.message || 'Error communicating with the intervention service.';
            alertEl.classList.remove('d-none');
            contentEl.classList.remove('d-none');
        });
    };

    function populateInterventionModal(data) {
        const student = data.student || {};
        const advisor = data.advisor || {};
        const guardian = data.guardian || {};
        const concern = data.concern || {};
        const templates = data.templates || {};

        // Student Box
        document.getElementById('intervStudentName').textContent = student.name || '—';
        document.getElementById('intervStudentNumber').textContent = student.student_number || '—';
        document.getElementById('intervGradeSection').textContent = student.grade_section || '—';
        
        const initials = (student.name || 'ST').split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
        document.getElementById('intervStudentAvatar').textContent = initials;

        // Concern & Pattern
        const badge = document.getElementById('intervConcernBadge');
        badge.textContent = concern.title || 'Attendance Concern';
        badge.className = `badge bg-${concern.badge_tone === 'danger' ? 'danger' : 'warning'} text-${concern.badge_tone === 'danger' ? 'white' : 'dark'} fw-bold`;
        
        document.getElementById('intervPatternDesc').textContent = concern.pattern_desc || 'Pattern observed.';

        // Chips
        const chipsContainer = document.getElementById('intervLogChips');
        chipsContainer.innerHTML = '';
        const dates = concern.log_dates || [];
        document.getElementById('intervLogCountBadge').textContent = `${dates.length} Logged Entries`;

        if (dates.length > 0) {
            dates.forEach(d => {
                const chip = document.createElement('span');
                chip.className = 'badge bg-secondary bg-opacity-10 text-dark fw-normal border';
                chip.style.fontSize = '0.68rem';
                chip.textContent = d;
                chipsContainer.appendChild(chip);
            });
        } else {
            chipsContainer.innerHTML = '<span class="text-muted small" style="font-size:0.68rem;">No recent log timestamps found.</span>';
        }

        // Adviser details & badge
        document.getElementById('intervAdvisorName').textContent = advisor.name || 'Unassigned Class Adviser';
        const advEmailEl = document.getElementById('intervAdvisorEmail');
        const advBadge   = document.getElementById('advisorEmailStatusBadge');
        const advCheck   = document.getElementById('recipientAdvisorCheck');

        if (advisor.has_email) {
            advEmailEl.textContent = advisor.email;
            advBadge.textContent = 'Email Verified';
            advBadge.className = 'badge bg-success bg-opacity-10 text-success';
            advCheck.disabled = false;
            advCheck.checked = true;
        } else {
            advEmailEl.textContent = 'No email on file';
            advBadge.textContent = 'No Email Address';
            advBadge.className = 'badge bg-danger bg-opacity-10 text-danger';
            advCheck.checked = false;
        }

        // Guardian details & badge
        document.getElementById('intervGuardianName').textContent = `${guardian.name} (${guardian.relationship || 'Guardian'})`;
        const grdEmailEl = document.getElementById('intervGuardianEmail');
        const grdBadge   = document.getElementById('guardianEmailStatusBadge');
        const grdCheck   = document.getElementById('recipientParentCheck');

        if (guardian.has_email) {
            grdEmailEl.textContent = guardian.email;
            grdBadge.textContent = 'Email Verified';
            grdBadge.className = 'badge bg-success bg-opacity-10 text-success';
            grdCheck.disabled = false;
            grdCheck.checked = true;
        } else {
            grdEmailEl.textContent = 'No email on file';
            grdBadge.textContent = 'No Email Address';
            grdBadge.className = 'badge bg-danger bg-opacity-10 text-danger';
            grdCheck.checked = false;
        }

        // Reset input fields
        document.getElementById('intervAdminNotes').value = '';
        selectedParentLang = 'en';
        document.getElementById('intervLangEn').checked = true;

        updatePreviews();
    }

    window.toggleRecipientOption = function(type) {
        const advCheck = document.getElementById('recipientAdvisorCheck');
        const grdCheck = document.getElementById('recipientParentCheck');
        const advTabNav = document.getElementById('tabAdvisorNavItem');
        const grdTabNav = document.getElementById('tabParentNavItem');
        const langRow   = document.getElementById('parentLanguageRow');

        if (type === 'advisor' && !advCheck.checked && !grdCheck.checked) {
            grdCheck.checked = true;
        }
        if (type === 'parent' && !advCheck.checked && !grdCheck.checked) {
            advCheck.checked = true;
        }

        advTabNav.style.display = advCheck.checked ? '' : 'none';
        grdTabNav.style.display = grdCheck.checked ? '' : 'none';
        langRow.style.display   = grdCheck.checked ? '' : 'none';

        // Ensure active tab corresponds to an enabled recipient
        if (advCheck.checked && !grdCheck.checked) {
            bootstrap.Tab.getOrCreateInstance(document.getElementById('previewTabAdvisorBtn')).show();
        } else if (!advCheck.checked && grdCheck.checked) {
            bootstrap.Tab.getOrCreateInstance(document.getElementById('previewTabParentBtn')).show();
        }
    };

    window.switchParentLanguage = function(lang) {
        selectedParentLang = lang;
        updatePreviews();
    };

    window.updateLivePreviewNotes = function() {
        updatePreviews();
    };

    function updatePreviews() {
        if (!currentInterventionData || !currentInterventionData.templates) return;

        const templates = currentInterventionData.templates;
        const notes = document.getElementById('intervAdminNotes').value.trim();

        // 1. Advisor Preview
        const advTpl = templates.advisor;
        if (advTpl) {
            let advBody = advTpl.message_body;
            if (notes) {
                advBody += `\n\n[ADMINISTRATIVE REMARK / DIRECTIVE]:\n${notes}`;
            }
            document.getElementById('intervAdvisorBodyPreview').textContent = advBody;
        }

        // 2. Parent Preview
        const prtTpl = selectedParentLang === 'tl' ? templates.parent_tl : templates.parent_en;
        if (prtTpl) {
            let prtBody = prtTpl.message_body;
            if (notes) {
                const noteTitle = selectedParentLang === 'tl' ? 'Pahayag mula sa School Administrator' : 'Note from School Administration';
                prtBody += `\n\n[${noteTitle}]:\n${notes}`;
            }
            document.getElementById('intervParentBodyPreview').textContent = prtBody;
        }
    }

    window.submitInOutIntervention = function() {
        if (!currentInterventionData) return;

        const advCheck = document.getElementById('recipientAdvisorCheck');
        const grdCheck = document.getElementById('recipientParentCheck');
        const alertEl  = document.getElementById('intervModalAlert');
        const submitBtn = document.getElementById('intervSubmitBtn');
        const spinner   = document.getElementById('intervSubmitBtnSpinner');
        const icon      = document.getElementById('intervSubmitBtnIcon');
        const text      = document.getElementById('intervSubmitBtnText');

        alertEl.classList.add('d-none');

        const recipients = [];
        if (advCheck.checked) recipients.push('advisor');
        if (grdCheck.checked) recipients.push('parent');

        if (recipients.length === 0) {
            alertEl.textContent = 'Please select at least one recipient (Class Adviser or Parent/Guardian).';
            alertEl.classList.remove('d-none');
            return;
        }

        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        icon.classList.add('d-none');
        text.textContent = 'Dispatching...';

        const payload = {
            enrollment_id: currentInterventionData.student.enrollment_id,
            recipients: recipients,
            reason_type: currentInterventionData.concern.reason_type,
            parent_language: selectedParentLang,
            admin_notes: document.getElementById('intervAdminNotes').value.trim(),
            custom_advisor_message: document.getElementById('intervAdvisorBodyPreview').textContent,
            custom_parent_message: document.getElementById('intervParentBodyPreview').textContent,
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || '{{ csrf_token() }}';

        fetch('{{ route("school_admin.in-out-intervention.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(res => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            icon.classList.remove('d-none');
            text.textContent = 'Dispatch Intervention';

            if (res.success) {
                // Hide modal
                const modalEl = document.getElementById('inOutInterventionModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                // Show success toast notification
                const toastContainer = document.createElement('div');
                toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
                toastContainer.style.zIndex = '10000';
                toastContainer.innerHTML = `
                    <div class="toast show align-items-center text-white bg-success border-0 shadow-lg" role="alert">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Intervention Dispatched:</strong> ${res.message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `;
                document.body.appendChild(toastContainer);
                setTimeout(() => toastContainer.remove(), 6000);
            } else {
                alertEl.textContent = res.message || 'Intervention could not be sent.';
                alertEl.classList.remove('d-none');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            icon.classList.remove('d-none');
            text.textContent = 'Dispatch Intervention';
            alertEl.textContent = err.message || 'Server communication error while dispatching intervention.';
            alertEl.classList.remove('d-none');
        });
    };

})();
</script>
