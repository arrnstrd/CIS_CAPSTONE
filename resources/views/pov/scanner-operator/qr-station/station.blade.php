<x-layouts.scanner-operator>
    <x-slot name="title">QR Station</x-slot>

    <x-slot name="pageName">QR Station</x-slot>

    <x-slot name="subtitle">Live QR scanning and attendance monitoring</x-slot>

    <div id="qrStationApp" class="px-3" data-initial='@json($initialData)'
        data-scan-url="{{ route('qr-station.scan') }}" data-csrf="{{ csrf_token() }}">

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
                            <span class="text-muted small d-none d-md-inline">Today's gate scans</span>
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

</x-layouts.scanner-operator>
