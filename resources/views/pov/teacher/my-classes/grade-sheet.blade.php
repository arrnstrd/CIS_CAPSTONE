<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-table"></i>
            Grading System
        </span>
    </x-slot>
    <x-slot name="subtitle">
        <span class="page-title-subtitle">{{ $ta->section->name }} · {{ $ta->subject->name }}</span>
    </x-slot>

<div class="mb-3 d-flex align-items-center gap-2">
    <a href="{{ route('teacher.grading-system.dashboard') }}" class="gs-back-btn" title="Back to My Classes">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <span class="gs-panel-title">Grade Sheet</span>
</div>

{{-- =========================================================
     TOP ACTIONS / STATS
     ========================================================= --}}
<div class="row g-3 mb-3">

    <div class="col-6 col-md-3">
        <div class="gs-stat-card d-flex align-items-center gap-3">
            <span class="gs-stat-icon gs-stat-icon-neutral">
                <i class="fa-solid fa-users"></i>
            </span>

            <div>
                <p class="gs-stat-label">Students</p>
                <p class="gs-stat-value">{{ $totalStudents }}</p>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="gs-stat-card d-flex align-items-center gap-3">
            <span class="gs-stat-icon gs-stat-icon-neutral">
                <i class="fa-solid fa-chart-simple"></i>
            </span>

            <div>
                <p class="gs-stat-label">Grade Completion</p>

                <p class="gs-stat-value" id="statGradeCompletion">
                    {{ $gradeCompletionPercent !== null ? $gradeCompletionPercent . '%' : '—' }}
                </p>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="gs-stat-card d-flex align-items-center gap-3">
            <span class="gs-stat-icon gs-stat-icon-neutral">
                <i class="fa-solid fa-star-half-stroke"></i>
            </span>

            <div>
                <p class="gs-stat-label">Class Average</p>

                <p class="gs-stat-value" id="statClassAverage">
                    {{ $classAverage !== null ? $classAverage : '—' }}
                </p>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="gs-stat-card d-flex align-items-center gap-3">
            <span class="gs-stat-icon gs-stat-icon-neutral">
                <i class="fa-solid fa-check-double"></i>
            </span>

            <div>
                <p class="gs-stat-label">Passing Rate</p>

                <p class="gs-stat-value" id="statPassingRate">
                    {{ $passingRate !== null ? $passingRate . '%' : '—' }}
                </p>
            </div>
        </div>
    </div>

</div>


<div class="gd-layout">

    <div class="gd-content">

        {{-- =========================================================
             HEADER + ACTIONS
             ========================================================= --}}
        <div class="d-flex justify-content-between align-items-center mb-3">

            <div>
                <p class="gs-panel-title mb-0">
                    Grade {{ $ta->section->grade_level }} - {{ $ta->section->name }}
                </p>

                <p class="text-muted small mb-0">
                    {{ $ta->subject->name }}
                </p>
            </div>


            <div class="d-flex align-items-center gap-2">

                {{-- IMPORT READY-MADE EXCEL GRADES --}}
                <a href="{{ route('teacher.grading-system.import-data', [
                    'teaching_assignment_id' => $ta->id,
                    'grading_period_id' => $selectedPeriodId
                ]) }}"
                   class="btn btn-primary btn-sm">

                    <i class="fa-solid fa-file-import me-1"></i>
                    Import Grades

                </a>


                {{-- ASSESSMENT LOG --}}
                <button type="button"
                        class="btn btn-outline-secondary btn-sm"
                        onclick="showAssessmentLog()">

                    <i class="fa-solid fa-list me-1"></i>
                    Assessment Log

                </button>

            </div>

        </div>


        {{-- =========================================================
             TERM TABS
             ========================================================= --}}
        <div class="gs-tab-bar mb-3">

            @foreach ($gradingPeriods as $gp)

                <a href="{{ route('teacher.grading-system.grade-sheet', [
                    'teachingAssignmentId' => $ta->id,
                    'grading_period_id' => $gp->id
                ]) }}"
                   class="gs-tab {{ $selectedPeriodId == $gp->id ? 'gs-tab-active' : '' }}">

                    Term {{ $gp->sequence }}

                </a>

            @endforeach

        </div>


        @php
            /*
             * =====================================================
             * ASSESSMENT COMPONENT
             * =====================================================
             *
             * The third component is EXAMINATIONS for all school levels.
             */

            $assessmentComponentKey = 'exam';


            /*
             * =====================================================
             * GROUP LEARNERS BY SEX
             * =====================================================
             */

            $maleRows = $rows->filter(function ($row) {
                $sex = strtolower(trim((string) ($row->sex ?? '')));

                return in_array($sex, [
                    'male',
                    'm',
                    'boy',
                    '1'
                ]);
            });


            $femaleRows = $rows->filter(function ($row) {
                $sex = strtolower(trim((string) ($row->sex ?? '')));

                return in_array($sex, [
                    'female',
                    'f',
                    'girl',
                    '2'
                ]);
            });


            $otherRows = $rows->reject(function ($row) {
                $sex = strtolower(trim((string) ($row->sex ?? '')));

                return in_array($sex, [
                    'male',
                    'm',
                    'boy',
                    '1',
                    'female',
                    'f',
                    'girl',
                    '2'
                ]);
            });


            /*
             * Keep the original ordering inside each group.
             */

            $groupedRows = collect();


            if ($maleRows->count() > 0) {

                $groupedRows = $groupedRows->merge(
                    $maleRows->map(function ($row) {
                        $row->display_sex_group = 'MALE';

                        return $row;
                    })
                );

            }


            if ($femaleRows->count() > 0) {

                $groupedRows = $groupedRows->merge(
                    $femaleRows->map(function ($row) {
                        $row->display_sex_group = 'FEMALE';

                        return $row;
                    })
                );

            }


            if ($otherRows->count() > 0) {

                $groupedRows = $groupedRows->merge(
                    $otherRows->map(function ($row) {
                        $row->display_sex_group = 'OTHER';

                        return $row;
                    })
                );

            }
        @endphp


        {{-- =========================================================
             GRADE SHEET
             ========================================================= --}}
        <div class="table-panel">

            {{-- TABLE HEADER / DESCRIPTION --}}
            <div class="d-flex justify-content-between align-items-center px-3 py-3 border-bottom">

                <div>

                    <h6 class="mb-0 fw-bold">
                        <i class="fa-solid fa-table me-2"></i>
                        Grade Sheet
                    </h6>

                    <small class="text-muted">

                        {{ $maleRows->count() }} Male ·
                        {{ $femaleRows->count() }} Female

                        @if ($otherRows->count() > 0)
                            · {{ $otherRows->count() }} Other / Unspecified
                        @endif

                    </small>

                </div>

            </div>


            {{-- LIVE ACTIVE SCORE CONTEXT BAR --}}
            <div id="gsActiveScoreContext" class="gs-active-context-bar py-2 px-3 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2" style="font-size: 0.8rem; background-color: #f8fafc; border-left: 4px solid #3b56c4;">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="badge text-white" style="font-size: 0.75rem; background-color: #3b56c4;">
                        <i class="fa-solid fa-user me-1"></i>
                        <span id="ctxStudentName">Assessment Guide: Additional assessments are listed in the Assessment Log. Student names appear when they have a recorded score.</span>
                    </span>
                    <span class="badge bg-secondary text-white" id="ctxCategoryBadge" style="display: none;"></span>
                    <span class="badge bg-dark text-white" id="ctxSlotBadge" style="display: none;"></span>
                    <span class="text-dark fw-medium" id="ctxAssessmentTitle" style="display: none;"></span>
                </div>
                <div class="text-muted small d-flex align-items-center gap-3">
                    <span><strong>Subject:</strong> {{ $ta->subject->name }}</span>
                    <span><strong>Section:</strong> Grade {{ $ta->section->grade_level }} - {{ $ta->section->name }}</span>
                    <span><strong>Term:</strong> Term {{ $gradingPeriods->firstWhere('id', $selectedPeriodId)?->sequence ?? '—' }}</span>
                    <span id="ctxHpsWrap" style="display: none;"><strong>Max (HPS):</strong> <span id="ctxHps" class="fw-bold text-dark">—</span></span>
                </div>
            </div>


            <div class="table-responsive">

                <table class="table table-bordered table-sm align-middle mb-0"
                       id="gradeSheetTable">

                    <thead>

                        {{-- =================================================
                             MAIN GROUP HEADER
                             ================================================= --}}
                        <tr>

                            <th rowspan="3"
                                class="align-middle fw-semibold text-dark learner-name-header"
                                style="width: 220px;">

                                Learner's Name

                            </th>


                            {{-- WRITTEN WORKS --}}
                            <th colspan="{{ $fixedSlots['written'] + 3 }}"
                                class="text-center gs-group-written gs-divider-written py-2">

                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <span class="fw-bold">{{ $categoryLabels['written'] }}</span>
                                    <button type="button"
                                            class="gs-add-col-btn gs-add-col"
                                            data-category="written"
                                            data-category-label="Written Works"
                                            data-slot-prefix="WW"
                                            data-used-slots="{{ $assessmentsByCategory['written']->count() }}"
                                            data-max-slots="{{ $fixedSlots['written'] }}"
                                            title="Add Written Work Assessment">
                                        <i class="fa-solid fa-plus me-1"></i>Add
                                    </button>
                                </div>

                            </th>


                            {{-- PERFORMANCE TASKS --}}
                            <th colspan="{{ $fixedSlots['performance'] + 3 }}"
                                class="text-center gs-group-performance gs-divider-performance py-2">

                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <span class="fw-bold">{{ $categoryLabels['performance'] }}</span>
                                    <button type="button"
                                            class="gs-add-col-btn gs-add-col"
                                            data-category="performance"
                                            data-category-label="Performance Tasks"
                                            data-slot-prefix="PT"
                                            data-used-slots="{{ $assessmentsByCategory['performance']->count() }}"
                                            data-max-slots="{{ $fixedSlots['performance'] }}"
                                            title="Add Performance Task Assessment">
                                        <i class="fa-solid fa-plus me-1"></i>Add
                                    </button>
                                </div>

                            </th>


                            {{-- EXAMINATIONS --}}
                            <th colspan="{{ $fixedSlots[$assessmentComponentKey] + 3 }}"
                                class="text-center gs-group-exam gs-divider-exam py-2">

                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <span class="fw-bold">{{ $categoryLabels[$assessmentComponentKey] ?? 'Examinations' }}</span>
                                    <button type="button"
                                            class="gs-add-col-btn gs-add-col"
                                            data-category="{{ $assessmentComponentKey }}"
                                            data-category-label="Examinations"
                                            data-slot-prefix="EX"
                                            data-used-slots="{{ $assessmentsByCategory[$assessmentComponentKey]->count() }}"
                                            data-max-slots="{{ $fixedSlots[$assessmentComponentKey] }}"
                                            title="Add Examination Assessment">
                                        <i class="fa-solid fa-plus me-1"></i>Add
                                    </button>
                                </div>

                            </th>


                            {{-- INITIAL --}}
                            <th rowspan="3"
                                class="align-middle text-center gs-divider-initial fw-semibold"
                                style="min-width: 90px;">

                                Initial Grade

                            </th>


                            {{-- TRANSMUTED --}}
                            <th rowspan="3"
                                class="align-middle text-center gs-divider-transmuted fw-semibold"
                                style="min-width: 90px;">

                                Transmuted

                            </th>


                            {{-- DESCRIPTOR --}}
                            <th rowspan="3"
                                class="align-middle text-center fw-semibold"
                                style="min-width: 100px;">

                                Descriptor

                            </th>

                        </tr>


                        {{-- =================================================
                             ASSESSMENT NAMES
                             ================================================= --}}
                        <tr>

                            {{-- WRITTEN --}}
                            @foreach ($assessmentsBySlot['written'] as $index => $assessment)

                                @php
                                    $slotLabel = $slotLabels['written'][$index]
                                        ?? ('WW' . ($index + 1));
                                @endphp

                                <th class="text-center small gs-group-written gs-assessment-header"
                                    title="{{ $assessment ? $assessment->title : $slotLabel }}">

                                    <div class="gs-assessment-slot">
                                        {{ $slotLabel }}
                                    </div>

                                    @if ($assessment)
                                        @php
                                            $writtenAssessmentData = [
                                                'id' => $assessment->id,
                                                'title' => $assessment->title,
                                                'total_items' => $assessment->total_items,
                                                'assessment_date' => $assessment->assessment_date ? $assessment->assessment_date->format('Y-m-d') : '',
                                                'description' => $assessment->description ?? '',
                                                'slot_label' => $slotLabel,
                                                'category_label' => 'Written Works',
                                            ];
                                        @endphp
                                        <div class="gs-assessment-title" title="{{ $assessment->title }}">
                                            {{ $assessment->title }}
                                        </div>
                                        <div class="gs-assessment-actions mt-1 d-flex justify-content-center gap-1">
                                            <button type="button"
                                                    class="gs-assessment-edit" title="Edit Assessment"
                                                    data-assessment="{{ json_encode($writtenAssessmentData) }}"
                                                    onclick="openEditAssessmentModal(JSON.parse(this.getAttribute('data-assessment')))">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button type="button"
                                                    class="gs-assessment-delete" title="Delete Assessment"
                                                    onclick="confirmDeleteAssessment({{ $assessment->id }}, '{{ addslashes($slotLabel) }}', '{{ addslashes($assessment->title) }}')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="gs-assessment-title text-muted" style="opacity: 0.35;">
                                            &mdash;
                                        </div>
                                    @endif

                                </th>

                            @endforeach


                            <th class="text-center small gs-group-written fw-semibold">
                                Total
                            </th>

                            <th class="text-center small gs-group-written fw-semibold">
                                PS
                            </th>

                            <th class="text-center small gs-group-written gs-divider-written fw-semibold">
                                WS
                            </th>


                            {{-- PERFORMANCE --}}
                            @foreach ($assessmentsBySlot['performance'] as $index => $assessment)

                                @php
                                    $slotLabel = $slotLabels['performance'][$index]
                                        ?? ('PT' . ($index + 1));
                                @endphp

                                <th class="text-center small gs-group-performance gs-assessment-header"
                                    title="{{ $assessment ? $assessment->title : $slotLabel }}">

                                    <div class="gs-assessment-slot">
                                        {{ $slotLabel }}
                                    </div>

                                    @if ($assessment)
                                        @php
                                            $performanceAssessmentData = [
                                                'id' => $assessment->id,
                                                'title' => $assessment->title,
                                                'total_items' => $assessment->total_items,
                                                'assessment_date' => $assessment->assessment_date ? $assessment->assessment_date->format('Y-m-d') : '',
                                                'description' => $assessment->description ?? '',
                                                'slot_label' => $slotLabel,
                                                'category_label' => 'Performance Tasks',
                                            ];
                                        @endphp
                                        <div class="gs-assessment-title" title="{{ $assessment->title }}">
                                            {{ $assessment->title }}
                                        </div>
                                        <div class="gs-assessment-actions mt-1 d-flex justify-content-center gap-1">
                                            <button type="button"
                                                    class="gs-assessment-edit" title="Edit Assessment"
                                                    data-assessment="{{ json_encode($performanceAssessmentData) }}"
                                                    onclick="openEditAssessmentModal(JSON.parse(this.getAttribute('data-assessment')))">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button type="button"
                                                    class="gs-assessment-delete" title="Delete Assessment"
                                                    onclick="confirmDeleteAssessment({{ $assessment->id }}, '{{ addslashes($slotLabel) }}', '{{ addslashes($assessment->title) }}')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="gs-assessment-title text-muted" style="opacity: 0.35;">
                                            &mdash;
                                        </div>
                                    @endif

                                </th>

                            @endforeach


                            <th class="text-center small gs-group-performance fw-semibold">
                                Total
                            </th>

                            <th class="text-center small gs-group-performance fw-semibold">
                                PS
                            </th>

                            <th class="text-center small gs-group-performance gs-divider-performance fw-semibold">
                                WS
                            </th>


                            {{-- EXAMINATIONS --}}
                            @foreach ($assessmentsBySlot[$assessmentComponentKey] as $index => $assessment)

                                @php
                                    $slotLabel = $slotLabels[$assessmentComponentKey][$index]
                                        ?? ('EX' . ($index + 1));
                                @endphp

                                <th class="text-center small gs-group-exam gs-assessment-header"
                                    title="{{ $assessment ? $assessment->title : $slotLabel }}">

                                    <div class="gs-assessment-slot">
                                        {{ $slotLabel }}
                                    </div>

                                    @if ($assessment)
                                        @php
                                            $examAssessmentData = [
                                                'id' => $assessment->id,
                                                'title' => $assessment->title,
                                                'total_items' => $assessment->total_items,
                                                'assessment_date' => $assessment->assessment_date ? $assessment->assessment_date->format('Y-m-d') : '',
                                                'description' => $assessment->description ?? '',
                                                'slot_label' => $slotLabel,
                                                'category_label' => 'Examinations',
                                            ];
                                        @endphp
                                        <div class="gs-assessment-title" title="{{ $assessment->title }}">
                                            {{ $assessment->title }}
                                        </div>
                                        <div class="gs-assessment-actions mt-1 d-flex justify-content-center gap-1">
                                            <button type="button"
                                                    class="gs-assessment-edit" title="Edit Assessment"
                                                    data-assessment="{{ json_encode($examAssessmentData) }}"
                                                    onclick="openEditAssessmentModal(JSON.parse(this.getAttribute('data-assessment')))">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button type="button"
                                                    class="gs-assessment-delete" title="Delete Assessment"
                                                    onclick="confirmDeleteAssessment({{ $assessment->id }}, '{{ addslashes($slotLabel) }}', '{{ addslashes($assessment->title) }}')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="gs-assessment-title text-muted" style="opacity: 0.35;">
                                            &mdash;
                                        </div>
                                    @endif

                                </th>

                            @endforeach


                            <th class="text-center small gs-group-exam fw-semibold">
                                Total
                            </th>

                            <th class="text-center small gs-group-exam fw-semibold">
                                PS
                            </th>

                            <th class="text-center small gs-group-exam gs-divider-exam fw-semibold">
                                WS
                            </th>

                        </tr>


                        {{-- =================================================
                             HPS
                             ================================================= --}}
                        <tr class="gs-hps-row">

                            {{-- WRITTEN --}}
                            @foreach ($assessmentsBySlot['written'] as $assessment)

                                <td class="text-center small gs-group-written">

                                    @if ($assessment)
                                        HPS: {{ $assessment->total_items }}
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif

                                </td>

                            @endforeach


                            <td class="text-center small gs-group-written fw-semibold">
                                HPS: {{ $assessmentsByCategory['written']->sum('total_items') }}
                            </td>

                            <td class="text-center small gs-group-written text-muted">
                                &mdash;
                            </td>

                            <td class="text-center small gs-group-written gs-divider-written text-muted">
                                &mdash;
                            </td>


                            {{-- PERFORMANCE --}}
                            @foreach ($assessmentsBySlot['performance'] as $assessment)

                                <td class="text-center small gs-group-performance">

                                    @if ($assessment)
                                        HPS: {{ $assessment->total_items }}
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif

                                </td>

                            @endforeach


                            <td class="text-center small gs-group-performance fw-semibold">
                                HPS: {{ $assessmentsByCategory['performance']->sum('total_items') }}
                            </td>

                            <td class="text-center small gs-group-performance text-muted">
                                &mdash;
                            </td>

                            <td class="text-center small gs-group-performance gs-divider-performance text-muted">
                                &mdash;
                            </td>


                            {{-- EXAMINATIONS --}}
                            @foreach ($assessmentsBySlot[$assessmentComponentKey] as $assessment)

                                <td class="text-center small gs-group-exam">

                                    @if ($assessment)
                                        HPS: {{ $assessment->total_items }}
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif

                                </td>

                            @endforeach


                            <td class="text-center small gs-group-exam fw-semibold">
                                HPS: {{ $assessmentsByCategory[$assessmentComponentKey]->sum('total_items') }}
                            </td>

                            <td class="text-center small gs-group-exam text-muted">
                                &mdash;
                            </td>

                            <td class="text-center small gs-group-exam gs-divider-exam text-muted">
                                &mdash;
                            </td>

                        </tr>

                    </thead>


                    <tbody>

                        @php
                            $currentGroup = null;
                        @endphp


                        @forelse ($groupedRows as $row)

                            {{-- =================================================
                                 SEX GROUP HIGHLIGHT
                                 ================================================= --}}
                            @if ($currentGroup !== $row->display_sex_group)

                                @php
                                    $currentGroup = $row->display_sex_group;
                                @endphp

                                <tr class="learner-group-row">

                                    <td colspan="{{ $fixedSlots['written']
                                        + $fixedSlots['performance']
                                        + $fixedSlots[$assessmentComponentKey]
                                        + 12 }}"
                                        class="learner-group-label">

                                        <span class="learner-group-label-inner">
                                            @if ($currentGroup === 'MALE')

                                                <i class="fa-solid fa-person me-2"></i>
                                                MALE

                                                <span class="learner-group-count">
                                                    {{ $maleRows->count() }}
                                                    learner{{ $maleRows->count() === 1 ? '' : 's' }}
                                                </span>

                                            @elseif ($currentGroup === 'FEMALE')

                                                <i class="fa-solid fa-person-dress me-2"></i>
                                                FEMALE

                                                <span class="learner-group-count">
                                                    {{ $femaleRows->count() }}
                                                    learner{{ $femaleRows->count() === 1 ? '' : 's' }}
                                                </span>

                                            @else

                                                <i class="fa-solid fa-users me-2"></i>
                                                OTHER / UNSPECIFIED

                                                <span class="learner-group-count">
                                                    {{ $otherRows->count() }}
                                                    learner{{ $otherRows->count() === 1 ? '' : 's' }}
                                                </span>

                                            @endif
                                        </span>

                                    </td>

                                </tr>

                            @endif


                            {{-- =================================================
                                 LEARNER ROW
                                 ================================================= --}}
                            <tr data-enrollment-id="{{ $row->enrollment_id }}">

                                {{-- LEARNER NAME --}}
                                <td class="learner-name-cell">

                                    <span class="learner-name">
                                        {{ $row->student_name }}
                                    </span>

                                </td>


                                {{-- =================================================
                                     WRITTEN WORKS
                                     ================================================= --}}
                                @foreach ($assessmentsBySlot['written'] as $index => $assessment)
                                    @php $slotLabel = $slotLabels['written'][$index] ?? ('WW' . ($index + 1)); @endphp

                                    <td class="text-center p-1 gs-group-written">

                                        @if ($assessment)

                                            <input type="number"
                                                   min="0"
                                                   max="{{ $assessment->total_items }}"
                                                   step="0.01"
                                                   class="form-control form-control-sm gs-score-input text-center"
                                                   style="width: 70px; display: inline-block;"
                                                   data-assessment-id="{{ $assessment->id }}"
                                                   data-enrollment-id="{{ $row->enrollment_id }}"
                                                   data-student-name="{{ $row->student_name }}"
                                                   data-category="written"
                                                   data-category-label="Written Works"
                                                   data-slot-label="{{ $slotLabel }}"
                                                   data-assessment-title="{{ $assessment->title }}"
                                                   data-total-items="{{ $assessment->total_items }}"
                                                   title="Student: {{ $row->student_name }}&#10;Written Work: {{ $assessment->title }} ({{ $slotLabel }})&#10;Max Score (HPS): {{ $assessment->total_items }}"
                                                   value="{{ $row->scores[$assessment->id]->score ?? '' }}">

                                        @else

                                            <span class="text-muted">—</span>

                                        @endif

                                    </td>

                                @endforeach


                                <td class="text-center gs-group-written gs-summary-cell"
                                    data-component="written"
                                    data-field="total">

                                    {{ $row->component_summaries['written']['total'] }}

                                </td>


                                <td class="text-center gs-group-written gs-summary-cell"
                                    data-component="written"
                                    data-field="ps">

                                    {{ $row->component_summaries['written']['ps'] ?? '—' }}

                                </td>


                                <td class="text-center gs-group-written gs-divider-written gs-summary-cell"
                                    data-component="written"
                                    data-field="ws">

                                    {{ $row->component_summaries['written']['ws'] ?? '—' }}

                                </td>


                                {{-- =================================================
                                     PERFORMANCE TASKS
                                     ================================================= --}}
                                @foreach ($assessmentsBySlot['performance'] as $index => $assessment)
                                    @php $slotLabel = $slotLabels['performance'][$index] ?? ('PT' . ($index + 1)); @endphp

                                    <td class="text-center p-1 gs-group-performance">

                                        @if ($assessment)

                                            <input type="number"
                                                   min="0"
                                                   max="{{ $assessment->total_items }}"
                                                   step="0.01"
                                                   class="form-control form-control-sm gs-score-input text-center"
                                                   style="width: 70px; display: inline-block;"
                                                   data-assessment-id="{{ $assessment->id }}"
                                                   data-enrollment-id="{{ $row->enrollment_id }}"
                                                   data-student-name="{{ $row->student_name }}"
                                                   data-category="performance"
                                                   data-category-label="Performance Tasks"
                                                   data-slot-label="{{ $slotLabel }}"
                                                   data-assessment-title="{{ $assessment->title }}"
                                                   data-total-items="{{ $assessment->total_items }}"
                                                   title="Student: {{ $row->student_name }}&#10;Performance Task: {{ $assessment->title }} ({{ $slotLabel }})&#10;Max Score (HPS): {{ $assessment->total_items }}"
                                                   value="{{ $row->scores[$assessment->id]->score ?? '' }}">

                                        @else

                                            <span class="text-muted">—</span>

                                        @endif

                                    </td>

                                @endforeach


                                <td class="text-center gs-group-performance gs-summary-cell"
                                    data-component="performance"
                                    data-field="total">

                                    {{ $row->component_summaries['performance']['total'] }}

                                </td>


                                <td class="text-center gs-group-performance gs-summary-cell"
                                    data-component="performance"
                                    data-field="ps">

                                    {{ $row->component_summaries['performance']['ps'] ?? '—' }}

                                </td>


                                <td class="text-center gs-group-performance gs-divider-performance gs-summary-cell"
                                    data-component="performance"
                                    data-field="ws">

                                    {{ $row->component_summaries['performance']['ws'] ?? '—' }}

                                </td>


                                {{-- EXAMINATIONS --}}
                                @foreach ($assessmentsBySlot[$assessmentComponentKey] as $index => $assessment)
                                    @php $slotLabel = $slotLabels[$assessmentComponentKey][$index] ?? ('EX' . ($index + 1)); @endphp

                                    <td class="text-center p-1 gs-group-exam">

                                        @if ($assessment)

                                            <input type="number"
                                                   min="0"
                                                   max="{{ $assessment->total_items }}"
                                                   step="0.01"
                                                   class="form-control form-control-sm gs-score-input text-center"
                                                   style="width: 70px; display: inline-block;"
                                                   data-assessment-id="{{ $assessment->id }}"
                                                   data-enrollment-id="{{ $row->enrollment_id }}"
                                                   data-student-name="{{ $row->student_name }}"
                                                   data-category="{{ $assessmentComponentKey }}"
                                                   data-category-label="Examinations"
                                                   data-slot-label="{{ $slotLabel }}"
                                                   data-assessment-title="{{ $assessment->title }}"
                                                   data-total-items="{{ $assessment->total_items }}"
                                                   title="Student: {{ $row->student_name }}&#10;Examination: {{ $assessment->title }} ({{ $slotLabel }})&#10;Max Score (HPS): {{ $assessment->total_items }}"
                                                   value="{{ $row->scores[$assessment->id]->score ?? '' }}">

                                        @else

                                            <span class="text-muted">—</span>

                                        @endif

                                    </td>

                                @endforeach


                                <td class="text-center gs-group-exam gs-summary-cell"
                                    data-component="{{ $assessmentComponentKey }}"
                                    data-field="total">

                                    {{ $row->component_summaries[$assessmentComponentKey]['total'] ?? ($row->component_summaries['exam']['total'] ?? ($row->component_summaries['quarterly']['total'] ?? '—')) }}

                                </td>


                                <td class="text-center gs-group-exam gs-summary-cell"
                                    data-component="{{ $assessmentComponentKey }}"
                                    data-field="ps">

                                    {{ $row->component_summaries[$assessmentComponentKey]['ps'] ?? ($row->component_summaries['exam']['ps'] ?? ($row->component_summaries['quarterly']['ps'] ?? '—')) }}

                                </td>


                                <td class="text-center gs-group-exam gs-divider-exam gs-summary-cell"
                                    data-component="{{ $assessmentComponentKey }}"
                                    data-field="ws">

                                    {{ $row->component_summaries[$assessmentComponentKey]['ws'] ?? ($row->component_summaries['exam']['ws'] ?? ($row->component_summaries['quarterly']['ws'] ?? '—')) }}

                                </td>


                                {{-- INITIAL --}}
                                <td class="text-center gs-divider-initial gs-initial-cell">

                                    {{ $row->initial_grade ?? '—' }}

                                </td>


                                {{-- TRANSMUTED --}}
                                <td class="text-center gs-transmuted-cell gs-divider-transmuted fw-bold">

                                    {{ $row->transmuted_grade ?? '—' }}

                                </td>


                                {{-- DESCRIPTOR --}}
                                <td class="text-center gs-descriptor-cell">

                                    {{ $row->descriptor ?? '—' }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="{{ $fixedSlots['written']
                                    + $fixedSlots['performance']
                                    + $fixedSlots[$assessmentComponentKey]
                                    + 12 }}"
                                    class="text-center text-muted py-4">

                                    No learners found.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     GROUP HIGHLIGHT STYLES
     ========================================================= --}}
<style>

    .learner-group-row td {
        padding: 9px 14px !important;
        border-left: 0 !important;
        border-right: 0 !important;
    }


    .learner-group-label {
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        background: #f5f7fa;
        color: #495057;
        border-top: 2px solid #dee2e6 !important;
        border-bottom: 1px solid #dee2e6 !important;
    }


    .learner-group-label-inner {
        position: sticky;
        left: 14px;
        display: inline-flex;
        align-items: center;
        z-index: 3;
    }


    .learner-group-count {
        margin-left: 8px;
        font-size: 0.72rem;
        font-weight: 500;
        letter-spacing: 0;
        color: #6c757d;
    }


    .learner-name-cell {
        position: sticky;
        left: 0;
        z-index: 5;
        width: 220px;
        min-width: 220px;
        max-width: 220px;
        padding: 8px 14px !important;
        background: #ffffff !important;
        white-space: nowrap;
        font-weight: 400 !important;
        font-size: 0.85rem;
        color: #2d3748;
        vertical-align: middle;
    }


    .learner-name {
        display: inline-block;
        font-weight: 400 !important;
        color: #2d3748;
        letter-spacing: 0.01em;
    }


    .learner-group-row + tr .learner-name-cell {
        border-top: 0 !important;
    }


    #gradeSheetTable thead .learner-name-header {
        position: sticky;
        left: 0;
        z-index: 12;
        width: 220px;
        min-width: 220px;
        max-width: 220px;
        font-weight: 600;
        color: #ffffff !important;
        background-color: #3b56c4 !important;
    }


    #gradeSheetTable tbody .learner-group-row td {
        position: static !important;
    }


    .btn-import-grades {
        font-weight: 600;
    }


    /* Header Group Backgrounds - Clean Flat Solid Blue (#3b56c4) */
    #gradeSheetTable thead tr:first-child th.gs-group-written,
    #gradeSheetTable thead tr:first-child th.gs-group-performance,
    #gradeSheetTable thead tr:first-child th.gs-group-exam,
    #gradeSheetTable thead tr:first-child th.gs-group-quarterly,
    #gradeSheetTable thead tr:nth-child(2) th.gs-group-written,
    #gradeSheetTable thead tr:nth-child(2) th.gs-group-performance,
    #gradeSheetTable thead tr:nth-child(2) th.gs-group-exam,
    #gradeSheetTable thead tr:nth-child(2) th.gs-group-quarterly,
    #gradeSheetTable thead .gs-assessment-header {
        background: #3b56c4 !important;
        background-color: #3b56c4 !important;
        background-image: none !important;
        text-shadow: none !important;
        filter: none !important;
        color: #ffffff !important;
        border-color: #3048ad !important;
    }

    /* Subtle individual column dividers for all cells in #gradeSheetTable (except last column / Descriptor) */
    #gradeSheetTable th:not(:last-child),
    #gradeSheetTable td:not(:last-child) {
        box-shadow: inset -1px 0 0 #d5dbf0 !important;
    }

    #gradeSheetTable th:last-child,
    #gradeSheetTable td:last-child {
        box-shadow: none !important;
    }

    /* Column boundary dividers for all Table Header and Body cells (3px #b5c2ea) */
    #gradeSheetTable thead th:first-child,
    #gradeSheetTable thead th.gs-divider-written,
    #gradeSheetTable thead th.gs-divider-performance,
    #gradeSheetTable thead th.gs-divider-exam,
    #gradeSheetTable thead th.gs-divider-quarterly,
    #gradeSheetTable thead th.gs-divider-initial,
    #gradeSheetTable thead th.gs-divider-transmuted,
    #gradeSheetTable tbody td:first-child,
    #gradeSheetTable tbody td.gs-divider-written,
    #gradeSheetTable tbody td.gs-divider-performance,
    #gradeSheetTable tbody td.gs-divider-exam,
    #gradeSheetTable tbody td.gs-divider-quarterly,
    #gradeSheetTable tbody td.gs-divider-initial,
    #gradeSheetTable tbody td.gs-divider-transmuted {
        box-shadow: inset -3px 0 0 #b5c2ea !important;
    }

    #gradeSheetTable thead th::before,
    #gradeSheetTable thead th::after,
    .gs-assessment-header::before,
    .gs-assessment-header::after {
        display: none !important;
        content: none !important;
        box-shadow: none !important;
        background: none !important;
    }


    /* Assessment Header Columns */
    .gs-assessment-header {
        vertical-align: top !important;
        padding: 7px 4px !important;
        min-width: 86px;
        background: #3b56c4 !important;
        background-image: none !important;
        text-shadow: none !important;
        filter: none !important;
    }

    .gs-assessment-slot {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #ffffff !important;
        text-shadow: none !important;
        line-height: 1.1;
        margin-bottom: 2px;
    }

    .gs-assessment-title {
        font-size: 0.78rem;
        font-weight: 600;
        color: #ffffff !important;
        text-shadow: none !important;
        line-height: 1.2;
        max-width: 95px;
        margin: 0 auto;
        word-wrap: break-word;
        min-height: 1.2em;
    }

    .gs-add-col-btn {
        padding: 2px 7px;
        font-size: 0.72rem;
        font-weight: 600;
        border-radius: 4px;
        border: 1px solid rgba(255, 255, 255, 0.35);
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        vertical-align: middle;
        line-height: 1.2;
    }

    .gs-add-col-btn:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.5);
    }


    /* Assessment Header Action Buttons */
    .gs-assessment-actions .gs-assessment-edit,
    .gs-assessment-actions .gs-assessment-delete {
        width: 30px;
        height: 27px;
        padding: 0;
        border-radius: 6px;
        border: 1px solid;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        line-height: 1;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    /* Edit - Soft Blue Gray */
    .gs-assessment-actions .gs-assessment-edit {
        color: #526273;
        background: #eef3f7;
        border-color: #d6e0e8;
    }

    .gs-assessment-actions .gs-assessment-edit:hover {
        color: #3f5061;
        background: #e3ebf1;
        border-color: #c7d3dd;
    }

    /* Delete - Soft Rose */
    .gs-assessment-actions .gs-assessment-delete {
        color: #a85c66;
        background: #fdf1f2;
        border-color: #efd6d9;
    }

    .gs-assessment-actions .gs-assessment-delete:hover {
        color: #8f4650;
        background: #f9e5e7;
        border-color: #e5c0c4;
    }
</style>


@include('pov.teacher.my-classes.partials.grade-sheet.assessment-log-modal')
@include('pov.teacher.my-classes.partials.grade-sheet.edit-assessment-modal')
@include('pov.teacher.my-classes.partials.grade-sheet.delete-assessment-modal')
@include('pov.teacher.my-classes.partials.grade-sheet.add-assessment-modal')


<script>

    const csrfToken =
        document.querySelector(
            'meta[name="csrf-token"]'
        ).content;


    const teachingAssignmentId =
        {{ $ta->id }};


    const gradingPeriodId =
        {{ $selectedPeriodId }};


    const schoolLevel =
        '{{ $schoolLevel }}';


    // =========================================================
    // Assessment Log
    // =========================================================

    let cachedAssessmentLog = [];

    function showAssessmentLog() {

        fetch(
            `/teacher/grading-system/assessments/log?teaching_assignment_id=${teachingAssignmentId}&grading_period_id=${gradingPeriodId}`
        )

            .then(response => response.json())

            .then(data => {

                cachedAssessmentLog = data;
                renderAssessmentLog(data);

                const modal =
                    new bootstrap.Modal(
                        document.getElementById(
                            'assessmentLogModal'
                        )
                    );

                modal.show();

            })

            .catch(error => {

                console.error(
                    'Error loading assessment log:',
                    error
                );

                alert(
                    'Error loading assessment log'
                );

            });

    }


    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }


    function renderAssessmentLog(assessments) {

        const container =
            document.getElementById(
                'assessmentLogContent'
            );

        if (!container) return;

        // Backend now returns only extra (beyond-visible-column) assessments.
        if (!assessments || assessments.length === 0) {

            container.innerHTML = `
                <div class="text-center text-muted py-5 border rounded bg-light">
                    <i class="fa-solid fa-circle-check fa-2x mb-2 d-block text-success"></i>
                    <strong class="d-block mb-1 text-dark">No Additional Assessments</strong>
                    <span>All assessments for this term are within the visible Grade Sheet columns (WW1–WW5, PT1–PT5, EX1–EX3).</span>
                </div>
            `;

            return;

        }

        let html = '<div class="d-flex flex-column gap-3">';

        assessments.forEach(assessment => {

            const slotDisplay = assessment.slot_code
                ? `<span class="badge bg-dark font-monospace me-1">${escapeHtml(assessment.slot_code)}</span>`
                : '';

            const categoryLabel = getCategoryLabel(assessment.category);
            const scoredCount = assessment.scores_count ?? 0;
            const allStudents = assessment.all_students || [];

            let studentRowsHtml = '';
            if (allStudents.length > 0) {
                studentRowsHtml = allStudents.map(s => `
                    <tr class="align-middle">
                        <td class="py-1 px-2 text-dark">
                            <i class="fa-solid fa-user me-2 text-muted small"></i>
                            ${escapeHtml(s.student_name)}
                        </td>
                        <td class="py-1 px-2 text-end" style="width: 140px;">
                            <div class="input-group input-group-sm justify-content-end">
                                <input type="number"
                                       step="any"
                                       min="0"
                                       max="${assessment.total_items}"
                                       class="form-control form-control-sm text-center font-monospace extra-score-input"
                                       data-assessment-id="${assessment.id}"
                                       data-enrollment-id="${s.enrollment_id}"
                                       value="${s.score !== null ? s.score : ''}"
                                       placeholder="—"
                                       style="max-width: 80px;">
                                <button type="button"
                                        class="btn btn-outline-secondary btn-sm"
                                        title="Clear Score"
                                        onclick="this.previousElementSibling.value='';">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } else {
                studentRowsHtml = `<tr><td colspan="2" class="text-center text-muted small py-2">No students enrolled.</td></tr>`;
            }

            const editPayload = JSON.stringify({
                id: assessment.id,
                title: assessment.title,
                total_items: assessment.total_items,
                assessment_date: assessment.assessment_date || '',
                description: assessment.description || '',
                slot_label: assessment.slot_code || '',
                category_label: categoryLabel,
                is_extra: true
            });

            html += `
                <div class="p-3 border rounded bg-white shadow-sm" id="extraAssessmentCard_${assessment.id}">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2 pb-2 border-bottom">
                        <div>
                            <div class="fw-bold text-dark d-flex align-items-center gap-1 mb-1">
                                ${slotDisplay} <span class="fs-6">${escapeHtml(assessment.title)}</span>
                            </div>
                            <div class="text-muted small">
                                <span class="badge bg-secondary me-1">${categoryLabel}</span>
                                · <strong>HPS:</strong> ${assessment.total_items}
                                ${assessment.assessment_date ? ` · <strong>Date:</strong> ${escapeHtml(assessment.assessment_date)}` : ''}
                                ${assessment.description ? ` · <em>${escapeHtml(assessment.description)}</em>` : ''}
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button type="button"
                                    class="btn btn-outline-primary btn-sm py-1 px-2"
                                    onclick='openEditAssessmentModal(${editPayload})'>
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Assessment
                            </button>
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm py-1 px-2"
                                    onclick="confirmDeleteAssessment(${assessment.id}, '${escapeHtml(assessment.slot_code || 'Assessment')}', '${escapeHtml(assessment.title)}', true)">
                                <i class="fa-solid fa-trash me-1"></i> Delete Assessment
                            </button>
                        </div>
                    </div>

                    <div class="mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="text-muted small fw-semibold">
                                <i class="fa-solid fa-users me-1"></i> Student Scores (${scoredCount} of ${allStudents.length} scored):
                            </div>
                        </div>
                        <div class="table-responsive rounded border" style="max-height: 220px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0 small">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="py-1 px-2">Student</th>
                                        <th class="py-1 px-2 text-end" style="width: 140px;">Score (Max: ${assessment.total_items})</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${studentRowsHtml}
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                            <span class="small text-muted" id="extraScoreStatus_${assessment.id}"></span>
                            <button type="button"
                                    class="btn btn-primary btn-sm"
                                    id="saveExtraScoresBtn_${assessment.id}"
                                    onclick="saveExtraScores(${assessment.id})">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Scores
                            </button>
                        </div>
                    </div>
                </div>
            `;

        });

        html += '</div>';
        container.innerHTML = html;

    }


    function getCategoryLabel(category) {

        const labels = {

            'written':
                'Written Works',

            'performance':
                'Performance Tasks',

            'exam':
                'Examinations'

        };


        return labels[category] || category;

    }


    // =========================================================
    // Edit Assessment Modal & Submission
    // =========================================================

    let currentEditIsExtra = false;

    function openEditAssessmentModal(data) {
        currentEditIsExtra = !!data.is_extra;
        document.getElementById('editAssessmentId').value = data.id;
        document.getElementById('editModalSlotBadge').textContent = data.slot_label || '—';
        document.getElementById('editModalCategoryBadge').textContent = data.category_label || '—';
        document.getElementById('editAssessmentTitle').value = data.title || '';
        document.getElementById('editAssessmentTotal').value = data.total_items || 10;
        document.getElementById('editAssessmentDate').value = data.assessment_date || '';
        document.getElementById('editAssessmentDescription').value = data.description || '';
        document.getElementById('editAssessmentError').textContent = '';

        const modalEl = document.getElementById('editAssessmentModal');
        new bootstrap.Modal(modalEl).show();
    }

    async function submitEditAssessment() {
        const id = document.getElementById('editAssessmentId').value;
        const title = document.getElementById('editAssessmentTitle').value.trim();
        const total = document.getElementById('editAssessmentTotal').value;
        const date = document.getElementById('editAssessmentDate').value;
        const description = document.getElementById('editAssessmentDescription').value.trim();
        const errorEl = document.getElementById('editAssessmentError');
        const submitBtn = document.getElementById('editAssessmentSubmit');
        const spinner = document.getElementById('editAssessmentSpinner');
        const btnText = document.getElementById('editAssessmentBtnText');

        errorEl.textContent = '';

        if (!title || !total || total < 1) {
            errorEl.textContent = 'Please provide a title and a valid total items (HPS) >= 1.';
            return;
        }

        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Saving...';

        try {
            const res = await fetch(`/teacher/grading-system/grade-sheet/assessment/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    title,
                    total_items: total,
                    assessment_date: date || null,
                    description: description || null
                })
            });

            const data = await res.json();

            if (!res.ok) {
                errorEl.textContent = data.error || data.message || 'Failed to update assessment.';
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                btnText.textContent = 'Save Changes';
                return;
            }

            const modalEl = document.getElementById('editAssessmentModal');
            bootstrap.Modal.getInstance(modalEl)?.hide();

            if (currentEditIsExtra) {
                // Refresh assessment log
                showAssessmentLog();
            } else {
                location.reload();
            }
        } catch (e) {
            console.error(e);
            errorEl.textContent = 'Something went wrong. Please try again.';
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = 'Save Changes';
        }
    }


    // =========================================================
    // Delete Assessment Confirmation & Submission
    // =========================================================

    let currentDeleteIsExtra = false;

    function confirmDeleteAssessment(assessmentId, slotLabel, title, isExtra = false) {
        currentDeleteIsExtra = !!isExtra;
        document.getElementById('deleteAssessmentId').value = assessmentId;
        document.getElementById('deleteAssessmentPrompt').textContent = `Delete ${slotLabel} – ${title}?`;
        document.getElementById('deleteAssessmentError').textContent = '';

        const modalEl = document.getElementById('deleteAssessmentModal');
        new bootstrap.Modal(modalEl).show();
    }

    async function submitDeleteAssessment() {
        const id = document.getElementById('deleteAssessmentId').value;
        const errorEl = document.getElementById('deleteAssessmentError');
        const submitBtn = document.getElementById('deleteAssessmentSubmit');
        const spinner = document.getElementById('deleteAssessmentSpinner');
        const btnText = document.getElementById('deleteAssessmentBtnText');

        errorEl.textContent = '';
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Deleting...';

        try {
            const res = await fetch(`/teacher/grading-system/grade-sheet/assessment/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await res.json();

            if (!res.ok) {
                errorEl.textContent = data.error || data.message || 'Failed to delete assessment.';
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                btnText.textContent = 'Delete Assessment';
                return;
            }

            const modalEl = document.getElementById('deleteAssessmentModal');
            bootstrap.Modal.getInstance(modalEl)?.hide();

            if (currentDeleteIsExtra) {
                showAssessmentLog();
            } else {
                location.reload();
            }
        } catch (e) {
            console.error(e);
            errorEl.textContent = 'Something went wrong. Please try again.';
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = 'Delete Assessment';
        }
    }


    // =========================================================
    // Save Extra Assessment Scores
    // =========================================================

    async function saveExtraScores(assessmentId) {
        const card = document.getElementById(`extraAssessmentCard_${assessmentId}`);
        if (!card) return;

        const btn = document.getElementById(`saveExtraScoresBtn_${assessmentId}`);
        const statusEl = document.getElementById(`extraScoreStatus_${assessmentId}`);
        const inputs = card.querySelectorAll('.extra-score-input');

        const scores = [];
        inputs.forEach(input => {
            const enrollmentId = parseInt(input.dataset.enrollmentId);
            const val = input.value.trim();
            scores.push({
                enrollment_id: enrollmentId,
                score: val !== '' ? parseFloat(val) : null
            });
        });

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...';
        }
        if (statusEl) {
            statusEl.textContent = '';
            statusEl.className = 'small text-muted';
        }

        try {
            const res = await fetch(`/teacher/grading-system/grade-sheet/assessment/${assessmentId}/scores`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ scores })
            });

            const data = await res.json();

            if (!res.ok) {
                if (statusEl) {
                    statusEl.textContent = data.error || data.message || 'Failed to save scores.';
                    statusEl.className = 'small text-danger';
                }
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Scores';
                }
                return;
            }

            if (statusEl) {
                statusEl.textContent = 'Scores saved successfully!';
                statusEl.className = 'small text-success fw-semibold';
                setTimeout(() => {
                    if (statusEl) statusEl.textContent = '';
                }, 3000);
            }

            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Scores';
            }

            // Refresh cached log data
            fetch(`/teacher/grading-system/assessments/log?teaching_assignment_id=${teachingAssignmentId}&grading_period_id=${gradingPeriodId}`)
                .then(r => r.json())
                .then(d => {
                    cachedAssessmentLog = d;
                });

        } catch (err) {
            console.error(err);
            if (statusEl) {
                statusEl.textContent = 'Something went wrong. Please try again.';
                statusEl.className = 'small text-danger';
            }
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i> Save Scores';
            }
        }
    }


    // =========================================================
    // Add Assessment Column
    // =========================================================

    document
        .querySelectorAll('.gs-add-col')
        .forEach(btn => {

            btn.addEventListener(
                'click',
                () => {

                    const category = btn.dataset.category;
                    const categoryLabel = btn.dataset.categoryLabel || getCategoryLabel(category);
                    const slotPrefix = btn.dataset.slotPrefix || (category === 'written' ? 'WW' : (category === 'performance' ? 'PT' : 'EX'));
                    const usedSlots = parseInt(btn.dataset.usedSlots || 0);
                    const maxSlots = parseInt(btn.dataset.maxSlots || (category === 'exam' ? 3 : 5));

                    document.getElementById('addColumnCategory').value = category;
                    document.getElementById('modalCategoryBadge').textContent = categoryLabel;

                    const modalSlotStatusAlert = document.getElementById('modalSlotStatusAlert');
                    const modalSlotStatusText = document.getElementById('modalSlotStatusText');
                    const modalFormFields = document.getElementById('modalFormFields');
                    const addColumnSubmitBtn = document.getElementById('addColumnSubmit');
                    const addColumnBtnText = document.getElementById('addColumnBtnText');
                    const errorEl = document.getElementById('addColumnError');
                    errorEl.textContent = '';

                    if (usedSlots >= maxSlots) {
                        const extraSlot = usedSlots + 1;
                        modalSlotStatusAlert.className = 'alert alert-info py-2 px-3 mb-3 small d-flex align-items-start gap-2';
                        modalSlotStatusText.innerHTML = `<div><strong><i class="fa-solid fa-layer-group me-1"></i> Extra Assessment (${slotPrefix}${extraSlot})</strong><br>The Grade Sheet already shows the maximum visible columns (${slotPrefix}1&ndash;${slotPrefix}${maxSlots}). This new assessment will be saved as <strong>${slotPrefix}${extraSlot}</strong> and will appear in the <strong>Assessment Log</strong> instead of as a new Grade Sheet column. Existing grading is not affected.</div>`;
                        modalFormFields.classList.remove('d-none');
                        addColumnSubmitBtn.disabled = false;
                        addColumnBtnText.textContent = 'Add Assessment';

                        document.getElementById('addColumnTitle').value = `${slotPrefix}${extraSlot}`;
                        document.getElementById('addColumnTotal').value = 10;
                        document.getElementById('addColumnDate').value = new Date().toISOString().split('T')[0];
                        document.getElementById('addColumnDescription').value = '';
                    } else {
                        const nextSlot = usedSlots + 1;
                        modalSlotStatusAlert.className = 'alert alert-info py-2 px-3 mb-3 small d-flex align-items-center gap-2';
                        modalSlotStatusText.innerHTML = `<div><strong>Creating Assessment Slot:</strong> ${slotPrefix}${nextSlot} (${nextSlot} of ${maxSlots} visible ${categoryLabel} in this term)</div>`;
                        modalFormFields.classList.remove('d-none');
                        addColumnSubmitBtn.disabled = false;
                        addColumnBtnText.textContent = 'Add Column';

                        document.getElementById('addColumnTitle').value = `${slotPrefix}${nextSlot}`;
                        document.getElementById('addColumnTotal').value = 10;
                        document.getElementById('addColumnDate').value = new Date().toISOString().split('T')[0];
                        document.getElementById('addColumnDescription').value = '';
                    }

                    const modalEl = document.getElementById('addColumnModal');
                    new bootstrap.Modal(modalEl).show();

                    modalEl.addEventListener(
                        'shown.bs.modal',
                        () => {
                            document.getElementById('addColumnTitle').focus();
                        },
                        { once: true }
                    );

                }
            );

        });


    function setAddColumnLoading(loading) {

        const btn =
            document.getElementById(
                'addColumnSubmit'
            );

        const spinner =
            document.getElementById(
                'addColumnSpinner'
            );

        const btnText =
            document.getElementById(
                'addColumnBtnText'
            );

        btn.disabled = loading;

        if (loading) {

            spinner.classList.remove(
                'd-none'
            );

            btnText.textContent =
                'Adding...';

        } else {

            spinner.classList.add(
                'd-none'
            );

            btnText.textContent =
                'Add Column';

        }

    }


    document
        .getElementById('addColumnSubmit')
        .addEventListener(
            'click',
            async () => {

            const category =
                document.getElementById(
                    'addColumnCategory'
                ).value;


            const title =
                document.getElementById(
                    'addColumnTitle'
                ).value.trim();


            const total =
                document.getElementById(
                    'addColumnTotal'
                ).value;


            const date =
                document.getElementById(
                    'addColumnDate'
                ).value;


            const description =
                document.getElementById(
                    'addColumnDescription'
                ).value.trim();


            const errorEl =
                document.getElementById(
                    'addColumnError'
                );


            errorEl.textContent = '';


            if (
                !title ||
                !total ||
                total < 1
            ) {

                errorEl.textContent =
                    'Please fill in a title and a valid total items value.';

                return;

            }


            setAddColumnLoading(true);


            try {

                const res =
                    await fetch(
                        '{{ route("teacher.grading-system.grade-sheet.assessment") }}',
                        {

                            method: 'POST',

                            headers: {

                                'Content-Type':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    csrfToken

                            },

                            body:
                                JSON.stringify({

                                    teaching_assignment_id:
                                        teachingAssignmentId,

                                    grading_period_id:
                                        gradingPeriodId,

                                    category,

                                    title,

                                    total_items:
                                        total,

                                    assessment_date:
                                        date || null,

                                    description:
                                        description || null,

                                }),

                        }
                    );

                const data = await res.json();

                if (!res.ok) {
                    errorEl.textContent = data.error || data.message || 'Failed to add column.';
                    setAddColumnLoading(false);
                    return;
                }


                location.reload();


            } catch (e) {

                console.error(e);

                errorEl.textContent =
                    'Something went wrong. Please try again.';

                setAddColumnLoading(false);

            }

        }
    );


    // =========================================================
    // Live Score Context & Save Scores
    // =========================================================

    const ctxStudentName = document.getElementById('ctxStudentName');
    const ctxCategoryBadge = document.getElementById('ctxCategoryBadge');
    const ctxSlotBadge = document.getElementById('ctxSlotBadge');
    const ctxAssessmentTitle = document.getElementById('ctxAssessmentTitle');
    const ctxHpsWrap = document.getElementById('ctxHpsWrap');
    const ctxHps = document.getElementById('ctxHps');

    document
        .querySelectorAll('.gs-score-input')
        .forEach(input => {

            input.dataset.prevScore = input.value.trim();

            input.addEventListener('focus', () => {
                const studentName = input.dataset.studentName || 'Student';
                const categoryLabel = input.dataset.categoryLabel || 'Assessment';
                const slotLabel = input.dataset.slotLabel || '';
                const assessmentTitle = input.dataset.assessmentTitle || '';
                const maxScore = input.dataset.totalItems || input.max || '—';
                const enrollmentId = input.dataset.enrollmentId;

                if (ctxStudentName) {
                    ctxStudentName.textContent = `Student: ${studentName}`;
                }
                if (ctxCategoryBadge) {
                    ctxCategoryBadge.textContent = categoryLabel;
                    ctxCategoryBadge.style.display = 'inline-block';
                }
                if (ctxSlotBadge) {
                    ctxSlotBadge.textContent = slotLabel;
                    ctxSlotBadge.style.display = slotLabel ? 'inline-block' : 'none';
                }
                if (ctxAssessmentTitle) {
                    ctxAssessmentTitle.textContent = assessmentTitle ? `· "${assessmentTitle}"` : '';
                    ctxAssessmentTitle.style.display = assessmentTitle ? 'inline' : 'none';
                }
                if (ctxHpsWrap && ctxHps) {
                    ctxHps.textContent = maxScore;
                    ctxHpsWrap.style.display = 'inline';
                }

                // Highlight row in table
                document.querySelectorAll('#gradeSheetTable tbody tr').forEach(r => r.classList.remove('table-active'));
                const row = document.querySelector(`tr[data-enrollment-id="${enrollmentId}"]`);
                if (row) {
                    row.classList.add('table-active');
                }
            });

            input.addEventListener(
                'blur',
                async () => {

                    const assessmentId =
                        input.dataset.assessmentId;


                    const enrollmentId =
                        input.dataset.enrollmentId;


                    const score =
                        input.value.trim();


                    // If score is cleared and it had a value before
                    if (score === '') {
                        if (input.dataset.prevScore !== undefined && input.dataset.prevScore !== '') {
                            try {
                                const res = await fetch('{{ route("teacher.grading-system.grade-sheet.score") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: JSON.stringify({
                                        assessment_id: assessmentId,
                                        enrollment_id: enrollmentId,
                                        score: null
                                    })
                                });
                                const data = await res.json();
                                if (res.ok) {
                                    input.dataset.prevScore = '';
                                    const row = document.querySelector(`tr[data-enrollment-id="${enrollmentId}"]`);
                                    if (row) {
                                        const initialCell = row.querySelector('.gs-initial-cell');
                                        if (initialCell) initialCell.textContent = data.initial_grade ?? '—';
                                        const transmutedCell = row.querySelector('.gs-transmuted-cell');
                                        if (transmutedCell) transmutedCell.textContent = data.transmuted_grade ?? '—';
                                        const descriptorCell = row.querySelector('.gs-descriptor-cell');
                                        if (descriptorCell) descriptorCell.textContent = data.descriptor ?? '—';
                                    }
                                }
                            } catch (e) {
                                console.error(e);
                            }
                        }
                        return;
                    }


                    const maxScore =
                        parseFloat(input.max);


                    const scoreValue =
                        parseFloat(score);


                    if (
                        scoreValue < 0 ||
                        scoreValue > maxScore
                    ) {

                        input.style.backgroundColor =
                            '#FCEBEB';


                        const errorMsg =
                            document.createElement(
                                'div'
                            );


                        errorMsg.className =
                            'invalid-feedback d-block';


                        errorMsg.textContent =
                            'Invalid Score: Score must be between 0 and ' +
                            maxScore;


                        input.parentNode.appendChild(
                            errorMsg
                        );


                        setTimeout(
                            () => {

                                errorMsg.remove();

                                input.style.backgroundColor =
                                    '';

                            },
                            3000
                        );


                        return;

                    }


                    const row =
                        document.querySelector(
                            `tr[data-enrollment-id="${enrollmentId}"]`
                        );


                    const originalBg =
                        input.style.backgroundColor;


                    input.style.backgroundColor =
                        '#FAEEDA';


                    try {

                        const res =
                            await fetch(
                                '{{ route("teacher.grading-system.grade-sheet.score") }}',
                                {

                                    method: 'POST',

                                    headers: {

                                        'Content-Type':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            csrfToken

                                    },

                                    body:
                                        JSON.stringify({

                                            assessment_id:
                                                assessmentId,

                                            enrollment_id:
                                                enrollmentId,

                                            score

                                        }),

                                }
                            );


                        const data =
                            await res.json();


                        if (!res.ok) {

                            input.style.backgroundColor =
                                '#FCEBEB';

                            alert(
                                data.error ||
                                'Score could not be saved.'
                            );

                            return;

                        }

                        input.dataset.prevScore = score;


                        if (!row) {
                            return;
                        }


                        // Initial Grade
                        const initialCell =
                            row.querySelector(
                                '.gs-initial-cell'
                            );


                        if (initialCell) {

                            initialCell.textContent =
                                data.initial_grade ??
                                '—';

                        }


                        // Transmuted Grade
                        const transmutedCell =
                            row.querySelector(
                                '.gs-transmuted-cell'
                            );


                        if (transmutedCell) {

                            transmutedCell.textContent =
                                data.transmuted_grade ??
                                '—';

                        }


                        // Descriptor
                        const descriptorCell =
                            row.querySelector(
                                '.gs-descriptor-cell'
                            );


                        if (descriptorCell) {

                            descriptorCell.textContent =
                                data.descriptor ??
                                '—';

                        }


                        // Component summaries
                        if (
                            data.component_summaries
                        ) {

                            Object.entries(
                                data.component_summaries
                            )
                            .forEach(
                                (
                                    [
                                        component,
                                        summary
                                    ]
                                ) => {

                                    [
                                        'total',
                                        'ps',
                                        'ws'
                                    ].forEach(
                                        field => {

                                            const cell =
                                                row.querySelector(
                                                    `.gs-summary-cell[data-component="${component}"][data-field="${field}"]`
                                                );


                                            if (cell) {

                                                cell.textContent =
                                                    summary[field] ??
                                                    '—';

                                            }

                                        }
                                    );

                                }
                            );

                        }


                        // Grade Completion
                        const completionEl =
                            document.getElementById(
                                'statGradeCompletion'
                            );


                        if (completionEl) {

                            completionEl.textContent =
                                data.grade_completion_percent !==
                                    null &&
                                data.grade_completion_percent !==
                                    undefined
                                    ? data.grade_completion_percent +
                                      '%'
                                    : '—';

                        }


                        // Class Average
                        const averageEl =
                            document.getElementById(
                                'statClassAverage'
                            );


                        if (averageEl) {

                            averageEl.textContent =
                                data.class_average !==
                                    null &&
                                data.class_average !==
                                    undefined
                                    ? data.class_average
                                    : '—';

                        }


                        // Passing Rate
                        const passingEl =
                            document.getElementById(
                                'statPassingRate'
                            );


                        if (passingEl) {

                            passingEl.textContent =
                                data.passing_rate !==
                                    null &&
                                data.passing_rate !==
                                    undefined
                                    ? data.passing_rate +
                                      '%'
                                    : '—';

                        }


                        // Saved indicator
                        input.style.backgroundColor =
                            '#e1f5ee';


                        setTimeout(
                            () => {

                                input.style.backgroundColor =
                                    originalBg;

                            },
                            800
                        );


                    } catch (e) {

                        console.error(e);

                        input.style.backgroundColor =
                            '#FCEBEB';

                    }

                }
            );

        });

</script>

</x-layouts.teacher>
