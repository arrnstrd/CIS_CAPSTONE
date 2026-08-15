// QR Station — HID scanner + manual input wiring.
// Captures keyboard input globally (HID keyboard-style scanners type the QR
// string + Enter), buffers characters, and triggers the latest scan submit
// handler. Also keeps the hidden scanner input focused.

export function createInput({ els, state, getSubmit, ensureAudio }) {
    // capture keyboard input globally so the operator never has to click before scanning
    // Auto-submit: most HID scanners just type the QR string with no terminating Enter,
    // so a short typing pause marks the scan as complete and submits it automatically.
    const AUTO_SUBMIT_DELAY_MS = 120;
    let autoSubmitTimer = null;

    function clearPendingAutoSubmit() {
        if (autoSubmitTimer) {
            clearTimeout(autoSubmitTimer);
            autoSubmitTimer = null;
        }
    }

    function submitBuffer() {
        if (!state.buffer) return;

        // station is busy — keep the buffered string and submit it once it frees up
        if (state.processing) {
            armAutoSubmit();
            return;
        }

        clearPendingAutoSubmit();
        const value = state.buffer;
        state.buffer = "";
        if (els.fbBuffer) els.fbBuffer.textContent = "—";
        const submit = getSubmit();
        if (submit) submit(value);
    }

    function armAutoSubmit() {
        clearPendingAutoSubmit();
        autoSubmitTimer = setTimeout(submitBuffer, AUTO_SUBMIT_DELAY_MS);
    }

    function handleKeydown(e) {
        ensureAudio();
        const inManual = document.activeElement === els.manualInput;

        if (e.key === "Enter") {
            if (inManual) {
                const submit = getSubmit();
                if (submit) submit(els.manualInput.value);
                return;
            }
            e.preventDefault();
            clearPendingAutoSubmit();
            submitBuffer();
            return;
        }

        if (inManual) return;

        if (
            !e.ctrlKey &&
            !e.metaKey &&
            !e.altKey &&
            e.key &&
            e.key.length === 1
        ) {
            e.preventDefault();
            state.buffer += e.key;
            if (els.fbBuffer) els.fbBuffer.textContent = state.buffer;
            armAutoSubmit();
        }
    }

    function focusScanner() {
        if (!els.scannerInput) return;
        if (document.activeElement !== els.manualInput) {
            els.scannerInput.focus({ preventScroll: true });
        }
    }

    function wire() {
        document.addEventListener("keydown", handleKeydown);

        // keep the hidden scanner input focused unless the operator is using the manual field
        if (els.scannerInput) {
            els.scannerInput.addEventListener("blur", () => {
                setTimeout(() => {
                    if (document.activeElement !== els.manualInput) {
                        els.scannerInput.focus({ preventScroll: true });
                    }
                }, 0);
            });
        }

        if (els.manualSend) {
            els.manualSend.addEventListener("click", () => {
                const submit = getSubmit();
                if (submit) submit(els.manualInput.value);
            });
        }
    }

    return { focusScanner, wire };
}
