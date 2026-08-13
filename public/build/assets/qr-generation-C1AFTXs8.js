(function(){let e=function(){let e=document.getElementById(`qrStationApp`);if(!e)return;let t={scannerInput:document.getElementById(`scannerInput`),manualInput:document.getElementById(`manualQrInput`),manualSend:document.getElementById(`manualQrSend`),feedback:document.getElementById(`scanFeedback`),fbHeaderTitle:document.getElementById(`fbHeaderTitle`),fbHeaderSub:document.getElementById(`fbHeaderSub`),qrContent:document.getElementById(`qrContent`),qrPopup:document.getElementById(`qrPopup`),qrPopupCard:document.getElementById(`qrPopupCard`),fbFill:document.getElementById(`fbFill`),fbLabel:document.getElementById(`fbLabel`),fbBuffer:document.getElementById(`fbBuffer`),fbAutoReady:document.getElementById(`fbAutoReady`),countdownBar:document.getElementById(`countdownBar`)},n=e.dataset.scanUrl,r=e.dataset.csrf,i={processing:!1,buffer:``,lastResult:null};function a(e,t,n=.1){try{let r=new(window.AudioContext||window.webkitAudioContext),i=r.createOscillator(),a=r.createGain();i.connect(a),a.connect(r.destination),i.type=`sine`,i.frequency.value=e,a.gain.setValueAtTime(0,r.currentTime),a.gain.linearRampToValueAtTime(n,r.currentTime+.01),a.gain.exponentialRampToValueAtTime(.001,r.currentTime+t/1e3),i.start(),i.stop(r.currentTime+t/1e3)}catch{}}function o(){a(880,90),setTimeout(()=>a(1318,140),100)}function s(){a(660,110),setTimeout(()=>a(660,110),140)}function c(){a(220,90,.14),setTimeout(()=>a(180,220,.14),100)}function l(e){i={...i,...e},u()}function u(){if(t.feedback){if(t.feedback.className=`qr-terminal state-${i.processing?`processing`:`idle`}`,i.processing)t.fbHeaderTitle.innerHTML=`<span class="w-2 h-2 rounded-full bg-amber-500 pulse-dot"></span><span class="text-xs font-bold uppercase tracking-wider text-amber-700">Processing…</span>`,t.fbHeaderSub.textContent=`Verifying QR token`;else if(i.lastResult){let e=i.lastResult;e.outcome===`success`?(t.fbHeaderTitle.innerHTML=`<span class="w-2 h-2 rounded-full bg-emerald-500"></span><span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Scan Complete</span>`,t.fbHeaderSub.textContent=`Attendance recorded`):e.outcome===`late`?(t.fbHeaderTitle.innerHTML=`<span class="w-2 h-2 rounded-full bg-amber-500"></span><span class="text-xs font-bold uppercase tracking-wider text-amber-700">Late Arrival</span>`,t.fbHeaderSub.textContent=`Late arrival flagged`):(t.fbHeaderTitle.innerHTML=`<span class="w-2 h-2 rounded-full bg-red-500"></span><span class="text-xs font-bold uppercase tracking-wider text-red-700">Scan Issue</span>`,t.fbHeaderSub.textContent=`Scan rejected`)}else t.fbHeaderTitle.innerHTML=`<span class="w-2 h-2 rounded-full bg-emerald-500 pulse-dot"></span><span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Scanner Active</span>`,t.fbHeaderSub.textContent=`Awaiting input…`;if(i.processing)t.qrContent.innerHTML=`
                    <div class="text-center fade-in-scale flex flex-col items-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted">Processing QR scan...</p>
                    </div>
                `;else if(i.lastResult){let e=i.lastResult;if(e.outcome===`success`||e.outcome===`late`){let n=e.outcome===`late`?`fa-clock`:`fa-circle-check`,r=e.outcome===`late`?`Late Arrival Recorded`:`Attendance Recorded`;t.qrContent.innerHTML=`
                        <div class="text-center">
                            <div class="mb-4">
                                <div class="w-20 h-20 mx-auto rounded-full ${e.outcome===`late`?`bg-amber-100`:`bg-emerald-100`} flex items-center justify-center mb-3">
                                    <i class="fa-solid ${n} text-3xl ${e.outcome===`late`?`text-amber-600`:`text-emerald-600`}"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-slate-800 mb-1">${r}</h4>
                                <p class="text-sm text-slate-500">${e.message}</p>
                            </div>
                            <div class="bg-white rounded-lg border p-3 text-left">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                                        <i class="fa-solid fa-user text-slate-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-sm">${e.student?.name||`Unknown`}</div>
                                        <div class="text-xs text-slate-500">${e.student?.grade||``} · Sec ${e.student?.section||``}</div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between text-xs text-slate-500">
                                    <span><i class="fas fa-clock me-1"></i> ${e.time}</span>
                                    <span><i class="fas fa-qrcode me-1"></i> ${e.qrRef}</span>
                                </div>
                            </div>
                        </div>
                    `}else t.qrContent.innerHTML=`
                        <div class="text-center">
                            <div class="mb-4">
                                <div class="w-20 h-20 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-3">
                                    <i class="fa-solid fa-circle-xmark text-3xl text-red-600"></i>
                                </div>
                                <h4 class="text-lg font-semibold text-slate-800 mb-1">${e.title||`Scan Rejected`}</h4>
                                <p class="text-sm text-slate-500">${e.message}</p>
                            </div>
                            <div class="bg-white rounded-lg border p-3 text-center">
                                <div class="text-xs text-slate-500">
                                    <i class="fas fa-qrcode me-1"></i> ${e.qrRef}
                                </div>
                            </div>
                        </div>
                    `}else t.qrContent.innerHTML=`
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
                `}}async function d(e){if(!i.processing){l({processing:!0});try{let t=await(await fetch(n,{method:`POST`,headers:{"Content-Type":`application/json`,"X-CSRF-TOKEN":r},body:JSON.stringify({qr_string:e})})).json();l({processing:!1,lastResult:t}),t.outcome===`success`?o():t.outcome===`late`?s():c(),setTimeout(()=>{l({lastResult:null})},4500)}catch(e){console.error(`Scan error:`,e),l({processing:!1,lastResult:{outcome:`error`,title:`Error`,message:`Failed to process scan`}}),c()}}}t.scannerInput&&t.scannerInput.addEventListener(`keydown`,e=>{if(e.key===`Enter`){e.preventDefault();let n=t.scannerInput.value.trim();t.scannerInput.value=``,n&&d(n)}}),t.manualInput&&t.manualSend&&(t.manualSend.addEventListener(`click`,()=>{let e=t.manualInput.value.trim();e&&(d(e),t.manualInput.value=``)}),t.manualInput.addEventListener(`keypress`,e=>{if(e.key===`Enter`){e.preventDefault();let n=t.manualInput.value.trim();n&&(d(n),t.manualInput.value=``)}}));function f(){if(t.scannerInput&&!i.processing)try{t.scannerInput.focus({preventScroll:!0})}catch{}}setInterval(f,250),document.addEventListener(`click`,f),window.addEventListener(`focus`,f),u()};document.addEventListener(`DOMContentLoaded`,function(){e()})})();