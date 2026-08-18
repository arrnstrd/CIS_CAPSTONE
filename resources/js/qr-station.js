// QR Station — entry point.
// All logic lives in the purpose-split modules under ./modules/:
//   core    — orchestrator (builds state, wires modules, bootstraps)
//   render  — UI rendering (terminal states, popup, queue, overview)
//   input   — HID scanner + manual input, focus/buffer management
//   api     — scan submission (networking)
//   audio   — synthesized sound feedback
//   utils   — shared constants + pure helpers
import { createQrStation } from "./modules/qr-station-core.js";

createQrStation(document.getElementById("qrStationApp"));
