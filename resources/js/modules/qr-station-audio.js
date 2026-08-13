// QR Station — lightweight synthesized audio feedback.
// Creates the Web Audio context lazily (on first user interaction) and
// plays short tones per outcome kind.

export function createAudio() {
    let audioCtx = null;

    function ensureAudio() {
        if (!audioCtx) {
            const AC = window.AudioContext || window.webkitAudioContext;
            if (AC) audioCtx = new AC();
        }
        if (audioCtx && audioCtx.state === "suspended") audioCtx.resume();
    }

    function tone(freq, opts) {
        opts = opts || {};
        if (!audioCtx) return;
        const start = opts.start || 0;
        const duration = opts.duration || 0.12;
        const t0 = audioCtx.currentTime + start;
        const osc = audioCtx.createOscillator();
        const g = audioCtx.createGain();
        osc.type = opts.type || "sine";
        osc.frequency.setValueAtTime(freq, t0);
        g.gain.setValueAtTime(0, t0);
        g.gain.linearRampToValueAtTime(opts.gain || 0.18, t0 + 0.01);
        g.gain.exponentialRampToValueAtTime(0.0001, t0 + duration);
        osc.connect(g).connect(audioCtx.destination);
        osc.start(t0);
        osc.stop(t0 + duration + 0.05);
    }

    function playSound(kind) {
        ensureAudio();
        if (!audioCtx) return;
        switch (kind) {
            case "success-in":
                tone(523.25);
                tone(783.99, { start: 0.12, gain: 0.2 });
                break;
            case "success-out":
                tone(783.99);
                tone(523.25, { start: 0.12, gain: 0.2 });
                break;
            case "late":
                tone(440, { type: "square", gain: 0.09 });
                tone(440, { type: "square", gain: 0.09, start: 0.16 });
                break;
            case "duplicate":
                tone(660, { type: "square", gain: 0.09 });
                tone(660, { type: "square", gain: 0.09, start: 0.14 });
                break;
            case "excess":
                tone(330, { type: "square", gain: 0.1 });
                tone(330, { type: "square", gain: 0.1, start: 0.12 });
                tone(330, { type: "square", gain: 0.1, start: 0.24 });
                break;
            case "invalid":
            case "error":
            default:
                tone(196, { duration: 0.28, type: "sawtooth", gain: 0.1 });
                break;
        }
    }

    return { ensureAudio, playSound };
}
