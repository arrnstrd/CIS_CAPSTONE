// QR Station — HID scanner + manual input wiring.
// Captures keyboard input globally (HID keyboard-style scanners type the QR
// string + Enter), buffers characters, and triggers the latest scan submit
// handler. Also keeps the hidden scanner input focused.

export function createInput({ els, state, getSubmit, ensureAudio }) {
    // capture keyboard input globally so the operator never has to click before scanning
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
            const submit = getSubmit();
            if (submit) submit(state.buffer);
            state.buffer = "";
            if (els.fbBuffer) els.fbBuffer.textContent = "—";
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
