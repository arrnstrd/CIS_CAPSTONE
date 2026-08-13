// QR Generation with Scanner Integration
// Combines QR generation functionality with live QR scanning

(function () {
    "use strict";

    // Initialize QR Scanner functionality
    const initQrScanner = function() {
        const root = document.getElementById("qrStationApp");
        if (!root) return;

        const els = {
            scannerInput: document.getElementById("scannerInput"),
            manualInput: document.getElementById("manualQrInput"),
            manualSend: document.getElementById("manualQrSend"),
            feedback: document.getElementById("scanFeedback"),
            fbHeaderTitle: document.getElementById("fbHeaderTitle"),
            fbHeaderSub: document.getElementById("fbHeaderSub"),
            qrContent: document.getElementById("qrContent"),
            qrPopup: document.getElementById("qrPopup"),
            qrPopupCard: document.getElementById("qrPopupCard"),
            fbFill: document.getElementById("fbFill"),
            fbLabel: document.getElementById("fbLabel"),
            fbBuffer: document.getElementById("fbBuffer"),
            fbAutoReady: document.getElementById("fbAutoReady"),
            countdownBar: document.getElementById("countdownBar"),
        };

        const scanUrl = root.dataset.scanUrl;
        const csrfToken = root.dataset.csrf;

        let state = {
            processing: false,
            buffer: "",
            lastResult: null,
        };

        // Audio feedback functions
        function beep(freq, duration, vol = 0.10) {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain); gain.connect(audioCtx.destination);
                osc.type = 'sine';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0, audioCtx.currentTime);
                gain.gain.linearRampToValueAtTime(vol, audioCtx.currentTime + 0.01);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration/1000);
                osc.start();
                osc.stop(audioCtx.currentTime + duration/1000);
            } catch(e) {}
        }

        function soundSuccess() { beep(880, 90); setTimeout(() => beep(1318, 140), 100); }
        function soundWarning() { beep(660, 110); setTimeout(() => beep(660, 110), 140); }
        function soundError() { beep(220, 90, 0.14); setTimeout(() => beep(180, 220, 0.14), 100); }

        // Scanner state management
        function setState(newState) {
            state = { ...state, ...newState };
            updateUI();
        }

        function updateUI() {
            if (!els.feedback) return;

            // Update feedback state
            els.feedback.className = `qr-terminal state-${state.processing ? 'processing' : 'idle'}`;
            
            // Update header
            if (state.processing) {
                els.fbHeaderTitle.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-500 pulse-dot"></span><span class="text-xs font-bold uppercase tracking-wider text-amber-700">Processing…</span>';
                els.fbHeaderSub.textContent = 'Verifying QR token';
            } else if (state.lastResult) {
                const result = state.lastResult;
                if (result.outcome === 'success') {
                    els.fbHeaderTitle.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-500"></span><span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Scan Complete</span>';
                    els.fbHeaderSub.textContent = 'Attendance recorded';
                } else if (result.outcome === 'late') {
                    els.fbHeaderTitle.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-500"></span><span class="text-xs font-bold uppercase tracking-wider text-amber-700">Late Arrival</span>';
                    els.fbHeaderSub.textContent = 'Late arrival flagged';
                } else {
                    els.fbHeaderTitle.innerHTML = '<span class="w-2 h-2 rounded-full bg-red-500"></span><span class="text-xs font-bold uppercase tracking-wider text-red-700">Scan Issue</span>';
                    els.fbHeaderSub.textContent = 'Scan rejected';
                }
            } else {
                els.fbHeaderTitle.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-500 pulse-dot"></span><span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Scanner Active</span>';
                els.fbHeaderSub.textContent = 'Awaiting input…';
            }

            // Update content
            if (state.processing) {
                els.qrContent.innerHTML = `
                    <div class="text-center fade-in-scale flex flex-col items-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted">Processing QR scan...</p>
                    </div>
                `;
            } else if (state.lastResult) {
                const result = state.lastResult;
                if (result.outcome === 'success' || result.outcome === 'late') {
                    const scanTypeLabels = { 'IN': 'Check-In', 'OUT': 'Check-Out' };
                    const icon = result.outcome === 'late' ? 'fa-clock' : 'fa-circle-check';
                    const eyebrow = result.outcome === 'late' ? 'Late Arrival Recorded' : 'Attendance Recorded';
                    
                    els.qrContent.innerHTML = `
                        <div class="text-center">
                            <div class="mb-4">
                                <div class="w-20 h-20 mx-auto rounded-full ${result.outcome === 'late' ? 'bg-amber-100' : 'bg-emerald-100'} flex items-center justify-center mb-3">
                                    <i class="fa-solid ${icon} text-3xl ${result.outcome === 'late' ? 'text-amber-600' : 'text-emerald-600'}"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-slate-800 mb-1">${eyebrow}</h4>
                                <p class="text-sm text-slate-500">${result.message}</p>
                            </div>
                            <div class="bg-white rounded-lg border p-3 text-left">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                                        <i class="fa-solid fa-user text-slate-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-sm">${result.student?.name || 'Unknown'}</div>
                                        <div class="text-xs text-slate-500">${result.student?.grade || ''} · Sec ${result.student?.section || ''}</div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between text-xs text-slate-500">
                                    <span><i class="fas fa-clock me-1"></i> ${result.time}</span>
                                    <span><i class="fas fa-qrcode me-1"></i> ${result.qrRef}</span>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    els.qrContent.innerHTML = `
                        <div class="text-center">
                            <div class="mb-4">
                                <div class="w-20 h-20 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-3">
                                    <i class="fa-solid fa-circle-xmark text-3xl text-red-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-slate-800 mb-1">${result.title || 'Scan Rejected'}</h4>
                                <p class="text-sm text-slate-500">${result.message}</p>
                            </div>
                            <div class="bg-white rounded-lg border p-3 text-center">
                                <div class="text-xs text-slate-500">
                                    <i class="fas fa-qrcode me-1"></i> ${result.qrRef}
                                </div>
                            </div>
                        </div>
                    `;
                }
            } else {
                els.qrContent.innerHTML = `
                    <div class="text-center ready-bg w-full h-full flex flex-col items-center justify-center rounded-2xl px-6">
                        <div class="relative mb-8">
                            <div class="w-24 h-24 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-qrcode text-4xl text-indigo-600"></i>
                            </div>
                            <div class="absolute -top-1 -right-1 w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center">
                                <div class="w-2 h-2 bg-white rounded-full pulse-dot"></div>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">Ready to Scan</h3>
                        <p class="text-sm text-slate-500">Point your QR scanner at the code or use manual input below</p>
                    </div>
                `;
            }
        }

        // Process QR scan
        async function processScan(qrString) {
            if (state.processing) return;
            
            setState({ processing: true });
            
            try {
                const response = await fetch(scanUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ qr_string: qrString }),
                });

                const result = await response.json();
                
                setState({ processing: false, lastResult: result });
                
                // Play appropriate sound
                if (result.outcome === 'success') {
                    soundSuccess();
                } else if (result.outcome === 'late') {
                    soundWarning();
                } else {
                    soundError();
                }

                // Auto-reset after delay
                setTimeout(() => {
                    setState({ lastResult: null });
                }, 4500);

            } catch (error) {
                console.error('Scan error:', error);
                setState({ processing: false, lastResult: { outcome: 'error', title: 'Error', message: 'Failed to process scan' } });
                soundError();
            }
        }

        // Event listeners
        if (els.scannerInput) {
            els.scannerInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const value = els.scannerInput.value.trim();
                    els.scannerInput.value = '';
                    if (value) processScan(value);
                }
            });
        }

        if (els.manualInput && els.manualSend) {
            els.manualSend.addEventListener('click', () => {
                const value = els.manualInput.value.trim();
                if (value) {
                    processScan(value);
                    els.manualInput.value = '';
                }
            });

            els.manualInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const value = els.manualInput.value.trim();
                    if (value) {
                        processScan(value);
                        els.manualInput.value = '';
                    }
                }
            });
        }

        // Auto-focus scanner input
        function focusScanner() {
            if (els.scannerInput && !state.processing) {
                try {
                    els.scannerInput.focus({ preventScroll: true });
                } catch(e) {}
            }
        }

        setInterval(focusScanner, 250);
        document.addEventListener('click', focusScanner);
        window.addEventListener('focus', focusScanner);

        // Initialize UI
        updateUI();
    };

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initQrScanner();
    });

})();