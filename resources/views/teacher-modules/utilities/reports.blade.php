<x-layouts.teacher>
    <x-slot name="pageName">Grading System</x-slot>
    <x-slot name="subtitle">Generate formal reports from your grading and academic records.</x-slot>

    <div class="gd-layout">
        <div class="gd-content">

            {{-- Page Header --}}
            <div class="mb-4">
                <p class="gs-panel-title mb-1">Reports</p>
                <p class="text-muted small mb-0">
                    Select a report type and class to generate a printable or exportable report.
                </p>
            </div>

            {{-- Report Filters --}}
            <div class="gs-filter-bar mb-4">
                <div class="row g-3">

                    {{-- Class / Section --}}
                    <div class="col-12 col-md-4">
                        <label for="classSelect" class="gs-filter-label">
                            Class / Section
                        </label>

                        <select id="classSelect" class="form-select form-select-sm">
                            @forelse ($teachingAssignments as $ta)
                                <option value="{{ $ta->id }}">
                                    Grade {{ $ta->section->grade_level }}
                                    - {{ $ta->section->name }}
                                    &middot; {{ $ta->subject->name }}
                                </option>
                            @empty
                                <option value="">
                                    No active classes available
                                </option>
                            @endforelse
                        </select>
                    </div>

                    {{-- Term --}}
                    <div class="col-12 col-md-4">
                        <label for="termSelect" class="gs-filter-label">
                            Term
                        </label>

                        <select id="termSelect" class="form-select form-select-sm">
                            <option value="">All Terms</option>

                            @foreach ($gradingPeriods ?? [] as $period)
                                <option value="{{ $period->id }}">
                                    Term {{ $period->sequence }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Student --}}
                    <div class="col-12 col-md-4">
                        <label for="studentSelect" class="gs-filter-label">
                            Student
                            <span class="text-muted fw-normal">
                                (Academic Record only)
                            </span>
                        </label>

                        <select id="studentSelect" class="form-select form-select-sm" disabled>
                            <option value="">
                                Select Academic Record first
                            </option>
                        </select>
                    </div>

                </div>
            </div>

            {{-- Report Type --}}
            <div class="mb-3">
                <p class="gs-panel-title mb-1">Report Type</p>
                <p class="text-muted small mb-3">
                    Choose the type of academic report you want to generate.
                </p>
            </div>

            <div class="row g-3">

                {{-- Class Grade Report --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="gs-panel gs-report-type-card h-100"
                         data-type="class-grade">

                        <i class="fa-solid fa-table-list"></i>

                        <p class="fw-semibold mb-1">
                            Class Grade Report
                        </p>

                        <p class="text-muted small mb-0">
                            Summary of learners' grades for the selected class and term.
                        </p>
                    </div>
                </div>

                {{-- Student Academic Record --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="gs-panel gs-report-type-card h-100"
                         data-type="academic-record">

                        <i class="fa-solid fa-user-graduate"></i>

                        <p class="fw-semibold mb-1">
                            Student Academic Record
                        </p>

                        <p class="text-muted small mb-0">
                            Individual academic performance and grade history.
                        </p>
                    </div>
                </div>

                {{-- Grade Submission Report --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="gs-panel gs-report-type-card h-100"
                         data-type="grade-submission">

                        <i class="fa-solid fa-file-circle-check"></i>

                        <p class="fw-semibold mb-1">
                            Grade Submission Report
                        </p>

                        <p class="text-muted small mb-0">
                            Overview of grade completion and submission status.
                        </p>
                    </div>
                </div>

                {{-- At-Risk Monitoring Report --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="gs-panel gs-report-type-card h-100"
                         data-type="at-risk">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                        <p class="fw-semibold mb-1">
                            At-Risk Monitoring Report
                        </p>

                        <p class="text-muted small mb-0">
                            List of learners with identified academic risk indicators.
                        </p>
                    </div>
                </div>

                {{-- Attendance Report --}}
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="gs-panel gs-report-type-card h-100"
                         data-type="attendance">

                        <i class="fa-solid fa-calendar-check"></i>

                        <p class="fw-semibold mb-1">
                            Attendance Report
                        </p>

                        <p class="text-muted small mb-0">
                            Attendance summary for learners in the selected class.
                        </p>
                    </div>
                </div>

            </div>

            {{-- Preview --}}
            <div id="reportPreview" class="gs-panel mt-4 d-none">

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

                    <div>
                        <p class="gs-panel-title mb-1">
                            Report Preview
                        </p>

                        <p class="text-muted small mb-0">
                            Review the generated report before exporting or printing.
                        </p>
                    </div>

                    <div class="d-flex gap-2">

                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                id="printReportBtn">

                            <i class="fa-solid fa-print me-1"></i>
                            Print
                        </button>

                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                id="exportPdfBtn">

                            <i class="fa-solid fa-file-pdf me-1"></i>
                            PDF
                        </button>

                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                id="exportExcelBtn">

                            <i class="fa-solid fa-file-excel me-1"></i>
                            Excel
                        </button>

                    </div>
                </div>

                <div id="reportContent">
                    {{-- Generated report appears here --}}
                </div>

            </div>

        </div>
    </div>


    <script>
        let currentReportData = null;
        let currentReportType = null;

        const classSelect = document.getElementById('classSelect');
        const termSelect = document.getElementById('termSelect');
        const studentSelect = document.getElementById('studentSelect');
        const reportPreview = document.getElementById('reportPreview');
        const reportContent = document.getElementById('reportContent');


        /*
        |--------------------------------------------------------------------------
        | HTML Helpers
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {
            if (value === null || value === undefined) {
                return '';
            }

            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }


        function displayValue(value, fallback = '—') {
            return value === null ||
                   value === undefined ||
                   value === ''
                ? fallback
                : escapeHtml(value);
        }


        function formatDateTime(value) {
            if (!value) {
                return '—';
            }

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return escapeHtml(value);
            }

            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        }


        function formatIndicators(indicators) {
            if (!indicators) {
                return 'No indicators';
            }

            if (!Array.isArray(indicators)) {
                return escapeHtml(String(indicators));
            }

            if (indicators.length === 0) {
                return 'No indicators';
            }

            return indicators
                .map(indicator => {

                    if (typeof indicator === 'string') {
                        return `<span class="gs-badge gs-badge-neutral me-1 mb-1">
                            ${escapeHtml(indicator)}
                        </span>`;
                    }

                    if (typeof indicator === 'object' && indicator !== null) {

                        const text =
                            indicator.label ??
                            indicator.name ??
                            indicator.indicator ??
                            indicator.title ??
                            JSON.stringify(indicator);

                        return `<span class="gs-badge gs-badge-neutral me-1 mb-1">
                            ${escapeHtml(text)}
                        </span>`;
                    }

                    return '';
                })
                .join('');
        }


        function showPreviewMessage(message, subMessage = '') {

            reportContent.innerHTML = `
                <div class="gs-chart-empty py-5 text-center">

                    <i class="fa-solid fa-circle-info gs-chart-empty-icon"></i>

                    <p class="mb-1">
                        ${escapeHtml(message)}
                    </p>

                    ${
                        subMessage
                            ? `
                                <span class="small text-muted">
                                    ${escapeHtml(subMessage)}
                                </span>
                              `
                            : ''
                    }

                </div>
            `;

            reportPreview.classList.remove('d-none');
        }


        /*
        |--------------------------------------------------------------------------
        | Filter State
        |--------------------------------------------------------------------------
        */

        function updateFilterState() {

            const isAcademicRecord =
                currentReportType === 'academic-record';

            const isAttendance =
                currentReportType === 'attendance';

            studentSelect.disabled =
                !classSelect.value || !isAcademicRecord;

            termSelect.disabled =
                isAttendance;

            if (!isAcademicRecord) {
                studentSelect.value = '';
            }

            if (isAttendance) {
                termSelect.value = '';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Load Students
        |--------------------------------------------------------------------------
        */

        function loadStudents() {

            const taId = classSelect.value;

            studentSelect.innerHTML =
                '<option value="">Loading students...</option>';

            studentSelect.disabled = true;

            if (!taId) {

                studentSelect.innerHTML =
                    '<option value="">Select a class first</option>';

                return;
            }

            fetch(`/teacher/grading-system/reports/students/${taId}`)

                .then(response => {

                    if (!response.ok) {
                        throw new Error('Failed to load students.');
                    }

                    return response.json();
                })

                .then(data => {

                    studentSelect.innerHTML =
                        '<option value="">Select a student</option>';

                    data.forEach(student => {

                        const option =
                            document.createElement('option');

                        option.value =
                            student.enrollment_id;

                        option.textContent =
                            `${student.name}${student.lrn ? ` (${student.lrn})` : ''}`;

                        studentSelect.appendChild(option);
                    });

                    updateFilterState();
                })

                .catch(error => {

                    console.error(error);

                    studentSelect.innerHTML =
                        '<option value="">Unable to load students</option>';

                    studentSelect.disabled = true;
                });
        }


        /*
        |--------------------------------------------------------------------------
        | Generate Report
        |--------------------------------------------------------------------------
        */

        function generateReport(type) {

            const taId = classSelect.value;
            const termId = termSelect.value;
            const studentId = studentSelect.value;

            if (!taId) {

                alert('Please select a class first.');

                return;
            }

            currentReportType = type;

            updateFilterState();


            /*
             * Student Academic Record requires
             * one selected student.
             */

            if (type === 'academic-record' && !studentId) {

                showPreviewMessage(
                    'Select a student first.',
                    'Choose a student from the Student filter to generate an individual academic record.'
                );

                return;
            }


            const params =
                new URLSearchParams();

            params.append(
                'report_type',
                type
            );


            /*
             * Term is used by grade-based
             * and at-risk reports.
             */

            if (termId && type !== 'attendance') {

                params.append(
                    'term_id',
                    termId
                );
            }


            /*
             * Student is only sent for
             * Student Academic Record.
             */

            if (
                type === 'academic-record' &&
                studentId
            ) {

                params.append(
                    'student_id',
                    studentId
                );
            }


            const endpoint =
                `/teacher/grading-system/reports/class-record/${taId}?${params.toString()}`;


            reportContent.innerHTML = `
                <div class="gs-chart-empty py-5 text-center">

                    <i class="fa-solid fa-spinner fa-spin gs-chart-empty-icon"></i>

                    <p class="mb-0">
                        Generating report...
                    </p>

                </div>
            `;

            reportPreview.classList.remove('d-none');


            fetch(endpoint)

                .then(response => {

                    if (!response.ok) {
                        throw new Error('Unable to generate report.');
                    }

                    return response.json();
                })

                .then(data => {

                    currentReportData = data;

                    renderReport(
                        type,
                        data
                    );
                })

                .catch(error => {

                    console.error(error);

                    currentReportData = null;

                    reportContent.innerHTML = `
                        <div class="gs-chart-empty py-5 text-center">

                            <i class="fa-solid fa-circle-exclamation gs-chart-empty-icon"></i>

                            <p class="mb-1">
                                Unable to generate the report.
                            </p>

                            <span class="small text-muted">
                                Please try again or check the selected class.
                            </span>

                        </div>
                    `;

                    reportPreview.classList.remove('d-none');
                });
        }


        /*
        |--------------------------------------------------------------------------
        | Report Header
        |--------------------------------------------------------------------------
        */

        function renderReportHeader(
            title,
            data,
            description = ''
        ) {

            return `
                <div class="gs-report-preview-meta mb-4">

                    <p class="gs-report-preview-title mb-1">
                        ${escapeHtml(title)}
                    </p>

                    <p class="gs-report-preview-subtitle mb-1">

                        Grade ${displayValue(data.grade_level)}
                        -
                        ${displayValue(data.section)}
                        &middot;
                        ${displayValue(data.subject)}

                    </p>

                    ${
                        description
                            ? `
                                <p class="text-muted small mb-0">
                                    ${escapeHtml(description)}
                                </p>
                              `
                            : ''
                    }

                </div>
            `;
        }


        /*
        |--------------------------------------------------------------------------
        | No Data
        |--------------------------------------------------------------------------
        */

        function renderNoData(colspan = 5) {

            return `
                <tr>

                    <td colspan="${colspan}"
                        class="text-center text-muted py-5">

                        <i class="fa-solid fa-folder-open mb-2 d-block"></i>

                        No report data available.

                    </td>

                </tr>
            `;
        }


        /*
        |--------------------------------------------------------------------------
        | Class Grade Report
        |--------------------------------------------------------------------------
        */

        function renderClassGradeReport(data) {

            const terms =
                data.terms || [];

            const rows =
                data.rows || [];


            const termHeaders =
                terms
                    .map(term => `
                        <th class="text-center">
                            ${escapeHtml(term)}
                        </th>
                    `)
                    .join('');


            const rowsHtml =
                rows
                    .map(row => {

                        const termCells =
                            (row.terms || [])
                                .map(grade => `
                                    <td class="text-center">
                                        ${displayValue(grade)}
                                    </td>
                                `)
                                .join('');


                        return `
                            <tr>

                                <td class="fw-medium">
                                    ${displayValue(row.name)}
                                </td>

                                ${termCells}

                                <td class="text-center fw-bold">
                                    ${displayValue(row.final)}
                                </td>

                            </tr>
                        `;
                    })
                    .join('');


            return `

                ${renderReportHeader(
                    'Class Grade Report',
                    data,
                    'Summary of learner grades for the selected class.'
                )}

                <div class="table-panel">

                    <div class="table-responsive">

                        <table class="table table-sm table-bordered mb-0">

                            <thead>

                                <tr>

                                    <th>
                                        Learner Name
                                    </th>

                                    ${termHeaders}

                                    <th class="text-center">
                                        Final Grade
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                ${
                                    rowsHtml ||
                                    renderNoData(terms.length + 2)
                                }

                            </tbody>

                        </table>

                    </div>

                </div>
            `;
        }


        /*
        |--------------------------------------------------------------------------
        | Student Academic Record
        |--------------------------------------------------------------------------
        */

        function renderAcademicRecord(data) {

            const rows =
                data.rows || [];

            const student =
                rows.length
                    ? rows[0]
                    : null;

            if (!student) {

                return `

                    ${renderReportHeader(
                        'Student Academic Record',
                        data
                    )}

                    <div class="gs-chart-empty py-5 text-center">

                        <i class="fa-solid fa-user-slash gs-chart-empty-icon"></i>

                        <p class="mb-1">
                            No student academic record found.
                        </p>

                        <span class="small text-muted">
                            Check the selected student and class.
                        </span>

                    </div>
                `;
            }


            const terms =
                data.terms || [];

            const grades =
                student.terms || [];


            const termRows =
                terms
                    .map((term, index) => {

                        const grade =
                            grades[index] ?? null;

                        return `
                            <tr>

                                <td class="fw-medium">
                                    ${escapeHtml(term)}
                                </td>

                                <td class="text-center">
                                    ${displayValue(grade)}
                                </td>

                            </tr>
                        `;
                    })
                    .join('');


            return `

                ${renderReportHeader(
                    'Student Academic Record',
                    data,
                    'Individual academic performance and grade history.'
                )}

                <div class="row g-3 mb-4">

                    <div class="col-12 col-md-8">

                        <div class="gs-stat-card h-100">

                            <p class="gs-stat-label mb-1">
                                Student
                            </p>

                            <p class="fw-semibold mb-0">
                                ${displayValue(student.name)}
                            </p>

                        </div>

                    </div>

                    <div class="col-12 col-md-4">

                        <div class="gs-stat-card h-100">

                            <p class="gs-stat-label mb-1">
                                Final Grade
                            </p>

                            <p class="gs-stat-value mb-0">
                                ${displayValue(student.final)}
                            </p>

                        </div>

                    </div>

                </div>


                <div class="table-panel">

                    <div class="table-responsive">

                        <table class="table table-sm table-bordered mb-0">

                            <thead>

                                <tr>

                                    <th>
                                        Term
                                    </th>

                                    <th class="text-center">
                                        Grade
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                ${
                                    termRows ||
                                    renderNoData(2)
                                }

                            </tbody>

                        </table>

                    </div>

                </div>
            `;
        }


        /*
        |--------------------------------------------------------------------------
        | Grade Submission Report
        |--------------------------------------------------------------------------
        */

        function renderGradeSubmissionReport(data) {

            const rows =
                data.rows || [];


            const totalSubmitted =
                rows.reduce(
                    (sum, row) =>
                        sum + Number(row.submitted || 0),
                    0
                );


            const totalMissing =
                rows.reduce(
                    (sum, row) =>
                        sum + Number(row.missing || 0),
                    0
                );


            const totalRecords =
                rows.reduce(
                    (sum, row) =>
                        sum + Number(row.total || 0),
                    0
                );


            const overallCompletion =
                totalRecords > 0
                    ? ((totalSubmitted / totalRecords) * 100).toFixed(1)
                    : '0.0';


            const rowsHtml =
                rows
                    .map(row => {

                        const completion =
                            Number(row.completion || 0);


                        return `
                            <tr>

                                <td class="fw-medium">
                                    ${displayValue(row.term)}
                                </td>

                                <td class="text-center">
                                    ${Number(row.submitted || 0)}
                                </td>

                                <td class="text-center">
                                    ${Number(row.missing || 0)}
                                </td>

                                <td class="text-center">
                                    ${Number(row.total || 0)}
                                </td>

                                <td class="text-center fw-semibold">
                                    ${completion.toFixed(1)}%
                                </td>

                            </tr>
                        `;
                    })
                    .join('');


            return `

                ${renderReportHeader(
                    'Grade Submission Report',
                    data,
                    'Overview of grade completion and submission status.'
                )}


                <div class="row g-3 mb-4">

                    <div class="col-12 col-md-4">

                        <div class="gs-stat-card h-100">

                            <p class="gs-stat-label mb-1">
                                Submitted
                            </p>

                            <p class="gs-stat-value gs-stat-present mb-0">
                                ${totalSubmitted}
                            </p>

                        </div>

                    </div>


                    <div class="col-12 col-md-4">

                        <div class="gs-stat-card h-100">

                            <p class="gs-stat-label mb-1">
                                Missing
                            </p>

                            <p class="gs-stat-value gs-stat-danger mb-0">
                                ${totalMissing}
                            </p>

                        </div>

                    </div>


                    <div class="col-12 col-md-4">

                        <div class="gs-stat-card h-100">

                            <p class="gs-stat-label mb-1">
                                Overall Completion
                            </p>

                            <p class="gs-stat-value mb-0">
                                ${overallCompletion}%
                            </p>

                        </div>

                    </div>

                </div>


                <div class="table-panel">

                    <div class="table-responsive">

                        <table class="table table-sm table-bordered mb-0">

                            <thead>

                                <tr>

                                    <th>
                                        Term
                                    </th>

                                    <th class="text-center">
                                        Submitted
                                    </th>

                                    <th class="text-center">
                                        Missing
                                    </th>

                                    <th class="text-center">
                                        Total
                                    </th>

                                    <th class="text-center">
                                        Completion
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                ${
                                    rowsHtml ||
                                    renderNoData(5)
                                }

                            </tbody>

                        </table>

                    </div>

                </div>
            `;
        }


        /*
        |--------------------------------------------------------------------------
        | At-Risk Monitoring Report
        |--------------------------------------------------------------------------
        */

        function renderAtRiskReport(data) {

            const rows =
                data.rows || [];


            const highRisk =
                rows.filter(
                    row => row.risk_level === 'High'
                ).length;


            const moderateRisk =
                rows.filter(
                    row => row.risk_level === 'Moderate'
                ).length;


            const rowsHtml =
                rows
                    .map(row => {

                        const riskLevel =
                            row.risk_level || 'Unknown';


                        let badgeClass =
                            'gs-badge-neutral';

                        if (riskLevel === 'High') {
                            badgeClass = 'gs-badge-danger';
                        } else if (riskLevel === 'Moderate') {
                            badgeClass = 'gs-badge-warning';
                        }


                        return `
                            <tr>

                                <td class="fw-medium">
                                    ${displayValue(row.name)}
                                </td>

                                <td class="text-center">
                                    ${displayValue(row.risk_score)}
                                </td>

                                <td class="text-center">

                                    <span class="gs-badge ${badgeClass}">
                                        ${escapeHtml(riskLevel)}
                                    </span>

                                </td>

                                <td class="text-center">
                                    ${
                                        row.attendance_rate !== null &&
                                        row.attendance_rate !== undefined
                                            ? `${escapeHtml(row.attendance_rate)}%`
                                            : '—'
                                    }
                                </td>

                                <td>
                                    ${formatIndicators(row.indicators)}
                                </td>

                            </tr>
                        `;
                    })
                    .join('');


            return `

                ${renderReportHeader(
                    'At-Risk Monitoring Report',
                    data,
                    'Learners with identified academic risk indicators.'
                )}


                <div class="row g-3 mb-4">

                    <div class="col-12 col-md-4">

                        <div class="gs-stat-card h-100">

                            <p class="gs-stat-label mb-1">
                                Students at Risk
                            </p>

                            <p class="gs-stat-value mb-0">
                                ${rows.length}
                            </p>

                        </div>

                    </div>


                    <div class="col-12 col-md-4">

                        <div class="gs-stat-card gs-stat-card-danger h-100">

                            <p class="gs-stat-label gs-stat-label-danger mb-1">
                                High Risk
                            </p>

                            <p class="gs-stat-value gs-stat-danger mb-0">
                                ${highRisk}
                            </p>

                        </div>

                    </div>


                    <div class="col-12 col-md-4">

                        <div class="gs-stat-card h-100">

                            <p class="gs-stat-label mb-1">
                                Moderate Risk
                            </p>

                            <p class="gs-stat-value mb-0">
                                ${moderateRisk}
                            </p>

                        </div>

                    </div>

                </div>


                <div class="table-panel">

                    <div class="table-responsive">

                        <table class="table table-sm table-bordered mb-0">

                            <thead>

                                <tr>

                                    <th>
                                        Learner Name
                                    </th>

                                    <th class="text-center">
                                        Risk Score
                                    </th>

                                    <th class="text-center">
                                        Risk Level
                                    </th>

                                    <th class="text-center">
                                        Attendance Rate
                                    </th>

                                    <th>
                                        Risk Indicators
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                ${
                                    rowsHtml ||
                                    renderNoData(5)
                                }

                            </tbody>

                        </table>

                    </div>

                </div>
            `;
        }


        /*
        |--------------------------------------------------------------------------
        | Attendance Report
        |--------------------------------------------------------------------------
        */

        function renderAttendanceReport(data) {

            const rows =
                data.rows || [];


            const totalStudents =
                rows.length;


            const totalPresentDays =
                rows.reduce(
                    (sum, row) =>
                        sum + Number(row.present_days || 0),
                    0
                );


            const averagePresentDays =
                totalStudents > 0
                    ? (totalPresentDays / totalStudents).toFixed(1)
                    : '0.0';


            const rowsHtml =
                rows
                    .map(row => {

                        return `
                            <tr>

                                <td class="fw-medium">
                                    ${displayValue(row.name)}
                                </td>

                                <td class="text-center">
                                    ${Number(row.present_days || 0)}
                                </td>

                                <td>
                                    ${formatDateTime(row.last_attendance)}
                                </td>

                            </tr>
                        `;
                    })
                    .join('');


            return `

                ${renderReportHeader(
                    'Attendance Report',
                    data,
                    'Attendance summary for learners in the selected class.'
                )}


                <div class="row g-3 mb-4">

                    <div class="col-12 col-md-6">

                        <div class="gs-stat-card h-100">

                            <p class="gs-stat-label mb-1">
                                Total Learners
                            </p>

                            <p class="gs-stat-value mb-0">
                                ${totalStudents}
                            </p>

                        </div>

                    </div>


                    <div class="col-12 col-md-6">

                        <div class="gs-stat-card h-100">

                            <p class="gs-stat-label mb-1">
                                Average Present Days
                            </p>

                            <p class="gs-stat-value mb-0">
                                ${averagePresentDays}
                            </p>

                        </div>

                    </div>

                </div>


                <div class="table-panel">

                    <div class="table-responsive">

                        <table class="table table-sm table-bordered mb-0">

                            <thead>

                                <tr>

                                    <th>
                                        Learner Name
                                    </th>

                                    <th class="text-center">
                                        Present Days
                                    </th>

                                    <th>
                                        Last Attendance
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                ${
                                    rowsHtml ||
                                    renderNoData(3)
                                }

                            </tbody>

                        </table>

                    </div>

                </div>
            `;
        }


        /*
        |--------------------------------------------------------------------------
        | Main Report Renderer
        |--------------------------------------------------------------------------
        */

        function renderReport(type, data) {

            let html = '';


            switch (type) {

                case 'class-grade':

                    html =
                        renderClassGradeReport(data);

                    break;


                case 'academic-record':

                    html =
                        renderAcademicRecord(data);

                    break;


                case 'grade-submission':

                    html =
                        renderGradeSubmissionReport(data);

                    break;


                case 'at-risk':

                    html =
                        renderAtRiskReport(data);

                    break;


                case 'attendance':

                    html =
                        renderAttendanceReport(data);

                    break;


                default:

                    html = `
                        <div class="gs-chart-empty py-5 text-center">

                            <i class="fa-solid fa-file-circle-question gs-chart-empty-icon"></i>

                            <p class="mb-0">
                                Unknown report type.
                            </p>

                        </div>
                    `;
            }


            reportContent.innerHTML =
                html;

            reportPreview.classList.remove('d-none');


            reportPreview.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }


        /*
        |--------------------------------------------------------------------------
        | Report Type Selection
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll('.gs-report-type-card')
            .forEach(card => {

                card.addEventListener('click', () => {

                    document
                        .querySelectorAll('.gs-report-type-card')
                        .forEach(item => {

                            item.classList.remove(
                                'gs-report-type-active'
                            );
                        });


                    card.classList.add(
                        'gs-report-type-active'
                    );


                    currentReportType =
                        card.dataset.type;


                    updateFilterState();


                    generateReport(
                        currentReportType
                    );

                });

            });


        /*
        |--------------------------------------------------------------------------
        | Class Change
        |--------------------------------------------------------------------------
        */

        classSelect.addEventListener(
            'change',
            () => {

                currentReportData = null;

                currentReportType = null;

                reportPreview.classList.add(
                    'd-none'
                );


                document
                    .querySelectorAll('.gs-report-type-card')
                    .forEach(card => {

                        card.classList.remove(
                            'gs-report-type-active'
                        );

                    });


                studentSelect.innerHTML =
                    '<option value="">Loading students...</option>';


                loadStudents();

                updateFilterState();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Term Change
        |--------------------------------------------------------------------------
        */

        termSelect.addEventListener(
            'change',
            () => {

                if (currentReportType) {

                    generateReport(
                        currentReportType
                    );

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Student Change
        |--------------------------------------------------------------------------
        */

        studentSelect.addEventListener(
            'change',
            () => {

                if (
                    currentReportType ===
                    'academic-record'
                ) {

                    generateReport(
                        currentReportType
                    );

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Print
        |--------------------------------------------------------------------------
        */

        document
            .getElementById('printReportBtn')
            .addEventListener(
                'click',
                () => {

                    if (!currentReportData) {
                        return;
                    }

                    window.print();

                }
            );


        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        document
            .getElementById('exportPdfBtn')
            .addEventListener(
                'click',
                () => {

                    if (!currentReportData) {
                        return;
                    }

                    window.print();

                }
            );


        /*
        |--------------------------------------------------------------------------
        | CSV Helper
        |--------------------------------------------------------------------------
        */

        function csvEscape(value) {

            if (
                value === null ||
                value === undefined
            ) {
                return '';
            }

            return `"${String(value)
                .replace(/"/g, '""')}"`;
        }


        /*
        |--------------------------------------------------------------------------
        | Excel / CSV Export
        |--------------------------------------------------------------------------
        */

        document
            .getElementById('exportExcelBtn')
            .addEventListener(
                'click',
                () => {

                    if (!currentReportData) {
                        return;
                    }


                    const data =
                        currentReportData;

                    const type =
                        currentReportType;

                    let csv = '';



                    /*
                    |--------------------------------------------------------------------------
                    | Class Grade
                    |--------------------------------------------------------------------------
                    */

                    if (type === 'class-grade') {

                        csv =
                            [
                                [
                                    'Learner Name',
                                    ...(data.terms || []),
                                    'Final Grade'
                                ]
                            ]
                            .map(row =>
                                row.map(csvEscape).join(',')
                            )
                            .join('\n');


                        (data.rows || [])
                            .forEach(row => {

                                csv += '\n';

                                csv += [
                                    row.name,
                                    ...(row.terms || []),
                                    row.final
                                ]
                                .map(csvEscape)
                                .join(',');

                            });
                    }



                    /*
                    |--------------------------------------------------------------------------
                    | Academic Record
                    |--------------------------------------------------------------------------
                    */

                    else if (type === 'academic-record') {

                        const student =
                            data.rows?.[0];

                        csv =
                            [
                                [
                                    'Student',
                                    student?.name || ''
                                ],
                                [
                                    'Term',
                                    'Grade'
                                ]
                            ]
                            .map(row =>
                                row.map(csvEscape).join(',')
                            )
                            .join('\n');


                        (data.terms || [])
                            .forEach((term, index) => {

                                csv += '\n';

                                csv += [
                                    term,
                                    student?.terms?.[index] ?? ''
                                ]
                                .map(csvEscape)
                                .join(',');

                            });


                        csv += '\n';

                        csv += [
                            'Final Grade',
                            student?.final ?? ''
                        ]
                        .map(csvEscape)
                        .join(',');
                    }



                    /*
                    |--------------------------------------------------------------------------
                    | Grade Submission
                    |--------------------------------------------------------------------------
                    */

                    else if (type === 'grade-submission') {

                        csv =
                            [
                                [
                                    'Term',
                                    'Submitted',
                                    'Missing',
                                    'Total',
                                    'Completion'
                                ]
                            ]
                            .map(row =>
                                row.map(csvEscape).join(',')
                            )
                            .join('\n');


                        (data.rows || [])
                            .forEach(row => {

                                csv += '\n';

                                csv += [
                                    row.term,
                                    row.submitted,
                                    row.missing,
                                    row.total,
                                    `${row.completion ?? 0}%`
                                ]
                                .map(csvEscape)
                                .join(',');

                            });
                    }



                    /*
                    |--------------------------------------------------------------------------
                    | At-Risk
                    |--------------------------------------------------------------------------
                    */

                    else if (type === 'at-risk') {

                        csv =
                            [
                                [
                                    'Learner Name',
                                    'Risk Score',
                                    'Risk Level',
                                    'Attendance Rate',
                                    'Risk Indicators'
                                ]
                            ]
                            .map(row =>
                                row.map(csvEscape).join(',')
                            )
                            .join('\n');


                        (data.rows || [])
                            .forEach(row => {

                                let indicators = '';

                                if (
                                    Array.isArray(
                                        row.indicators
                                    )
                                ) {

                                    indicators =
                                        row.indicators
                                            .map(indicator => {

                                                if (
                                                    typeof indicator ===
                                                    'string'
                                                ) {
                                                    return indicator;
                                                }

                                                if (
                                                    indicator &&
                                                    typeof indicator ===
                                                    'object'
                                                ) {

                                                    return (
                                                        indicator.label ??
                                                        indicator.name ??
                                                        indicator.indicator ??
                                                        indicator.title ??
                                                        JSON.stringify(indicator)
                                                    );
                                                }

                                                return '';
                                            })
                                            .filter(Boolean)
                                            .join('; ');

                                } else {

                                    indicators =
                                        row.indicators || '';

                                }


                                csv += '\n';

                                csv += [
                                    row.name,
                                    row.risk_score,
                                    row.risk_level,
                                    row.attendance_rate !== null &&
                                    row.attendance_rate !== undefined
                                        ? `${row.attendance_rate}%`
                                        : '',
                                    indicators
                                ]
                                .map(csvEscape)
                                .join(',');

                            });
                    }



                    /*
                    |--------------------------------------------------------------------------
                    | Attendance
                    |--------------------------------------------------------------------------
                    */

                    else if (type === 'attendance') {

                        csv =
                            [
                                [
                                    'Learner Name',
                                    'Present Days',
                                    'Last Attendance'
                                ]
                            ]
                            .map(row =>
                                row.map(csvEscape).join(',')
                            )
                            .join('\n');


                        (data.rows || [])
                            .forEach(row => {

                                csv += '\n';

                                csv += [
                                    row.name,
                                    row.present_days,
                                    row.last_attendance
                                        ? formatDateTime(row.last_attendance)
                                        : ''
                                ]
                                .map(csvEscape)
                                .join(',');

                            });
                    }


                    const blob =
                        new Blob(
                            [csv],
                            {
                                type:
                                    'text/csv;charset=utf-8;'
                            }
                        );


                    const link =
                        document.createElement('a');


                    const fileName =
                        type
                            ? `${type.replace(/-/g, '_')}_report.csv`
                            : 'academic_report.csv';


                    link.href =
                        URL.createObjectURL(blob);

                    link.download =
                        fileName;


                    document.body.appendChild(
                        link
                    );

                    link.click();

                    document.body.removeChild(
                        link
                    );


                    URL.revokeObjectURL(
                        link.href
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | Initial Student Load
        |--------------------------------------------------------------------------
        */

        if (classSelect.value) {

            loadStudents();

        }


        updateFilterState();

    </script>

</x-layouts.teacher>