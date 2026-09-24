<x-layouts.school-admin>
    <x-slot name="title">QR Station</x-slot>

    <x-slot name="pageName">QR Station</x-slot>

    <x-slot name="subtitle">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span>Live QR scanning and in and out monitoring</span>
            <button type="button" class="btn qr-step-node-btn d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#qrSetupGuideModal">
                <span class="qr-step-node-icon">
                    <i class="fas fa-list-check"></i>
                </span>
                <span>Setup Guide & Instructions</span>
                <span class="qr-step-node-badge">4 Steps</span>
            </button>
        </div>
    </x-slot>

    <div id="qrStationApp" class="px-3" data-initial='@json($initialData)'
        data-scan-url="{{ route('school_admin.qr-station.scan') }}" data-csrf="{{ csrf_token() }}"
        data-attendance-realtime data-resync-url="{{ route('school_admin.attendance.monitoring.resync') }}">

        {{-- Hidden anchor input for HID keyboard-style QR scanners --}}
        <input type="text" id="scannerInput" class="qr-scanner-input" tabindex="-1" autocomplete="off"
            autocapitalize="off" autocorrect="off" spellcheck="false" aria-hidden="true">

        <div class="row g-4">
            {{-- LEFT: latest scan / feedback (scanner terminal) --}}
            <div class="col-12 col-lg-5">
                <div id="scanFeedback" class="qr-terminal state-idle">

                    {{-- header / status bar --}}
                    <div class="qr-terminal__header">
                        <div class="qr-terminal__status">
                            <span class="qr-terminal__dot"></span>
                            <span class="qr-terminal__status-text" id="fbHeaderTitle">Scanner Active</span>
                        </div>
                        <span class="qr-terminal__header-sub" id="fbHeaderSub">Awaiting input…</span>
                    </div>

                    {{-- central scan result (rendered per state by qr-station.js) --}}
                    <div class="qr-terminal__body">
                        <div id="qrContent" class="qr-content"></div>

                        {{-- pop-up result card (overlays the body after validation) --}}
                        <div id="qrPopup" class="qr-popup" style="display: none;">
                            <div class="qr-popup__card" id="qrPopupCard"></div>
                        </div>
                    </div>

                    {{-- processing progress line --}}
                    <div class="qr-terminal__progress">
                        <div class="qr-progress">
                            <div class="qr-progress__fill" id="fbFill"></div>
                        </div>
                        <div class="qr-progress__label" id="fbLabel"></div>
                    </div>

                    {{-- footer / device status bar --}}
                    <div class="qr-terminal__footer ms-3">
                        <div class="qr-terminal__device">
                            <i class="fas fa-keyboard me-1"></i>
                            <span>USB HID Scanner</span>
                            <span class="qr-terminal__sep"></span>
                            <span class="qr-terminal__buffer">buffer: <span id="fbBuffer">—</span></span>
                        </div>
                        <span class="qr-terminal__auto" id="fbAutoReady">Auto-ready</span>
                    </div>

                    {{-- thin countdown bar shown while a result is on screen --}}
                    <div id="countdownBar" class="qr-terminal__countdown" style="display: none;"></div>
                </div>

                {{-- Manual QR string fallback (same processing path as the scanner) --}}
                <div class="qr-manual mt-3">
                    <div class="qr-manual__label"><i class="fas fa-keyboard me-1"></i> Manual QR String</div>
                    <div class="input-group flex-grow-1" style="min-width: 220px;">
                        <input type="text" id="manualQrInput" class="form-control qr-manual__input"
                            placeholder="Paste QR string here" autocomplete="off" autocapitalize="off">
                        <button class="btn btn-dark" type="button" id="manualQrSend">
                            <i class="fas fa-paper-plane me-1"></i> Send
                        </button>
                    </div>
                </div>
            </div>

            {{-- RIGHT: live queue --}}
            <div class="col-12 col-lg-7">
                <div class="qr-queue">
                    <div class="qr-queue__head">
                        <div class="d-flex align-items-center gap-2">
                            <span class="qr-live-dot"></span>
                            <span class="fw-bold text-uppercase small text-dark">Live</span>
                            <span class="text-muted small d-none d-md-inline">Today's attendance scans</span>
                        </div>
                        <span class="badge bg-dark" id="queueCount">0</span>
                    </div>

                    {{-- compact live overview cards --}}
                    <div class="row g-2 mb-2  qr-overview">
                        <div class="col-6 col-xl-3">
                            <div class="qr-ov-card">
                                <div class="qr-ov-icon qr-ov-icon--dark"><i class="fas fa-hashtag"></i></div>
                                <div>
                                    <div class="qr-ov-label">Total Scans</div>
                                    <div class="qr-ov-value" id="ovTotal">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="qr-ov-card">
                                <div class="qr-ov-icon qr-ov-icon--success"><i class="fas fa-right-to-bracket"></i>
                                </div>
                                <div>
                                    <div class="qr-ov-label">Time-In</div>
                                    <div class="qr-ov-value" id="ovIn">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="qr-ov-card">
                                <div class="qr-ov-icon qr-ov-icon--primary"><i class="fas fa-right-from-bracket"></i>
                                </div>
                                <div>
                                    <div class="qr-ov-label">Time-Out</div>
                                    <div class="qr-ov-value" id="ovOut">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="qr-ov-card">
                                <div class="qr-ov-icon qr-ov-icon--warning"><i class="fas fa-clock"></i></div>
                                <div>
                                    <div class="qr-ov-label">Late</div>
                                    <div class="qr-ov-value" id="ovLate">0</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- scrollable live queue --}}
                    <div class="qr-queue-scroll">
                        <x-ui.table>
                            <thead class="text-uppercase small">
                                <tr>
                                    <th style="width: 34%"><span class="fas fa-user me-1"></span> Student</th>
                                    <th style="width: 12%"><span class="fas fa-graduation-cap me-1"></span> Grade</th>
                                    <th style="width: 12%"><span class="fas fa-users me-1"></span> Section</th>
                                    <th style="width: 13%"><span class="fas fa-clock me-1"></span> Time</th>
                                    <th style="width: 14%"><span class="fas fa-qrcode me-1"></span> Type</th>
                                    <th style="width: 15%"><span class="fas fa-flag me-1"></span> Status</th>
                                </tr>
                            </thead>
                            <tbody id="queueBody">
                                <tr id="queueEmpty">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No scans recorded yet today.
                                    </td>
                                </tr>
                            </tbody>
                        </x-ui.table>
                    </div>
                </div>
            </div>
        </div>


    </div>

    {{-- HID QR Scanner Setup & Operating Guide Modal (Stepper Wizard) --}}
    <div class="modal fade qr-guide-modal" id="qrSetupGuideModal" tabindex="-1" aria-labelledby="qrSetupGuideModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                {{-- Modal Header --}}
                <div class="modal-header bg-dark text-white p-3 px-4 border-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="qr-guide-badge-icon bg-primary bg-opacity-25 text-primary border border-primary-subtle rounded-3 p-2 d-flex align-items-center justify-content-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 7V4a2 2 0 0 1 2-2h3"></path>
                                <path d="M15 2h3a2 2 0 0 1 2 2v3"></path>
                                <path d="M4 17v3a2 2 0 0 0 2 2h3"></path>
                                <path d="M15 22h3a2 2 0 0 0 2-2v-3"></path>
                                <rect x="7" y="7" width="10" height="10" rx="1"></rect>
                            </svg>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold fs-6 mb-0 text-white" id="qrSetupGuideModalLabel">
                                HID QR Scanner Setup Guide
                            </h5>
                            <span class="text-white-50 small" id="qrGuideStepCounter" style="font-size: 0.78rem;">
                                Step 1 of 4 &bull; Schedule Setup
                            </span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Stepper Progress Nav Bar --}}
                <div class="bg-white border-bottom px-4 py-2">
                    <div class="qr-stepper-tabs d-flex align-items-center justify-content-between gap-2">
                        <button type="button" class="qr-step-tab active" data-step-target="1">
                            <span class="qr-step-dot">1</span>
                            <span class="qr-step-tab-label">Prerequisite</span>
                        </button>
                        <div class="qr-step-line"></div>
                        <button type="button" class="qr-step-tab" data-step-target="2">
                            <span class="qr-step-dot">2</span>
                            <span class="qr-step-tab-label">Scanner Device</span>
                        </button>
                        <div class="qr-step-line"></div>
                        <button type="button" class="qr-step-tab" data-step-target="3">
                            <span class="qr-step-dot">3</span>
                            <span class="qr-step-tab-label">Keep Active</span>
                        </button>
                        <div class="qr-step-line"></div>
                        <button type="button" class="qr-step-tab" data-step-target="4">
                            <span class="qr-step-dot">4</span>
                            <span class="qr-step-tab-label">Live Roster</span>
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="modal-body p-4 bg-light" style="min-height: 320px;">

                    {{-- Step 1: Prerequisite Schedule Configuration --}}
                    <div class="qr-wizard-step active" data-step-content="1">
                        <div class="qr-guide-prereq-card p-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="qr-guide-prereq-icon p-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                        <line x1="12" y1="9" x2="12" y2="13"></line>
                                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                    </svg>
                                </div>
                                <div>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 mb-1" style="font-size: 0.72rem; font-weight: 700;">
                                        MANDATORY SYSTEM PREREQUISITES
                                    </span>
                                    <h5 class="fw-bold text-dark mb-0 fs-6">Section & Schedule Configuration Rules</h5>
                                </div>
                            </div>

                            <p class="text-secondary small leading-relaxed mb-3">
                                <strong>Why & How it Works:</strong> For QR attendance validation to succeed, student scan lookup relies on matching student section settings against configured schedule rules:
                            </p>

                            <div class="bg-white p-3 rounded-3 border mb-3">
                                <div class="d-flex flex-column gap-2.5 text-dark small">
                                    <div class="d-flex align-items-start gap-2 mb-2">
                                        <i class="fas fa-user-check text-success mt-1"></i>
                                        <div>
                                            <strong>1. Student Enrolled in Section:</strong>
                                            <span class="text-muted d-block">Student must be actively enrolled and assigned to a section roster.</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2 mb-2">
                                        <i class="fas fa-layer-group text-primary mt-1"></i>
                                        <div>
                                            <strong>2. Section Department & Session Type Configured:</strong>
                                            <span class="text-muted d-block">The section must be configured with its <strong>Department Level</strong> (Elementary, High School, Senior High) and <strong>Session Type</strong> (Morning, Afternoon, Whole Day).</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="fas fa-clock text-warning mt-1"></i>
                                        <div>
                                            <strong>3. Schedule Rules Active for Level & Session:</strong>
                                            <span class="text-muted d-block">An active <a href="{{ route('schedule-configuration.index') }}" class="text-decoration-underline text-dark fw-bold">Schedule Configuration</a> rule must exist for each department level & session type to validate Time-In, Late Cutoff, and Time-Out.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-muted small mb-3">
                                Scans for unassigned students or sections without matching schedule rules cannot be validated by the scanner station.
                            </p>

                            <div>
                                <a href="{{ route('schedule-configuration.index') }}" class="btn btn-warning btn-sm fw-bold px-3">
                                    <i class="fas fa-gear me-1"></i> Manage Schedule Configuration <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Connect HID Scanner --}}
                    <div class="qr-wizard-step" data-step-content="2" style="display: none;">
                        <div class="qr-guide-step-card p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="qr-guide-step-number qr-guide-step-number-1">STEP 01 OF 03</span>
                                <div class="qr-guide-step-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#3730a3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 2v20M7 7l5-5 5 5M7 17l5 5 5-5"></path>
                                        <circle cx="12" cy="12" r="3" fill="#4f46e5"></circle>
                                    </svg>
                                </div>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Connect HID QR Scanner</h5>
                            <p class="text-secondary leading-relaxed mb-4">
                                Connect any hardware 2D QR barcode scanner device or smartphone app configured with <strong>HID Scanner Mode</strong> (Human Interface Device). Connect via <strong>USB Cable</strong> or <strong>Bluetooth</strong>.
                            </p>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <div class="bg-white p-3 border rounded-3 text-center">
                                        <i class="fas fa-plug text-primary fs-3 mb-2 d-block"></i>
                                        <h6 class="fw-bold text-dark mb-1">USB Cable Scanner</h6>
                                        <span class="text-muted small">Plug and play 2D QR barcode scanner</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="bg-white p-3 border rounded-3 text-center">
                                        <i class="fab fa-bluetooth-b text-indigo fs-3 mb-2 d-block"></i>
                                        <h6 class="fw-bold text-dark mb-1">Bluetooth / Wireless</h6>
                                        <span class="text-muted small">Paired mobile or wireless HID scanner gun</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3: Keep Station Active --}}
                    <div class="qr-wizard-step" data-step-content="3" style="display: none;">
                        <div class="qr-guide-step-card p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="qr-guide-step-number qr-guide-step-number-2">STEP 02 OF 03</span>
                                <div class="qr-guide-step-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                        <line x1="8" y1="21" x2="16" y2="21"></line>
                                        <line x1="12" y1="17" x2="12" y2="21"></line>
                                        <circle cx="12" cy="10" r="2" fill="#d97706"></circle>
                                    </svg>
                                </div>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Keep Station Active & Open in Browser</h5>
                            <p class="text-secondary leading-relaxed mb-4">
                                Ensure your device is connected and <strong>leave this QR Station page active and open</strong>. HID scanners type decoded QR strings directly into the active browser page like a keyboard.
                            </p>

                            <div class="alert alert-warning border-warning-subtle d-flex align-items-center gap-3 p-3 rounded-3 mb-0">
                                <i class="fas fa-exclamation-triangle text-warning fs-4 flex-shrink-0"></i>
                                <div class="small">
                                    <strong>Important Requirement:</strong> If the QR station page is closed or minimized, incoming scans typed by the scanner will not be received or processed by the system.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 4: Live Sync --}}
                    <div class="qr-wizard-step" data-step-content="4" style="display: none;">
                        <div class="qr-guide-step-card p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="qr-guide-step-number qr-guide-step-number-3">STEP 03 OF 03</span>
                                <div class="qr-guide-step-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </div>
                            </div>
                            <h5 class="fw-bold text-dark mb-2">Live Terminal Feedback & Roster Table Sync</h5>
                            <p class="text-secondary leading-relaxed mb-4">
                                When a student presents their QR code to the scanner, verification occurs automatically in real time:
                            </p>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="bg-white p-3 border rounded-3 h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2 text-primary fw-bold">
                                            <i class="fas fa-desktop"></i> Left Terminal
                                        </div>
                                        <p class="text-muted small mb-0">
                                            Displays instantaneous visual feedback, student information, attendance status (Present, Late, Time-Out), and plays chime audio.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="bg-white p-3 border rounded-3 h-100">
                                        <div class="d-flex align-items-center gap-2 mb-2 text-success fw-bold">
                                            <i class="fas fa-table-list"></i> Right Live Roster
                                        </div>
                                        <p class="text-muted small mb-0">
                                            Appends today's scan entry live to the attendance roster table with grade, section, timestamp, and scan status flags.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer bg-white px-4 py-3 border-top d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 fw-bold rounded-pill" id="btnQrGuidePrev" disabled>
                        <i class="fas fa-arrow-left me-1"></i> Previous
                    </button>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-primary btn-sm px-4 fw-bold rounded-pill" id="btnQrGuideNext">
                            Next Step <i class="fas fa-arrow-right ms-1"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm px-4 fw-bold rounded-pill" id="btnQrGuideFinish" style="display: none;" data-bs-dismiss="modal">
                            <i class="fas fa-check me-1"></i> Got It, Finish Guide
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let currentStep = 1;
            const totalSteps = 4;
            const stepTitles = [
                'Step 1 of 4 • Schedule Setup',
                'Step 2 of 4 • Connect Scanner',
                'Step 3 of 4 • Keep Station Active',
                'Step 4 of 4 • Live Roster Feed'
            ];

            const btnPrev = document.getElementById('btnQrGuidePrev');
            const btnNext = document.getElementById('btnQrGuideNext');
            const btnFinish = document.getElementById('btnQrGuideFinish');
            const stepCounter = document.getElementById('qrGuideStepCounter');

            const updateStep = (step) => {
                currentStep = step;

                // Update content visibility
                document.querySelectorAll('[data-step-content]').forEach(el => {
                    el.style.display = (el.getAttribute('data-step-content') == step) ? 'block' : 'none';
                });

                // Update tabs
                document.querySelectorAll('[data-step-target]').forEach(el => {
                    const s = el.getAttribute('data-step-target');
                    if (s == step) {
                        el.classList.add('active');
                    } else {
                        el.classList.remove('active');
                    }
                });

                // Update step counter text
                if (stepCounter && stepTitles[step - 1]) {
                    stepCounter.textContent = stepTitles[step - 1];
                }

                // Update button states
                if (btnPrev) btnPrev.disabled = (step === 1);
                if (step === totalSteps) {
                    if (btnNext) btnNext.style.display = 'none';
                    if (btnFinish) btnFinish.style.display = 'inline-block';
                } else {
                    if (btnNext) btnNext.style.display = 'inline-block';
                    if (btnFinish) btnFinish.style.display = 'none';
                }
            };

            if (btnNext) {
                btnNext.addEventListener('click', () => {
                    if (currentStep < totalSteps) updateStep(currentStep + 1);
                });
            }

            if (btnPrev) {
                btnPrev.addEventListener('click', () => {
                    if (currentStep > 1) updateStep(currentStep - 1);
                });
            }

            document.querySelectorAll('[data-step-target]').forEach(tab => {
                tab.addEventListener('click', () => {
                    const targetStep = parseInt(tab.getAttribute('data-step-target'));
                    if (targetStep) updateStep(targetStep);
                });
            });

            const modalEl = document.getElementById('qrSetupGuideModal');
            if (modalEl) {
                modalEl.addEventListener('show.bs.modal', () => {
                    updateStep(1);
                });
            }
        });
    </script>

</x-layouts.school-admin>
