<x-layouts.teacher>
    <x-slot name="pageName">Grading System</x-slot>
    <x-slot name="subtitle">Overview of your sections' grading performance.</x-slot>



    <div class="gd-layout">
        <div class="gd-content">
            <p class="gs-panel-title mb-1">Reports</p>
            <p class="text-muted small mb-3">Select a class and report type to generate a preview.</p>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-4">
                    <label class="gd-form-label">Class</label>
                    <select id="classSelect" class="form-select form-select-sm">
                        @foreach ($teachingAssignments as $ta)
                            <option value="{{ $ta->id }}">Grade {{ $ta->section->grade_level }} - {{ $ta->section->name }} · {{ $ta->subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="gd-form-label">Student</label>
                    <select id="studentSelect" class="form-select form-select-sm" disabled>
                        <option value="">Select a class first</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="gd-form-label">&nbsp;</label>
                    <button type="button" id="viewReportCardBtn" class="btn btn-primary btn-sm w-100" disabled>
                        <i class="fa-solid fa-id-card me-1"></i> View Report Card
                    </button>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="gs-panel gs-report-type-card" data-type="form138">
                        <i class="fa-solid fa-id-card mb-2" style="color:#2438b9;"></i>
                        <p class="fw-semibold mb-1">Form 138 (Report Card)</p>
                        <p class="text-muted small mb-0">Per-learner report card.</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="gs-panel gs-report-type-card" data-type="class-record">
                        <i class="fa-solid fa-table-list mb-2" style="color:#2438b9;"></i>
                        <p class="fw-semibold mb-1">Class Record Summary</p>
                        <p class="text-muted small mb-0">Full class record across all terms.</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="gs-panel gs-report-type-card" data-type="per-learner">
                        <i class="fa-solid fa-chart-line mb-2" style="color:#2438b9;"></i>
                        <p class="fw-semibold mb-1">Per-Learner Progress</p>
                        <p class="text-muted small mb-0">Individual progress breakdown.</p>
                    </div>
                </div>
            </div>

            <div id="reportPreview" class="gs-panel mt-3 d-none">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="gs-panel-title mb-0">Preview</p>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="exportPdfBtn">
                            <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="exportExcelBtn">
                            <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                        </button>
                    </div>
                </div>
                <div id="reportContent"></div>
            </div>
        </div>
    </div>

    <script>
        let currentReportData = null;
        let currentReportType = null;

        function fetchAndRender(type) {
            const taId = document.getElementById('classSelect').value;
            if (!taId) {
                alert('You have no active classes to report on.');
                return;
            }

            fetch(`/teacher/grading-system/reports/class-record/${taId}`)
                .then(res => res.json())
                .then(data => {
                    currentReportData = data;
                    currentReportType = type;
                    renderReport(type, data);
                });
        }

        function renderReport(type, data) {
            const el = document.getElementById('reportContent');
            const title = { form138: 'Form 138 (Report Card)', 'class-record': 'Class Record Summary', 'per-learner': 'Per-Learner Progress Report' }[type];

            let termHeaders = data.terms.map(t => `<th class="text-center">${t}</th>`).join('');
            let rowsHtml = data.rows.map(r => {
                let termCells = r.terms.map(g => `<td class="text-center">${g ?? '—'}</td>`).join('');
                return `<tr><td>${r.name}</td>${termCells}<td class="text-center fw-bold">${r.final ?? '—'}</td></tr>`;
            }).join('');

            el.innerHTML = `
                <div class="mb-3 small text-muted">
                    <strong>${title}</strong><br>
                    Grade ${data.grade_level} - ${data.section} · ${data.subject}
                </div>
                <div class="table-panel">
                    <table class="table table-sm table-bordered mb-0">
                        <thead><tr><th>Learner Name</th>${termHeaders}<th class="text-center">Final Grade</th></tr></thead>
                        <tbody>${rowsHtml || '<tr><td colspan="10" class="text-center text-muted py-3">No data available.</td></tr>'}</tbody>
                    </table>
                </div>
            `;
            document.getElementById('reportPreview').classList.remove('d-none');
        }

        document.querySelectorAll('.gs-report-type-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.gs-report-type-card').forEach(c => c.classList.remove('gs-report-type-active'));
                card.classList.add('gs-report-type-active');
                fetchAndRender(card.dataset.type);
            });
        });

        document.getElementById('classSelect').addEventListener('change', () => {
            const taId = document.getElementById('classSelect').value;
            const studentSelect = document.getElementById('studentSelect');
            const viewReportCardBtn = document.getElementById('viewReportCardBtn');
            
            // Clear and disable student selection
            studentSelect.innerHTML = '<option value="">Loading students...</option>';
            studentSelect.disabled = true;
            viewReportCardBtn.disabled = true;
            
            if (taId) {
                // Fetch students for this class
                fetch(`/teacher/grading-system/reports/students/${taId}`)
                    .then(res => res.json())
                    .then(data => {
                        studentSelect.innerHTML = '<option value="">Select a student</option>';
                        data.forEach(student => {
                            studentSelect.innerHTML += `<option value="${student.enrollment_id}">${student.name} (${student.lrn})</option>`;
                        });
                        studentSelect.disabled = false;
                    })
                    .catch(error => {
                        studentSelect.innerHTML = '<option value="">Error loading students</option>';
                        console.error('Error loading students:', error);
                    });
            }
            
            if (currentReportType) fetchAndRender(currentReportType);
        });

        document.getElementById('studentSelect').addEventListener('change', () => {
            const studentSelect = document.getElementById('studentSelect');
            const viewReportCardBtn = document.getElementById('viewReportCardBtn');
            
            viewReportCardBtn.disabled = !studentSelect.value;
        });

        document.getElementById('viewReportCardBtn').addEventListener('click', () => {
            const enrollmentId = document.getElementById('studentSelect').value;
            if (enrollmentId) {
                window.location.href = `/teacher/grading-system/report-card/${enrollmentId}`;
            }
        });

        document.getElementById('exportPdfBtn').addEventListener('click', () => {
            window.print();
        });

        document.getElementById('exportExcelBtn').addEventListener('click', () => {
            if (!currentReportData) return;
            let csv = 'Learner Name,' + currentReportData.terms.join(',') + ',Final Grade\n';
            currentReportData.rows.forEach(r => {
                csv += `"${r.name}",${r.terms.map(g => g ?? '').join(',')},${r.final ?? ''}\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'class_record.csv';
            link.click();
        });
    </script>
</x-layouts.teacher>