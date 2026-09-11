<x-layouts.teacher>

<x-slot name="pageName">
    <span class="page-title-icon">
        <i class="fa-solid fa-clipboard-user"></i>
        Student Profile
    </span>
</x-slot>

<x-slot name="subtitle">
    Academic record, grades, and performance overview for this student.
</x-slot>

@include('pov.teacher.my-classes.partials.common.grading-breadcrumb', [
    'crumbs' => [
        [
            'label' => 'Student Profile',
            'url'   => route('teacher.grading-system.student-profile'),
        ],
        [
            'label' => $enrollment->student->full_name,
            'url'   => '#',
        ],
    ]
])

{{-- ============================================================
SECTION 1 — STUDENT HEADER
============================================================ --}}

<div class="gs-panel mb-3">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">

        <div class="d-flex align-items-center gap-3">

            <span class="gs-profile-avatar">
                {{ strtoupper(substr($enrollment->student->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($enrollment->student->last_name ?? '?', 0, 1)) }}
            </span>

            <div>

                <p
                    class="text-muted mb-1"
                    style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;"
                >
                    Student Academic Profile
                </p>

                <p class="gs-panel-title mb-1">
                    {{ $enrollment->student->full_name }}
                </p>

                <div
                    class="d-flex flex-wrap align-items-center gap-2"
                    style="font-size: 0.78rem;"
                >

                    <span class="text-muted">
                        Grade {{ $enrollment->section->grade_level }}
                        &middot;
                        {{ $enrollment->section->name }}
                    </span>

                    <span class="text-muted">
                        &middot;
                    </span>

                    <span class="text-muted">
                        No:
                        <span class="fw-semibold text-dark">
                            {{ $enrollment->student->student_number ?? 'N/A' }}
                        </span>
                    </span>

                    <span class="text-muted">
                        &middot;
                    </span>

                    <span class="text-muted">
                        LRN:
                        <span class="fw-semibold text-dark">
                            {{ $enrollment->student->lrn ?? '—' }}
                        </span>
                    </span>

                    <span class="text-muted">
                        &middot;
                    </span>

                    <span class="text-muted">
                        SY 2025-2026
                    </span>

                </div>

            </div>

        </div>

        <div class="d-flex align-items-center gap-2">
            <x-ui.backButton />
        </div>

    </div>

</div>


{{-- ============================================================
SECTION 2 — ACADEMIC SUMMARY
============================================================ --}}

<div class="row g-3 mb-3">

    {{-- Overall Average --}}
    <div class="col-6 col-md">

        <div class="gs-stat-card h-100">

            <p class="gs-stat-label mb-1">
                Overall Average
            </p>

            <p class="gs-stat-value mb-0">
                {{ $overallAvg !== null ? number_format($overallAvg, 1) : '—' }}
            </p>

        </div>

    </div>


    {{-- Enrolled Subjects --}}
    <div class="col-6 col-md">

        <div class="gs-stat-card h-100">

            <p class="gs-stat-label mb-1">
                Subjects
            </p>

            <p class="gs-stat-value mb-0">
                {{ $subjects->count() }}
            </p>

        </div>

    </div>


    {{-- Passing Subjects --}}
    <div class="col-6 col-md">

        <div class="gs-stat-card gs-stat-card-success h-100">

            <p class="gs-stat-label gs-stat-label-success mb-1">
                Passing
            </p>

            <p class="gs-stat-value gs-stat-present mb-0">

                {{ $subjects->filter(
                    fn($s) =>
                        $s->average !== null
                        && $s->average >= 75
                )->count() }}

                <span
                    class="text-muted fw-normal"
                    style="font-size: 0.82rem;"
                >
                    / {{ $subjects->count() }}
                </span>

            </p>

        </div>

    </div>


    {{-- Missing Grades --}}
    <div class="col-6 col-md">

        <div class="gs-stat-card h-100">

            <p class="gs-stat-label mb-1">
                Missing Grades
            </p>

            <p class="gs-stat-value mb-0 {{ $missingGradesCount > 0 ? 'text-warning' : '' }}">
                {{ $missingGradesCount }}
            </p>

        </div>

    </div>


    {{-- Attendance Rate --}}
    <div class="col-12 col-md">

        <div class="gs-stat-card h-100">

            <p class="gs-stat-label mb-1">
                Attendance Rate
            </p>

            <p class="gs-stat-value mb-0">
                {{ $attendanceRate !== null
                    ? number_format($attendanceRate, 1) . '%'
                    : '—'
                }}
            </p>

        </div>

    </div>

</div>


{{-- ============================================================
SECTION 3 — GRADE SUMMARY
============================================================ --}}

<div class="gs-panel academic-record-card mb-3">

    <div class="profile-card-header">

        <div>

            <p class="gs-panel-title mb-1">
                Grade Summary
            </p>

            <p class="text-muted small mb-0">
                Final grades recorded across the student's subjects and terms.
            </p>

        </div>

        <div class="term-filter">

            <label for="termFilter">
                View:
            </label>

            <select
                id="termFilter"
                class="form-select form-select-sm"
            >

                <option value="all">
                    All Terms
                </option>

                <option value="Term 1">
                    Term 1
                </option>

                <option value="Term 2">
                    Term 2
                </option>

                <option value="Term 3">
                    Term 3
                </option>

            </select>

        </div>

    </div>


    <div class="academic-table-wrapper">

        <table class="academic-table">

            <thead>

                <tr>

                    <th style="padding-left: 18px;">
                        Subject
                    </th>

                    <th>
                        Grade Level
                    </th>

                    <th>
                        Section
                    </th>

                    <th>
                        Term
                    </th>

                    <th>
                        Grade
                    </th>

                    <th>
                        Status
                    </th>

                </tr>

            </thead>

            <tbody id="academicTableBody">

                @php
                    $hasGradeRecords = false;
                @endphp

                @foreach ($subjects as $subject)

                    @foreach ($subject->periods as $period)

                        @php
                            $hasGradeRecords = true;
                        @endphp

                        <tr
                            class="academic-row"
                            data-term="{{ $period->term_label }}"
                        >

                            <td style="padding-left: 18px;">

                                <div class="subject-cell">

                                    <span class="subject-icon">
                                        <i class="fa-solid fa-book-open"></i>
                                    </span>

                                    <strong>
                                        {{ $subject->subject_name }}
                                    </strong>

                                </div>

                            </td>

                            <td>
                                Grade {{ $subject->grade_level }}
                            </td>

                            <td>
                                {{ $subject->section_name }}
                            </td>

                            <td>

                                <span class="term-label">
                                    {{ $period->term_label }}
                                </span>

                            </td>

                            <td>

                                <strong
                                    class="grade-number {{ ($period->grade !== null && $period->grade < 75) ? 'text-danger' : '' }}"
                                >
                                    {{ $period->grade !== null
                                        ? number_format($period->grade, 1)
                                        : '—'
                                    }}
                                </strong>

                            </td>

                            <td>

                                @if ($period->grade !== null)

                                    @if ($period->grade >= 75)

                                        <span class="grade-status passing">
                                            Passing
                                        </span>

                                    @else

                                        <span class="grade-status failing">
                                            Below Passing
                                        </span>

                                    @endif

                                @else

                                    <span class="grade-status missing">
                                        Not Yet Graded
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                @endforeach


                @if (!$hasGradeRecords)

                    <tr>

                        <td
                            colspan="6"
                            class="empty-table"
                        >

                            <div class="empty-table-content">

                                <i class="fa-solid fa-folder-open"></i>

                                <strong>
                                    No grade records found
                                </strong>

                                <span>
                                    No final grades have been recorded for this student yet.
                                </span>

                            </div>

                        </td>

                    </tr>

                @endif

            </tbody>

        </table>


        <div
            id="noAcademicResults"
            class="academic-no-results d-none"
        >

            <i class="fa-solid fa-filter-circle-xmark"></i>

            <strong>
                No grades for this term
            </strong>

            <span>
                Try selecting a different term filter.
            </span>

        </div>

    </div>

</div>


{{-- ============================================================
SECTION 4 — GRADE SUMMARY DETAILS
============================================================ --}}

<div class="gs-panel mb-3">

    <div class="profile-card-header simple">

        <div>

            <p class="gs-panel-title mb-1">
                Grade Summary Details
            </p>

            <p class="text-muted small mb-0">
                Overview of the student's recorded final grades.
            </p>

        </div>

    </div>


    @php

        $totalRecordedEntries = $subjects
            ->flatMap->periods
            ->filter(
                fn($p) =>
                    $p->grade !== null
            )
            ->count();

        $subjectsWithRecordsCount = $subjects
            ->filter(
                fn($s) =>
                    $s->periods
                        ->contains(
                            fn($p) =>
                                $p->grade !== null
                        )
            )
            ->count();

        $totalExpectedGradeEntries =
            $subjects->count() * 3;

        $pendingGradeEntries = max(
            0,
            $totalExpectedGradeEntries
            - $totalRecordedEntries
        );

    @endphp


    <div style="padding: 4px 18px 14px;">

        <div class="gs-rules-section">

            <div class="gs-rules-row">

                <span class="gs-rules-label">
                    Recorded Grade Entries
                </span>

                <span class="gs-rules-value">
                    {{ $totalRecordedEntries }}
                    <span
                        class="text-muted fw-normal"
                        style="font-size: 0.76rem;"
                    >
                        / {{ $totalExpectedGradeEntries }}
                    </span>
                </span>

            </div>

        </div>


        <div class="gs-rules-section">

            <div class="gs-rules-row">

                <span class="gs-rules-label">
                    Subjects with Records
                </span>

                <span class="gs-rules-value">
                    {{ $subjectsWithRecordsCount }}
                    /
                    {{ $subjects->count() }}
                </span>

            </div>

        </div>


        <div class="gs-rules-section gs-rules-section-last">

            <div class="gs-rules-row">

                <span class="gs-rules-label">
                    Pending Grade Entries
                </span>

                <span class="gs-rules-value {{ $pendingGradeEntries > 0 ? 'text-warning' : '' }}">
                    {{ $pendingGradeEntries }}
                </span>

            </div>

        </div>

    </div>

</div>


{{-- ============================================================
SECTION 5 — ASSESSMENT SUMMARY
============================================================ --}}

<div class="gs-panel mb-3">

    <div class="profile-card-header simple">

        <div>

            <p class="gs-panel-title mb-1">
                Assessment Summary
            </p>

            <p class="text-muted small mb-0">
                Individual assessments and the student's recorded scores.
            </p>

        </div>

    </div>


    {{-- Assessment Overview --}}

    <div class="row g-2 px-3 pt-2 pb-3">

        <div class="col-6 col-md-3">

            <div class="gs-stat-card h-100">

                <p class="gs-stat-label mb-1">
                    Total Assessments
                </p>

                <p class="gs-stat-value mb-0">
                    {{ $assessmentCounts['total'] ?? 0 }}
                </p>

            </div>

        </div>


        <div class="col-6 col-md-3">

            <div class="gs-stat-card gs-stat-card-success h-100">

                <p class="gs-stat-label gs-stat-label-success mb-1">
                    Scores Recorded
                </p>

                <p class="gs-stat-value gs-stat-present mb-0">
                    {{ $assessmentCounts['recorded'] ?? 0 }}
                </p>

            </div>

        </div>


        <div class="col-6 col-md-3">

            <div class="gs-stat-card h-100">

                <p class="gs-stat-label mb-1">
                    Pending Scores
                </p>

                <p class="gs-stat-value mb-0 {{ ($assessmentCounts['pending'] ?? 0) > 0 ? 'text-warning' : '' }}">
                    {{ $assessmentCounts['pending'] ?? 0 }}
                </p>

            </div>

        </div>


        <div class="col-6 col-md-3">

            <div class="gs-stat-card h-100">

                <p class="gs-stat-label mb-1">
                    Assessment Types
                </p>

                <p class="gs-stat-value mb-0">
                    {{ collect([
                        $assessmentCounts['written'] ?? 0,
                        $assessmentCounts['performance'] ?? 0,
                        $assessmentCounts['term_assessment'] ?? 0
                    ])->filter(fn($count) => $count > 0)->count() }}
                </p>

            </div>

        </div>

    </div>


    @if ($assessmentSummary->isNotEmpty())

        <div class="academic-table-wrapper">

            <table class="academic-table">

                <thead>

                    <tr>

                        <th style="padding-left: 18px;">
                            Assessment
                        </th>

                        <th>
                            Subject
                        </th>

                        <th>
                            Type
                        </th>

                        <th>
                            Term
                        </th>

                        <th>
                            Score
                        </th>

                        <th>
                            Result
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach ($assessmentSummary as $assessment)

                        <tr>

                            {{-- Assessment --}}

                            <td style="padding-left: 18px;">

                                <div class="subject-cell">

                                    <span class="subject-icon">
                                        <i class="fa-solid fa-clipboard-check"></i>
                                    </span>

                                    <div>

                                        <strong>
                                            {{ $assessment->title }}
                                        </strong>

                                        @if ($assessment->assessment_date)

                                            <div
                                                class="text-muted"
                                                style="font-size: 0.7rem;"
                                            >
                                                {{ \Carbon\Carbon::parse($assessment->assessment_date)->format('M d, Y') }}
                                            </div>

                                        @endif

                                    </div>

                                </div>

                            </td>


                            {{-- Subject --}}

                            <td>
                                {{ $assessment->subject_name }}
                            </td>


                            {{-- Type --}}

                            <td>

                                @if ($assessment->category_key === 'written')

                                    <span class="term-label">
                                        Written Work
                                    </span>

                                @elseif ($assessment->category_key === 'performance')

                                    <span class="term-label">
                                        Performance Task
                                    </span>

                                @else

                                    <span class="term-label">
                                        Term Assessment
                                    </span>

                                @endif

                            </td>


                            {{-- Term --}}

                            <td>

                                <span class="term-label">
                                    {{ $assessment->term_label }}
                                </span>

                            </td>


                            {{-- Score --}}

                            <td>

                                @if ($assessment->has_score)

                                    <strong>
                                        {{ rtrim(rtrim(number_format($assessment->score, 2), '0'), '.') }}
                                        /
                                        {{ $assessment->total_items }}
                                    </strong>

                                    @if ($assessment->percentage !== null)

                                        <div
                                            class="text-muted"
                                            style="font-size: 0.7rem;"
                                        >
                                            {{ number_format($assessment->percentage, 1) }}%
                                        </div>

                                    @endif

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- Result --}}

                            <td>

                                @if ($assessment->has_score)

                                    @if ($assessment->percentage >= 75)

                                        <span class="grade-status passing">
                                            Recorded
                                        </span>

                                    @else

                                        <span class="grade-status failing">
                                            Recorded
                                        </span>

                                    @endif

                                @else

                                    <span class="grade-status missing">
                                        No Score
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    @else

        <div class="simple-empty-state">

            <i class="fa-regular fa-clipboard"></i>

            <strong>
                No assessments recorded
            </strong>

            <span>
                No active assessments have been created for this student's assigned subjects yet.
            </span>

        </div>

    @endif

</div>


{{-- ============================================================
SECTION 6 — PERFORMANCE PROGRESSION
============================================================ --}}

<div class="performance-layout mb-3">

    {{-- Chart --}}

    <div class="profile-card performance-card">

        <div class="profile-card-header simple">

            <div>

                <p class="gs-panel-title mb-1">
                    Performance Progression
                </p>

                <p class="text-muted small mb-0">
                    Term-by-term grade trend based on recorded subjects.
                </p>

            </div>

        </div>


        <div
            class="grade-chart-wrapper"
            style="max-height: 240px;"
        >

            @if (
                collect($gradeHistory)
                    ->contains(
                        fn($item) =>
                            $item['average'] !== null
                    )
            )

                <canvas id="gradeChart"></canvas>

            @else

                <div class="chart-empty-state">

                    <i class="fa-solid fa-chart-line fa-lg"></i>

                    <strong>
                        No grade history available
                    </strong>

                    <span>
                        A trend chart will appear once grades are recorded across multiple terms.
                    </span>

                </div>

            @endif

        </div>

    </div>


    {{-- Performance Summary --}}

    <div class="profile-card">

        <div class="profile-card-header simple">

            <div>

                <p class="gs-panel-title mb-1">
                    Performance Summary
                </p>

            </div>

        </div>


        @php

            $validAverages = $subjects
                ->pluck('average')
                ->filter(
                    fn($value) =>
                        $value !== null
                );

            $highestGrade = $validAverages->isNotEmpty()
                ? $validAverages->max()
                : null;

            $lowestGrade = $validAverages->isNotEmpty()
                ? $validAverages->min()
                : null;

            $subjectsWithAverage = $subjects
                ->filter(
                    fn($s) =>
                        $s->average !== null
                );

            $passingRate = $subjects->count() > 0
                ? (
                    $subjects
                        ->filter(
                            fn($s) =>
                                $s->average !== null
                                && $s->average >= 75
                        )
                        ->count()
                    / $subjects->count()
                ) * 100
                : 0;

        @endphp


        <div style="padding: 4px 18px 14px;">

            <div class="gs-rules-section">

                <div class="gs-rules-row">

                    <span class="gs-rules-label">
                        Highest Subject Grade
                    </span>

                    <span class="gs-rules-value text-success">

                        {{ $highestGrade !== null
                            ? number_format($highestGrade, 1)
                            : '—'
                        }}

                    </span>

                </div>

            </div>


            <div class="gs-rules-section">

                <div class="gs-rules-row">

                    <span class="gs-rules-label">
                        Lowest Subject Grade
                    </span>

                    <span class="gs-rules-value {{ ($lowestGrade !== null && $lowestGrade < 75) ? 'text-danger' : '' }}">

                        {{ $lowestGrade !== null
                            ? number_format($lowestGrade, 1)
                            : '—'
                        }}

                    </span>

                </div>

            </div>


            <div class="gs-rules-section">

                <div class="gs-rules-row">

                    <span class="gs-rules-label">
                        Passing Subject Rate
                    </span>

                    <span class="gs-rules-value">
                        {{ number_format($passingRate, 0) }}%
                    </span>

                </div>

            </div>


            <div class="gs-rules-section gs-rules-section-last">

                <div class="gs-rules-row">

                    <span class="gs-rules-label">
                        Academic Standing
                    </span>

                    <span class="gs-badge {{ $belowPassingCount == 0 ? 'gs-badge-success' : 'gs-badge-warning' }}">

                        {{ $belowPassingCount == 0
                            ? 'Good Standing'
                            : $belowPassingCount . ' Concern(s)'
                        }}

                    </span>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ============================================================
SECTION 7 — ATTENDANCE HISTORY
============================================================ --}}

<div class="profile-card attendance-card mb-3">

    <div class="profile-card-header simple">

        <div>

            <p class="gs-panel-title mb-1">
                Attendance History
            </p>

            <p class="text-muted small mb-0">
                Recorded attendance based on QR scans and teacher verification.
            </p>

        </div>


        <div style="font-size: 0.78rem; text-align: right;">

            <span class="text-muted">
                Attendance Rate:
            </span>

            <span class="fw-semibold text-dark ms-1">
                {{ number_format($attendanceRate ?? 0, 1) }}%
            </span>

            <span class="mx-1 text-muted">
                &middot;
            </span>

            <span class="text-muted">
                Present:
            </span>

            <span class="fw-semibold text-dark ms-1">
                {{ $presentCount }}
            </span>

        </div>

    </div>


    @if ($attendanceHistory->isNotEmpty())

        <div class="academic-table-wrapper">

            <table class="academic-table">

                <thead>

                    <tr>

                        <th style="padding-left: 18px;">
                            Date
                        </th>

                        <th>
                            Time
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Remarks
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach ($attendanceHistory as $attendance)

                        @php

                            $attendanceDate = $attendance->date
                                ? \Carbon\Carbon::parse($attendance->date)
                                : null;

                            $attendanceTime = $attendance->scan_time
                                ? \Carbon\Carbon::parse($attendance->scan_time)
                                : null;

                            $status = strtolower(
                                $attendance->status ?? ''
                            );

                            $statusClass = match ($status) {

                                'present' => 'passing',

                                'late' => 'warning',

                                'absent',
                                'not_in_classroom' => 'failing',

                                'excused' => 'info',

                                default => 'missing',

                            };

                        @endphp


                        <tr>

                            {{-- Date --}}

                            <td style="padding-left: 18px;">

                                <div class="attendance-date">

                                    <i class="fa-regular fa-calendar"></i>

                                    {{ $attendanceDate
                                        ? $attendanceDate->format('M d, Y')
                                        : '—'
                                    }}

                                </div>

                            </td>


                            {{-- Time --}}

                            <td>

                                @if ($attendanceTime)

                                    {{ $attendanceTime->format('h:i A') }}

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- Status --}}

                            <td>

                                <span class="grade-status {{ $statusClass }}">

                                    @if ($status === 'present')

                                        <i class="fa-solid fa-circle-check me-1"></i>

                                    @elseif ($status === 'late')

                                        <i class="fa-solid fa-clock me-1"></i>

                                    @elseif ($status === 'absent')

                                        <i class="fa-solid fa-circle-xmark me-1"></i>

                                    @elseif ($status === 'excused')

                                        <i class="fa-solid fa-circle-info me-1"></i>

                                    @elseif ($status === 'not_in_classroom')

                                        <i class="fa-solid fa-location-dot me-1"></i>

                                    @else

                                        <i class="fa-solid fa-circle-question me-1"></i>

                                    @endif

                                    {{ $attendance->status }}

                                </span>

                            </td>


                            {{-- Remarks --}}

                            <td>

                                @if (!empty($attendance->remarks))

                                    <span
                                        class="attendance-remarks"
                                        title="{{ $attendance->remarks }}"
                                    >
                                        {{ $attendance->remarks }}
                                    </span>

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    @else

        <div class="simple-empty-state">

            <i class="fa-regular fa-calendar-xmark"></i>

            <strong>
                No attendance records
            </strong>

            <span>
                No QR attendance scans or teacher verification records were found for this student.
            </span>

        </div>

    @endif

</div>


{{-- ============================================================
SECTION 8 — ACADEMIC NOTES
============================================================ --}}

<div class="gs-panel">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <p class="gs-panel-title mb-1">
                Academic Notes
            </p>
            <p class="text-muted small mb-0">
                General academic observation notes recorded by teachers.
            </p>
        </div>
        <button type="button"
                class="btn btn-sm btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#addAcademicNoteModal">
            <i class="fa-solid fa-plus me-1"></i> Add Note
        </button>
    </div>

    @if (isset($academicNotes) && $academicNotes->isNotEmpty())
        <div class="risk-list">
            @foreach ($academicNotes as $note)
                <div class="risk-item d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-start gap-2 flex-grow-1">
                        <div class="risk-item-icon">
                            <i class="fa-solid fa-note-sticky"></i>
                        </div>
                        <div class="risk-item-content">
                            <strong>
                                {{ $note->teacher->full_name ?? ($note->teacher->user ? $note->teacher->user->first_name . ' ' . $note->teacher->user->last_name : 'Teacher') }}
                                <span class="text-muted fw-normal" style="font-size: 0.7rem;">
                                    &middot; {{ $note->created_at->format('M d, Y h:i A') }}
                                    @if ($note->updated_at && $note->updated_at->gt($note->created_at))
                                        <em>(edited)</em>
                                    @endif
                                </span>
                            </strong>
                            <p class="mb-0 mt-1" style="white-space: pre-line;">{{ $note->note }}</p>
                        </div>
                    </div>

                    @if ($note->teacher_id === auth()->user()?->teacher?->id)
                        <div class="d-flex align-items-center gap-1 ms-2 flex-shrink-0">
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary py-1 px-2 btn-edit-academic-note"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editAcademicNoteModal"
                                    data-note-id="{{ $note->id }}"
                                    data-note-text="{{ $note->note }}"
                                    data-update-url="{{ route('teacher.student-profile.academic-notes.update', ['enrollmentId' => $enrollment->id, 'noteId' => $note->id]) }}"
                                    title="Edit Note">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger py-1 px-2 btn-delete-academic-note"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteAcademicNoteModal"
                                    data-delete-url="{{ route('teacher.student-profile.academic-notes.destroy', ['enrollmentId' => $enrollment->id, 'noteId' => $note->id]) }}"
                                    title="Delete Note">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="simple-empty-state">
            <i class="fa-regular fa-comment-dots"></i>
            <strong>
                No academic notes recorded
            </strong>
            <span>
                General academic observation notes will appear here once added.
            </span>
        </div>
    @endif

</div>

{{-- Add Academic Note Modal --}}
<div class="modal fade" id="addAcademicNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('teacher.student-profile.academic-notes.store', $enrollment->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size: 0.95rem;">Add Academic Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="addAcademicNoteText" class="form-label small text-muted">Observation / Note</label>
                    <textarea name="note" id="addAcademicNoteText" class="form-control" rows="4" maxlength="2000" required placeholder="e.g. Student shows great enthusiasm in class participation; recommended for peer mentoring."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Note</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Edit Academic Note Modal --}}
<div class="modal fade" id="editAcademicNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="editAcademicNoteForm" action="">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size: 0.95rem;">Edit Academic Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="editAcademicNoteText" class="form-label small text-muted">Observation / Note</label>
                    <textarea name="note" id="editAcademicNoteText" class="form-control" rows="4" maxlength="2000" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Update Note</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Delete Academic Note Confirmation Modal --}}
<div class="modal fade" id="deleteAcademicNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form method="POST" id="deleteAcademicNoteForm" action="">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size: 0.95rem;">Delete Academic Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-0">Are you sure you want to delete this academic note? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </div>
            </div>
        </form>
    </div>
</div>


{{-- ============================================================
SCRIPTS
============================================================ --}}

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Grade Chart
    |--------------------------------------------------------------------------
    */

    const chartEl =
        document.getElementById('gradeChart');

    if (
        chartEl &&
        typeof Chart !== 'undefined'
    ) {

        const labels = @json(
            array_column($gradeHistory, 'term')
        );

        const values = @json(
            array_column($gradeHistory, 'average')
        );

        if (
            labels.length &&
            values.length
        ) {

            new Chart(
                chartEl.getContext('2d'),
                {
                    type: 'line',

                    data: {

                        labels: labels,

                        datasets: [{

                            label: 'Average Grade',

                            data: values,

                            borderColor: '#2438b9',

                            backgroundColor: 'rgba(36, 56, 185, 0.07)',

                            tension: 0.35,

                            fill: true,

                            pointRadius: 4,

                            pointHoverRadius: 6,

                            pointBackgroundColor: '#2438b9'

                        }]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        plugins: {

                            legend: {
                                display: false
                            }

                        },

                        scales: {

                            y: {

                                min: 60,

                                max: 100,

                                ticks: {

                                    stepSize: 10,

                                    font: {
                                        size: 11
                                    }

                                },

                                grid: {
                                    color: 'rgba(0,0,0,0.05)'
                                }

                            },

                            x: {

                                grid: {
                                    display: false
                                },

                                ticks: {

                                    font: {
                                        size: 11
                                    }

                                }

                            }

                        }

                    }

                }
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Term Filter — Grade Summary
    |--------------------------------------------------------------------------
    */

    const termFilter =
        document.getElementById('termFilter');

    const rows =
        document.querySelectorAll('.academic-row');

    const noResults =
        document.getElementById('noAcademicResults');


    if (termFilter) {

        termFilter.addEventListener(
            'change',
            function () {

                const selectedTerm =
                    this.value;

                let visibleCount = 0;


                rows.forEach(
                    function (row) {

                        const matches =
                            selectedTerm === 'all'
                            || row.dataset.term === selectedTerm;


                        row.style.display =
                            matches
                                ? ''
                                : 'none';


                        if (matches) {
                            visibleCount++;
                        }

                    }
                );


                if (noResults) {

                    noResults.classList.toggle(
                        'd-none',
                        !(
                            rows.length > 0
                            && visibleCount === 0
                        )
                    );

                }

            }
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Academic Note Modal Handlers
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.btn-edit-academic-note').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = document.getElementById('editAcademicNoteForm');
            const textarea = document.getElementById('editAcademicNoteText');
            if (form && textarea) {
                form.action = this.dataset.updateUrl;
                textarea.value = this.dataset.noteText;
            }
        });
    });

    document.querySelectorAll('.btn-delete-academic-note').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const form = document.getElementById('deleteAcademicNoteForm');
            if (form) {
                form.action = this.dataset.deleteUrl;
            }
        });
    });

});

</script>

</x-layouts.teacher>