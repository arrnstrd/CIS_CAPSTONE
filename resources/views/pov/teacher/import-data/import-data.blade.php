<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-file-import"></i>
            Grading System
        </span>
    </x-slot>
    <x-slot name="subtitle">
        <span class="page-title-subtitle">Import DepEd E-Class Record Excel sheets directly into your Grade Sheet.</span>
    </x-slot>

    @if (!$ta)
        <div class="alert alert-warning my-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> No active teaching assignment found. Please go back to <a href="{{ route('teacher.grading-system.dashboard') }}" class="alert-link">My Classes</a>.
        </div>
    @else
        <div class="gd-layout">
            <div class="gd-content">
                {{-- Header & Locked Class Context --}}
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('teacher.grading-system.grade-sheet', ['teachingAssignmentId' => $ta->id, 'grading_period_id' => $selectedPeriodId]) }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Back to Grade Sheet">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                        <div>
                            <span class="badge bg-primary px-2 py-1 mb-1">
                                Grade {{ $ta->section->grade_level }} - {{ $ta->section->name }} &middot; {{ $ta->subject->name }}
                            </span>
                            <h5 class="fw-bold mb-0">E-Class Record Import</h5>
                        </div>
                    </div>

                    <a href="{{ route('teacher.grading-system.import-data.download-template', $ta->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-download me-1"></i> Download Official DepEd Template
                    </a>
                </div>

                {{-- Stepper Progress Bar --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between position-relative stepper-bar mb-2">
                        <div class="stepper-step active" id="stepIndicator1">
                            <div class="stepper-circle">1</div>
                            <span class="stepper-label">Term & Template</span>
                        </div>
                        <div class="stepper-step" id="stepIndicator2">
                            <div class="stepper-circle">2</div>
                            <span class="stepper-label">Upload & Validate Roster</span>
                        </div>
                        <div class="stepper-step" id="stepIndicator3">
                            <div class="stepper-circle">3</div>
                            <span class="stepper-label">Preview & Confirm</span>
                        </div>
                    </div>
                </div>

                {{-- STEP 1: TERM & TEMPLATE SELECTION --}}
                <div id="stepPanel1" class="gs-panel mb-4">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-calendar-check me-2 text-primary"></i>Step 1: Select Target Term</h6>
                    <p class="text-muted small mb-3">Choose the grading term sheet you want to import from your DepEd E-Class Record Excel file.</p>

                    <div class="row g-3 align-items-end mb-4">
                        <div class="col-12 col-md-6">
                            <label for="termSelect" class="gs-filter-label">Target Term</label>
                            <select id="termSelect" class="form-select">
                                @foreach ($gradingPeriods as $period)
                                    <option value="TERM {{ $period->sequence }}" @selected(($selectedPeriodId && $selectedPeriodId == $period->id) || $loop->first)>
                                        Term {{ $period->sequence }} ({{ $period->name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-light rounded border">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="fw-semibold mb-0 small text-dark">DepEd Template File Required</p>
                                        <p class="text-muted mb-0" style="font-size: 0.8rem;">
                                            Class Level: <strong>{{ ($ta->section->grade_level >= 11) ? 'Senior High School (SHS)' : 'Elem / Junior High (Grades 1-10)' }}</strong>
                                        </p>
                                    </div>
                                    <a href="{{ route('teacher.grading-system.import-data.download-template', $ta->id) }}" class="btn btn-sm btn-outline-success">
                                        <i class="fa-solid fa-file-excel me-1"></i> Get Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary px-4" id="toStep2Btn">
                            Next: Upload File <i class="fa-solid fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 2: UPLOAD & VALIDATE ROSTER --}}
                <div id="stepPanel2" class="gs-panel mb-4 d-none">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-file-arrow-up me-2 text-primary"></i>Step 2: Upload & Validate Roster</h6>
                    <p class="text-muted small mb-3">Upload your official DepEd Excel workbook. The system will selectively parse your chosen term sheet and verify student roster against the official class database.</p>

                    <div id="dropZone" class="text-center py-5 mb-4" style="border: 2px dashed #3b82f6; cursor: pointer; background: #f8fafc; border-radius: 12px; transition: all 0.2s;">
                        <i class="fa-solid fa-file-excel" style="font-size: 2.8rem; color: #16a34a;"></i>
                        <p class="mt-2 mb-1 fw-semibold text-dark">Drag & drop your DepEd E-Class Record (.xlsx) file here</p>
                        <p class="text-muted small mb-0">or click to browse from your computer</p>
                        <input type="file" id="fileInput" accept=".xlsx, .xls" class="d-none">
                    </div>

                    <div id="fileSpinner" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Parsing Excel file...</span>
                        </div>
                        <p class="mt-2 text-muted small">Parsing worksheet structure and performing strict student roster validation...</p>
                    </div>

                    <div id="fileError" class="alert alert-danger d-none mb-4" role="alert"></div>

                    {{-- Validation Roster Alert Box --}}
                    <div id="validationResultBox" class="d-none mb-4">
                        <div id="statusBanner" class="alert mb-3" role="alert"></div>

                        <div id="discrepancyCard" class="card border-danger mb-3 d-none">
                            <div class="card-header bg-danger text-white fw-semibold small">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Hard Block Active: Student Roster Discrepancy
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <h6 class="text-danger small fw-bold">Missing Students (In DB Roster, Not in Excel)</h6>
                                        <ul class="list-group list-group-flush small" id="missingStudentsList"></ul>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <h6 class="text-danger small fw-bold">Extra Students (In Excel, Not in DB Roster)</h6>
                                        <ul class="list-group list-group-flush small" id="extraStudentsList"></ul>
                                    </div>
                                </div>
                                <p class="text-muted small mt-3 mb-0">Please verify your Excel file or contact your admin to align class enrollment before re-uploading.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" id="backToStep1Btn">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back
                        </button>
                        <button type="button" class="btn btn-primary px-4" id="toStep3Btn" disabled>
                            Next: Preview & Confirm <i class="fa-solid fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 3: PREVIEW & CONFIRM IMPORT --}}
                <div id="stepPanel3" class="gs-panel mb-4 d-none">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-clipboard-check me-2 text-primary"></i>Step 3: Preview & Confirm Import</h6>
                    <p class="text-muted small mb-3">Review the detected assessment slots, HPS, and verified student scores before saving to the Grade Sheet.</p>

                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="gs-stat-card">
                                <p class="gs-stat-label">Target Term</p>
                                <p class="gs-stat-value text-primary" id="statTargetTerm" style="font-size: 1.1rem;"></p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="gs-stat-card">
                                <p class="gs-stat-label">Template Type</p>
                                <p class="gs-stat-value" id="statTemplateType" style="font-size: 1.1rem;"></p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="gs-stat-card">
                                <p class="gs-stat-label">Assessments Detected</p>
                                <p class="gs-stat-value" id="statAssessmentsCount"></p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="gs-stat-card">
                                <p class="gs-stat-label">Verified Students</p>
                                <p class="gs-stat-value text-success" id="statMatchedCount"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Assessment Slots --}}
                    <div class="mb-4">
                        <p class="fw-semibold small mb-2">Detected Assessment Slots & Highest Possible Scores (HPS)</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Category</th>
                                        <th>Slot Number</th>
                                        <th>Excel Column</th>
                                        <th>Highest Possible Score (HPS)</th>
                                    </tr>
                                </thead>
                                <tbody id="assessmentsTableBody"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Matched Roster Preview --}}
                    <div class="mb-4">
                        <p class="fw-semibold small mb-2">Student Roster & Scores Preview</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Student Name (System)</th>
                                        <th>LRN</th>
                                        <th>Excel Name</th>
                                        <th>Scores Extracted</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsTableBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="step3Error" class="alert alert-danger d-none mb-4" role="alert"></div>

                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" id="backToStep2Btn">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back
                        </button>
                        <button type="button" class="btn btn-success px-4" id="confirmImportBtn">
                            <i class="fa-solid fa-file-export me-1"></i> Confirm & Save to Grade Sheet
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .stepper-bar {
                display: flex;
                justify-content: space-between;
                position: relative;
            }
            .stepper-bar::before {
                content: '';
                position: absolute;
                top: 18px;
                left: 15%;
                right: 15%;
                height: 2px;
                background: #e2e8f0;
                z-index: 1;
            }
            .stepper-step {
                position: relative;
                z-index: 2;
                background: #fff;
                padding: 0 10px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .stepper-circle {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                background: #f1f5f9;
                color: #64748b;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                border: 2px solid #cbd5e1;
                transition: all 0.2s;
            }
            .stepper-step.active .stepper-circle {
                background: #2563eb;
                color: #fff;
                border-color: #2563eb;
            }
            .stepper-step.completed .stepper-circle {
                background: #16a34a;
                color: #fff;
                border-color: #16a34a;
            }
            .stepper-label {
                font-size: 0.8rem;
                font-weight: 600;
                color: #64748b;
                margin-top: 6px;
            }
            .stepper-step.active .stepper-label {
                color: #1e293b;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const taId = {{ $ta->id }};
                const termSelect = document.getElementById('termSelect');
                const dropZone = document.getElementById('dropZone');
                const fileInput = document.getElementById('fileInput');
                const fileSpinner = document.getElementById('fileSpinner');
                const fileError = document.getElementById('fileError');
                const validationResultBox = document.getElementById('validationResultBox');

                const toStep2Btn = document.getElementById('toStep2Btn');
                const toStep3Btn = document.getElementById('toStep3Btn');
                const backToStep1Btn = document.getElementById('backToStep1Btn');
                const backToStep2Btn = document.getElementById('backToStep2Btn');
                const confirmImportBtn = document.getElementById('confirmImportBtn');

                let currentFile = null;
                let currentInspectionData = null;

                // Step Navigation
                toStep2Btn.addEventListener('click', () => setStep(2));
                backToStep1Btn.addEventListener('click', () => setStep(1));
                toStep3Btn.addEventListener('click', () => setStep(3));
                backToStep2Btn.addEventListener('click', () => setStep(2));

                function setStep(step) {
                    document.getElementById('stepPanel1').classList.toggle('d-none', step !== 1);
                    document.getElementById('stepPanel2').classList.toggle('d-none', step !== 2);
                    document.getElementById('stepPanel3').classList.toggle('d-none', step !== 3);

                    for (let i = 1; i <= 3; i++) {
                        const ind = document.getElementById(`stepIndicator${i}`);
                        ind.classList.toggle('active', i === step);
                        ind.classList.toggle('completed', i < step);
                    }
                }

                // File Upload & Inspection
                dropZone.addEventListener('click', () => fileInput.click());

                fileInput.addEventListener('change', (e) => {
                    if (e.target.files[0]) handleFileSelect(e.target.files[0]);
                });

                dropZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropZone.style.background = '#eff6ff';
                    dropZone.style.borderColor = '#2563eb';
                });

                dropZone.addEventListener('dragleave', () => {
                    dropZone.style.background = '#f8fafc';
                    dropZone.style.borderColor = '#3b82f6';
                });

                dropZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropZone.style.background = '#f8fafc';
                    dropZone.style.borderColor = '#3b82f6';
                    if (e.dataTransfer.files[0]) handleFileSelect(e.dataTransfer.files[0]);
                });

                function handleFileSelect(file) {
                    if (!file.name.toLowerCase().endsWith('.xlsx') && !file.name.toLowerCase().endsWith('.xls')) {
                        showError('Please select a valid Excel file (.xlsx or .xls).');
                        return;
                    }
                    currentFile = file;
                    inspectFile(file);
                }

                function inspectFile(file) {
                    fileError.classList.add('d-none');
                    fileSpinner.classList.remove('d-none');
                    validationResultBox.classList.add('d-none');
                    toStep3Btn.disabled = true;

                    const formData = new FormData();
                    formData.append('excel_file', file);
                    formData.append('teaching_assignment_id', taId);
                    formData.append('sheet_name', termSelect.value);

                    fetch("{{ route('teacher.grading-system.import-data.inspect') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(res => {
                        fileSpinner.classList.add('d-none');
                        if (!res.success) {
                            showError(res.error || 'Failed to parse Excel file.');
                            return;
                        }

                        currentInspectionData = res;
                        renderValidationResults(res.data);
                    })
                    .catch(err => {
                        fileSpinner.classList.add('d-none');
                        showError('An error occurred while inspecting the file.');
                        console.error(err);
                    });
                }

                function renderValidationResults(data) {
                    validationResultBox.classList.remove('d-none');

                    const statusBanner = document.getElementById('statusBanner');
                    const discrepancyCard = document.getElementById('discrepancyCard');
                    const missingList = document.getElementById('missingStudentsList');
                    const extraList = document.getElementById('extraStudentsList');

                    missingList.innerHTML = '';
                    extraList.innerHTML = '';

                    if (data.can_import) {
                        statusBanner.className = 'alert alert-success d-flex align-items-center gap-2 mb-3';
                        statusBanner.innerHTML = '<i class="fa-solid fa-circle-check fs-5"></i><div><strong>Roster Verified (100% Match)!</strong> All enrolled students match the uploaded Excel sheet.</div>';
                        discrepancyCard.classList.add('d-none');
                        toStep3Btn.disabled = false;
                    } else {
                        statusBanner.className = 'alert alert-danger d-flex align-items-center gap-2 mb-3';
                        statusBanner.innerHTML = '<i class="fa-solid fa-circle-xmark fs-5"></i><div><strong>Import Blocked (Roster Mismatch)!</strong> ' + (data.mismatch_message || 'The student roster in the Excel file does not match the enrolled class list.') + '</div>';

                        discrepancyCard.classList.remove('d-none');

                        if (data.missing_students.length > 0) {
                            data.missing_students.forEach(st => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item list-group-item-danger py-1';
                                li.textContent = `${st.name} (LRN: ${st.lrn})`;
                                missingList.appendChild(li);
                            });
                        } else {
                            missingList.innerHTML = '<li class="list-group-item text-muted py-1">No missing students</li>';
                        }

                        if (data.extra_students.length > 0) {
                            data.extra_students.forEach(st => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item list-group-item-danger py-1';
                                li.textContent = `${st.excel_name} (Excel Row ${st.row})`;
                                extraList.appendChild(li);
                            });
                        } else {
                            extraList.innerHTML = '<li class="list-group-item text-muted py-1">No extra students</li>';
                        }

                        toStep3Btn.disabled = true;
                    }

                    // Render Step 3 Details
                    document.getElementById('statTargetTerm').textContent = termSelect.value;
                    document.getElementById('statTemplateType').textContent = data.template_type === 'shs' ? 'SHS Template' : 'Elem / JHS Template';
                    document.getElementById('statAssessmentsCount').textContent = data.assessments.length;
                    document.getElementById('statMatchedCount').textContent = data.matched_students_count;

                    // Assessments Table
                    const asmTbody = document.getElementById('assessmentsTableBody');
                    asmTbody.innerHTML = '';
                    data.assessments.forEach(asm => {
                        const catLabel = asm.category === 'written_work' ? 'Written Work (WW)' : (asm.category === 'performance_task' ? 'Performance Task (PT)' : 'Examination (EX)');
                        const slotNum = asm.target_slot_number || asm.slot_number;
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><span class="badge bg-light text-dark border">${catLabel}</span></td>
                            <td class="fw-semibold">Slot ${slotNum}</td>
                            <td class="font-monospace text-muted">Column ${asm.col_letter}</td>
                            <td class="fw-bold">${asm.hps} pts</td>
                        `;
                        asmTbody.appendChild(tr);
                    });

                    // Matched Students Table
                    const stTbody = document.getElementById('studentsTableBody');
                    stTbody.innerHTML = '';
                    data.matched_records.forEach((st, idx) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${idx + 1}</td>
                            <td class="fw-semibold">${st.student_name}</td>
                            <td class="text-muted small">${st.lrn || 'N/A'}</td>
                            <td class="small text-secondary">${st.excel_name}</td>
                            <td><span class="badge bg-info text-dark">${st.scores.length} scores</span></td>
                            <td><span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Matched</span></td>
                        `;
                        stTbody.appendChild(tr);
                    });
                }

                // Process Final Confirm
                confirmImportBtn.addEventListener('click', () => {
                    if (!currentFile || !currentInspectionData || !currentInspectionData.data.can_import) return;

                    confirmImportBtn.disabled = true;
                    confirmImportBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving Grades...';

                    const formData = new FormData();
                    formData.append('excel_file', currentFile);
                    formData.append('teaching_assignment_id', taId);
                    formData.append('sheet_name', termSelect.value);

                    fetch("{{ route('teacher.grading-system.import-data.process') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (res.success) {
                            alert(res.message);
                            window.location.href = res.redirect_url;
                        } else {
                            showError(res.error || 'Failed to save grades.');
                            confirmImportBtn.disabled = false;
                            confirmImportBtn.innerHTML = '<i class="fa-solid fa-file-export me-1"></i> Confirm & Save to Grade Sheet';
                        }
                    })
                    .catch(err => {
                        showError('An error occurred while importing grades.');
                        console.error(err);
                        confirmImportBtn.disabled = false;
                        confirmImportBtn.innerHTML = '<i class="fa-solid fa-file-export me-1"></i> Confirm & Save to Grade Sheet';
                    });
                });

                const step3Error = document.getElementById('step3Error');

                function showError(msg) {
                    fileError.textContent = msg;
                    fileError.classList.remove('d-none');
                    if (step3Error) {
                        step3Error.textContent = msg;
                        step3Error.classList.remove('d-none');
                    }
                }

                function clearErrors() {
                    fileError.classList.add('d-none');
                    if (step3Error) step3Error.classList.add('d-none');
                }
            });
        </script>
    @endif
</x-layouts.teacher>