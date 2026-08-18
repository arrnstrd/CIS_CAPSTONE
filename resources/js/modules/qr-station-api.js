// QR Station — scan submission (the "actual scanning").
// POSTs the QR string to the backend scan endpoint and drives the render,
// queue, overview and audio modules with the classified outcome.

import { classify, qrRefOf } from "./qr-station-utils.js";

export function createApi({ root, els, state, render, audio, focusScanner }) {
    async function submitScan(code) {
        const qr = (code || "").trim();
        if (!qr || state.processing) return;

        state.processing = true;
        state.qrRef = qrRefOf(qr);
        render.setTerminalMode("processing");

        let result = { key: "error" };
        let body = {};
        try {
            const res = await fetch(root.dataset.scanUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": root.dataset.csrf,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({ code: qr }),
            });

            try {
                body = await res.json();
            } catch (e) {
                body = {};
            }

            result = classify(res.status, body);

            audio.playSound(result.sound);
            render.addToQueue(result, body);
            render.updateOverview(result);
        } catch (err) {
            result = { key: "error", sound: "invalid", logged: false };
            body = { message: "Could not reach the server. Please try again." };
            audio.playSound("invalid");
        } finally {
            // after validation, reveal the result (student details / rejection)
            render.setTerminalMode("result", result, body);
            state.processing = false;
            if (els.manualInput) els.manualInput.value = "";
            focusScanner();
        }
    }

    return { submitScan };
}
