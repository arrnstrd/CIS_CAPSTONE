/**
 * Room Attendance Management (Side Panel, History Stepper & Bulk Operations)
 * POV: Teacher
 */

document.addEventListener('DOMContentLoaded', () => {
    const sidePanelEl = document.getElementById('attendanceSidePanel');
    const bulkActionBar = document.getElementById('bulkActionBar');

    // Guard: Exit early if not on the room attendance detail page
    if (!sidePanelEl && !bulkActionBar) {
        return;
    }

    const editForm = document.getElementById('editForm');
    const tabBtnVerify = document.getElementById('tabBtnVerify');
    const tabBtnTimeline = document.getElementById('tabBtnTimeline');
    const panelViewVerify = document.getElementById('panelViewVerify');
    const panelViewTimeline = document.getElementById('panelViewTimeline');
    const refreshTimelineBtn = document.getElementById('refreshTimelineBtn');
    const timelineContainer = document.getElementById('timelineContainer');
    const panelEventCount = document.getElementById('panelEventCount');

    let currentEnrollmentId = null;
    let currentDate = null;

    const baseUrl = sidePanelEl
        ? (sidePanelEl.getAttribute('data-base-url') || ('/teacher/room-attendance/' + sidePanelEl.getAttribute('data-section-id')))
        : '';

    // Tab navigation (Verify Status vs Progression History)
    if (tabBtnVerify && tabBtnTimeline && panelViewVerify && panelViewTimeline) {
        tabBtnVerify.addEventListener('click', function () {
            tabBtnVerify.classList.add('active');
            tabBtnTimeline.classList.remove('active');
            panelViewVerify.style.display = 'block';
            panelViewTimeline.style.display = 'none';
        });

        tabBtnTimeline.addEventListener('click', function () {
            tabBtnTimeline.classList.add('active');
            tabBtnVerify.classList.remove('active');
            panelViewVerify.style.display = 'none';
            panelViewTimeline.style.display = 'block';
        });
    }

    // Radio card active styling in side panel
    document.querySelectorAll('.ra-status-radio-card input[type="radio"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.ra-status-radio-card').forEach(function (el) {
                el.classList.remove('active');
            });
            this.closest('.ra-status-radio-card')?.classList.add('active');
        });
    });

    // Offcanvas show event for Attendance Side Panel
    if (sidePanelEl) {
        sidePanelEl.addEventListener('show.bs.offcanvas', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;

            currentEnrollmentId = btn.getAttribute('data-enrollment-id');
            currentDate = btn.getAttribute('data-date');

            const studentName = btn.getAttribute('data-student-name') || '—';
            const studentNo = btn.getAttribute('data-student-no') || '—';
            const studentInitials = btn.getAttribute('data-student-initials') || '—';
            const timeIn = btn.getAttribute('data-time-in') || 'No scan';
            const currentStatus = btn.getAttribute('data-current-status');
            const currentStatusLabel = btn.getAttribute('data-current-status-label') || '—';
            const currentStatusDot = btn.getAttribute('data-current-status-dot') || 'dot-secondary';
            const remarks = btn.getAttribute('data-remarks') || '';

            // Protected Gate Scan Preview
            const hasGateScan = btn.getAttribute('data-has-gate-scan') === '1';
            const gateExactTime = btn.getAttribute('data-gate-exact-time');
            const gateScanType = btn.getAttribute('data-gate-scan-type');
            const gateSession = btn.getAttribute('data-gate-session');

            const previewIcon = document.getElementById('panelGatePreviewIcon');
            const previewTime = document.getElementById('panelGatePreviewTime');
            const previewSub = document.getElementById('panelGatePreviewSub');

            if (previewIcon && previewTime && previewSub) {
                if (hasGateScan && gateExactTime) {
                    previewIcon.className = 'ra-gate-preview-icon';
                    previewIcon.innerHTML = '<i class="fa-solid fa-door-open"></i>';
                    previewTime.textContent = gateExactTime;
                    previewSub.innerHTML = '<i class="fa-solid fa-qrcode text-muted me-1"></i>' +
                        escapeHtml((gateScanType || 'IN') + ' Scan • ' + (gateSession ? (gateSession.charAt(0).toUpperCase() + gateSession.slice(1)) : 'Campus Attendance'));
                } else {
                    previewIcon.className = 'ra-gate-preview-icon no-scan';
                    previewIcon.innerHTML = '<i class="fa-solid fa-ban"></i>';
                    previewTime.textContent = 'No attendance scan recorded';
                    previewSub.innerHTML = '<i class="fa-solid fa-circle-info text-muted me-1"></i>No campus attendance scan recorded';
                }
            }

            // Header Strip
            const avatarEl = document.getElementById('panelStudentAvatar');
            if (avatarEl) avatarEl.textContent = studentInitials;

            const nameEl = document.getElementById('panelStudentName');
            if (nameEl) nameEl.textContent = studentName;

            const noEl = document.getElementById('panelStudentNo');
            if (noEl) noEl.innerHTML = '<i class="fa-regular fa-id-badge me-1"></i>' + escapeHtml(studentNo);

            const timeInEl = document.getElementById('panelTimeIn');
            if (timeInEl) timeInEl.textContent = timeIn;

            const statusBadge = document.getElementById('panelCurrentStatusBadge');
            if (statusBadge) {
                statusBadge.textContent = currentStatusLabel;
                statusBadge.className = 'badge-dot ' + currentStatusDot;
            }

            // Form fields
            const dateInput = document.getElementById('editDateInput');
            if (dateInput) dateInput.value = currentDate;

            const remarksInput = document.getElementById('remarks');
            if (remarksInput) remarksInput.value = remarks;

            document.querySelectorAll('.ra-status-radio-card input[type="radio"]').forEach(function (radio) {
                const isChecked = (radio.value === currentStatus);
                radio.checked = isChecked;
                radio.closest('.ra-status-radio-card')?.classList.toggle('active', isChecked);
            });

            if (editForm && baseUrl) {
                editForm.action = baseUrl + "/" + currentEnrollmentId + "/verify";
            }

            // Reset tabs to Verify Status
            if (tabBtnVerify) {
                tabBtnVerify.click();
            }

            // Fetch Timeline / History Stepper
            loadTimeline(currentEnrollmentId, currentDate);
        });
    }

    if (refreshTimelineBtn) {
        refreshTimelineBtn.addEventListener('click', function () {
            if (currentEnrollmentId) {
                loadTimeline(currentEnrollmentId, currentDate);
            }
        });
    }

    function loadTimeline(enrollmentId, date) {
        if (!timelineContainer) return;

        timelineContainer.innerHTML = '<div class="text-center py-5 text-muted">' +
            '<i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>' +
            '<span>Loading attendance progression...</span>' +
            '</div>';

        const url = baseUrl + "/" + enrollmentId + "/history" +
            (date ? "?date=" + encodeURIComponent(date) : "");

        fetch(url)
            .then(res => res.json())
            .then(data => {
                renderTimeline(data);
            })
            .catch(() => {
                timelineContainer.innerHTML = '<div class="text-center py-4 text-danger small">' +
                    '<i class="fa-solid fa-circle-exclamation fa-2x mb-2 d-block"></i>' +
                    'Failed to load history. Please try again.' +
                    '</div>';
            });
    }

    function renderTimeline(data) {
        if (!timelineContainer) return;

        const statusDotMap = {
            present: 'dot-success',
            late: 'dot-warning',
            not_in_classroom: 'dot-warning',
            absent: 'dot-danger',
            excused: 'dot-secondary',
            no_data: 'dot-secondary'
        };

        let html = '';

        if (Array.isArray(data.stepper) && data.stepper.length > 0) {
            if (panelEventCount) {
                panelEventCount.textContent = data.stepper.length;
            }

            html += '<div class="ra-stepper-date-header">';
            html += '  <span><i class="fa-regular fa-calendar me-1 text-primary"></i>' + escapeHtml(data.date_formatted || 'Selected Date') + '</span>';
            html += '  <span class="badge bg-white text-secondary border">' + data.stepper.length + ' Step' + (data.stepper.length > 1 ? 's' : '') + '</span>';
            html += '</div>';

            html += '<div class="ra-stepper">';

            data.stepper.forEach((step, idx) => {
                const isLast = (idx === data.stepper.length - 1);
                let nodeClass = 'node-modified';
                let nodeIcon = 'fa-pen-to-square';

                if (step.stage === 'raw_gate_in') {
                    nodeClass = 'node-raw';
                    nodeIcon = 'fa-door-open';
                } else if (step.stage === 'no_gate_scan') {
                    nodeClass = 'node-raw-empty';
                    nodeIcon = 'fa-ban';
                } else if (step.stage === 'system_auto_transition' || step.is_system) {
                    nodeClass = 'node-system-auto';
                    nodeIcon = 'fa-clock-rotate-left';
                } else if (step.is_current) {
                    nodeClass = 'node-active';
                    nodeIcon = 'fa-check-double';
                }

                html += '<div class="ra-step-item">';
                html += '  <div class="ra-step-node ' + nodeClass + '">';
                html += '    <i class="fa-solid ' + nodeIcon + '"></i>';
                html += '  </div>';

                if (!isLast) {
                    html += '  <div class="ra-step-connector"></div>';
                }

                html += '  <div class="ra-step-content">';
                html += '    <div class="ra-step-header">';
                html += '      <h6 class="ra-step-title">' + escapeHtml(step.title);
                if (step.is_system) {
                    html += '        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-2" style="font-size: 0.65rem;"><i class="fa-solid fa-clock-rotate-left me-1"></i>Automated</span>';
                }
                html += '      </h6>';
                html += '      <span class="ra-step-time">' + escapeHtml(step.time || '—') + '</span>';
                html += '    </div>';

                if (step.is_raw) {
                    html += '    <div class="d-flex align-items-center gap-2 mb-2">';
                    html += '      <span class="badge-dot ' + (statusDotMap[step.status] || 'dot-secondary') + '">' + escapeHtml(step.status_label) + '</span>';
                    html += '      <span class="ra-gate-preview-badge"><i class="fa-solid fa-lock text-muted me-1"></i>Raw Scan Log</span>';
                    html += '    </div>';
                    html += '    <div class="ra-step-meta">';
                    html += '      <i class="fa-solid fa-id-card-clip"></i>';
                    html += '      <span>' + escapeHtml(step.actor) + '</span>';
                    html += '    </div>';
                } else {
                    html += '    <div class="d-flex align-items-center flex-wrap gap-1">';
                    html += '      <div class="ra-step-transition">';
                    html += '        <span class="badge-dot ' + (statusDotMap[step.previous_status] || 'dot-secondary') + '">' + escapeHtml(step.previous_status_label) + '</span>';
                    html += '        <i class="fa-solid fa-arrow-right ra-step-arrow"></i>';
                    html += '        <span class="badge-dot ' + (statusDotMap[step.new_status] || 'dot-secondary') + '">' + escapeHtml(step.new_status_label) + '</span>';
                    html += '      </div>';
                    if (step.is_current) {
                        html += '      <span class="ra-step-badge-active"><i class="fa-solid fa-circle-check me-1"></i>Active Status</span>';
                    }
                    html += '    </div>';
                    html += '    <div class="ra-step-meta">';
                    html += '      <i class="fa-solid ' + (step.is_system ? 'fa-robot text-warning' : 'fa-user-check') + '"></i>';
                    html += '      <span>' + escapeHtml(step.actor) + '</span>';
                    html += '    </div>';
                    if (step.remarks) {
                        html += '    <div class="ra-step-remarks">"' + escapeHtml(step.remarks) + '"</div>';
                    }
                }

                html += '  </div>';
                html += '</div>';
            });

            html += '</div>';
        } else {
            if (panelEventCount) {
                panelEventCount.textContent = '0';
            }
            html = '<div class="text-center py-5 text-muted small">' +
                '<i class="fa-regular fa-calendar-xmark fa-2x mb-2 d-block text-secondary"></i>' +
                'No attendance changes or scans recorded for this student.' +
                '</div>';
        }

        timelineContainer.innerHTML = html;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ==========================================
    // Bulk Selection & Confirmation Modal
    // ==========================================
    const selectAllStudents = document.getElementById('selectAllStudents');
    const bulkSelectedCount = document.getElementById('bulkSelectedCount');
    const btnClearBulk = document.getElementById('btnClearBulk');
    const btnOpenBulkModal = document.getElementById('btnOpenBulkModal');
    const bulkRemarksInput = document.getElementById('bulkRemarksInput');
    const bulkConfirmModalEl = document.getElementById('bulkConfirmModal');
    const bulkVerifyForm = document.getElementById('bulkVerifyForm');

    function updateBulkBar() {
        const allBoxes = document.querySelectorAll('.ra-student-checkbox');
        const checkedBoxes = document.querySelectorAll('.ra-student-checkbox:checked');
        const count = checkedBoxes.length;

        if (bulkActionBar) {
            if (count > 0) {
                bulkActionBar.classList.add('is-open');
                bulkActionBar.setAttribute('aria-hidden', 'false');
            } else {
                bulkActionBar.classList.remove('is-open');
                bulkActionBar.setAttribute('aria-hidden', 'true');
            }
        }

        if (bulkSelectedCount) {
            bulkSelectedCount.textContent = count;
        }

        if (btnOpenBulkModal) {
            btnOpenBulkModal.disabled = (count === 0);
        }

        allBoxes.forEach(cb => {
            const row = cb.closest('tr');
            if (row) {
                row.classList.toggle('ra-row-selected', cb.checked);
            }
        });

        if (selectAllStudents && allBoxes.length > 0) {
            if (count === 0) {
                selectAllStudents.checked = false;
                selectAllStudents.indeterminate = false;
            } else if (count === allBoxes.length) {
                selectAllStudents.checked = true;
                selectAllStudents.indeterminate = false;
            } else {
                selectAllStudents.checked = false;
                selectAllStudents.indeterminate = true;
            }
        }
    }

    // Event delegation for checkbox and master select-all changes
    document.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('ra-student-checkbox')) {
            updateBulkBar();
        } else if (e.target && e.target.id === 'selectAllStudents') {
            const isChecked = e.target.checked;
            document.querySelectorAll('.ra-student-checkbox').forEach(cb => {
                cb.checked = isChecked;
            });
            updateBulkBar();
        } else if (e.target && e.target.name === 'bulk_status_choice') {
            document.querySelectorAll('.ra-bulk-status-pill').forEach(el => el.classList.remove('active'));
            e.target.closest('.ra-bulk-status-pill')?.classList.add('active');
        }
    });

    // Click checkbox cell to toggle
    document.addEventListener('click', function (e) {
        const cell = e.target.closest('.ra-checkbox-cell');
        if (cell && e.target.tagName !== 'INPUT') {
            const cb = cell.querySelector('.ra-student-checkbox');
            if (cb) {
                cb.checked = !cb.checked;
                updateBulkBar();
            }
        }
    });

    if (btnClearBulk) {
        btnClearBulk.addEventListener('click', function () {
            document.querySelectorAll('.ra-student-checkbox').forEach(cb => {
                cb.checked = false;
            });
            updateBulkBar();
        });
    }

    function populateConfirmationModal() {
        const checkedBoxes = document.querySelectorAll('.ra-student-checkbox:checked');
        if (checkedBoxes.length === 0) return false;

        const selectedRadio = document.querySelector('input[name="bulk_status_choice"]:checked');
        const targetStatus = selectedRadio ? selectedRadio.value : 'present';
        const targetStatusLabel = selectedRadio ? selectedRadio.closest('.ra-bulk-status-pill').textContent.trim() : 'Present';
        const remarks = (bulkRemarksInput ? bulkRemarksInput.value.trim() : '');

        const statusDotMap = {
            present: 'dot-success',
            late: 'dot-warning',
            not_in_classroom: 'dot-warning',
            absent: 'dot-danger',
            excused: 'dot-secondary'
        };

        const countText = document.getElementById('modalSelectedCountText');
        if (countText) countText.textContent = checkedBoxes.length;
        const listCount = document.getElementById('modalListCount');
        if (listCount) listCount.textContent = checkedBoxes.length;
        const warnCount = document.getElementById('modalWarnCount');
        if (warnCount) warnCount.textContent = checkedBoxes.length;

        const statusBadge = document.getElementById('modalStatusBadge');
        if (statusBadge) {
            statusBadge.textContent = targetStatusLabel;
            statusBadge.className = 'badge-dot fs-6 py-1 px-3 ' + (statusDotMap[targetStatus] || 'dot-secondary');
        }

        const remarksRow = document.getElementById('modalRemarksRow');
        const remarksText = document.getElementById('modalRemarksText');
        if (remarksRow && remarksText) {
            if (remarks) {
                remarksRow.style.display = 'block';
                remarksText.textContent = remarks;
            } else {
                remarksRow.style.display = 'none';
            }
        }

        const listContainer = document.getElementById('modalStudentListContainer');
        const hiddenIdsContainer = document.getElementById('modalFormEnrollmentIds');
        if (hiddenIdsContainer) hiddenIdsContainer.innerHTML = '';

        let listHtml = '';
        checkedBoxes.forEach(cb => {
            const sName = cb.getAttribute('data-student-name') || 'Student';
            const sNo = cb.getAttribute('data-student-no') || '';
            listHtml += '<span class="ra-student-chip">' +
                '<i class="fa-solid fa-circle-user text-primary small"></i> ' +
                '<span>' + escapeHtml(sName) + '</span>' +
                (sNo ? ' <span class="ra-student-chip-sub">(' + escapeHtml(sNo) + ')</span>' : '') +
                '</span>';

            if (hiddenIdsContainer) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'enrollment_ids[]';
                input.value = cb.value;
                hiddenIdsContainer.appendChild(input);
            }
        });

        if (listContainer) {
            listContainer.innerHTML = listHtml;
        }

        const formStatus = document.getElementById('modalFormStatus');
        if (formStatus) formStatus.value = targetStatus;

        const formRemarks = document.getElementById('modalFormRemarks');
        if (formRemarks) formRemarks.value = remarks;

        return true;
    }

    if (bulkConfirmModalEl) {
        bulkConfirmModalEl.addEventListener('show.bs.modal', function (event) {
            const valid = populateConfirmationModal();
            if (!valid) {
                event.preventDefault();
            }
        });
    }

    if (btnOpenBulkModal) {
        btnOpenBulkModal.addEventListener('click', function (e) {
            const checkedBoxes = document.querySelectorAll('.ra-student-checkbox:checked');
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                return;
            }
            populateConfirmationModal();

            // Fallback programmatic open if Bootstrap data API has not kicked in
            if (window.bootstrap && window.bootstrap.Modal) {
                const modal = window.bootstrap.Modal.getOrCreateInstance(bulkConfirmModalEl);
                if (modal && !bulkConfirmModalEl.classList.contains('show')) {
                    modal.show();
                }
            }
        });
    }

    if (bulkVerifyForm) {
        bulkVerifyForm.addEventListener('submit', function () {
            const submitBtn = document.getElementById('btnSubmitBulkVerify');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Applying changes...';
            }
        });
    }
});
