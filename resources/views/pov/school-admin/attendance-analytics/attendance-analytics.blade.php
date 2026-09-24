<x-layouts.school-admin>
    <x-slot name="title">
        School Analytics
    </x-slot>

    <x-slot name="pageName">
        School Analytics & Attendance Monitoring
    </x-slot>

    <x-slot name="subtitle">
        School-wide progressive analytical drill-down across levels, grades, sections, subjects, and students.
    </x-slot>

    @php
        $currentFilter = $dateFilter ?? request('date_filter', 'today');
        $queryValue = $query ?? request('query', '');
        $scanTypeValue = $scan_type ?? request('scan_type', 'all');
        $sessionTypeValue = $session_type ?? request('session_type', 'all');
        $flagTypeValue = $flag_type ?? request('flag_type', 'all');
        $filters = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_7_days' => 'Last 7 Days',
            'month' => 'This Month',
            'custom' => 'Custom Range',
        ];

        // Determine default modal start and end dates based on active filter
        $defaultStartDate = request('custom_start_date') ?: match ($currentFilter) {
            'yesterday' => now()->subDay()->format('Y-m-d'),
            'last_7_days' => now()->subDays(6)->format('Y-m-d'),
            'month' => now()->startOfMonth()->format('Y-m-d'),
            default => now()->format('Y-m-d'),
        };
        $defaultEndDate = request('custom_end_date') ?: match ($currentFilter) {
            'yesterday' => now()->subDay()->format('Y-m-d'),
            default => now()->format('Y-m-d'),
        };

        $scope = $scopeContext ?? [
            'title' => 'School-wide Analytics',
            'breadcrumbs' => [['label' => 'School-wide', 'url' => route('school_admin.time-in-time-out-history.analytics')]],
            'activeChips' => [],
            'isSchoolWide' => true,
            'cohortStudentCount' => 0,
            'cohortSectionCount' => 0,
            'clearAllUrl' => route('school_admin.time-in-time-out-history.analytics'),
        ];

        $selLevel = $academicLevel ?? request('academic_level', '');
        $selGrades = (array) ($gradeLevels ?? request('grade_levels', request('grade_level', [])));
        $selSections = (array) ($sectionIds ?? request('section_ids', request('section_id', [])));
        $selSubjects = (array) ($subjectIds ?? request('subject_ids', request('subject_id', [])));
        $selStudent = $studentId ?? request('student_id', '');
        $selTerm = $term ?? request('term', '');
    @endphp

    <style>
        /* ==========================================================================
           SCOPE & PROGRESSIVE FILTER BAR STYLES
           ========================================================================== */
        .sa-scope-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            margin: 0 1rem 1.25rem 1rem;
            overflow: hidden;
        }

        /* Scope Status Header */
        .sa-scope-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 0.85rem 1.25rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .sa-scope-badge-tag {
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            background: rgba(59, 130, 246, 0.25);
            color: #93c5fd;
            border: 1px solid rgba(147, 197, 253, 0.3);
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .sa-scope-breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.4rem;
            font-size: 0.85rem;
            margin: 0.25rem 0 0 0;
            font-weight: 600;
        }

        .sa-scope-breadcrumb__item {
            color: #cbd5e1;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .sa-scope-breadcrumb__item.active {
            color: #38bdf8;
            font-weight: 800;
        }

        .sa-scope-cohort {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.5rem;
            padding: 0.35rem 0.75rem;
            font-size: 0.75rem;
            color: #e2e8f0;
        }

        .sa-scope-cohort strong {
            color: #ffffff;
            font-weight: 700;
        }

        /* Filter Controls Body */
        .sa-scope-body {
            padding: 1rem 1.25rem;
            background: #ffffff;
        }

        .sa-filter-label {
            font-size: 0.68rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #475569;
            letter-spacing: 0.05em;
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sa-filter-label .hint {
            font-size: 0.62rem;
            color: #94a3b8;
            text-transform: none;
            font-weight: 400;
        }

        /* Active Filter Chips */
        .sa-filter-chips {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.45rem;
            padding-top: 0.75rem;
            border-top: 1px dashed #e2e8f0;
            margin-top: 0.75rem;
        }

        .sa-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: #f1f5f9;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            padding: 0.2rem 0.65rem;
            font-size: 0.72rem;
            font-weight: 600;
            transition: all 0.15s ease;
        }

        .sa-chip:hover {
            background: #e2e8f0;
        }

        .sa-chip__remove {
            color: #64748b;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            transition: all 0.15s ease;
            font-size: 0.75rem;
            line-height: 1;
        }

        .sa-chip__remove:hover {
            background: #dc2626;
            color: #ffffff;
        }

        /* Disclosure Trigger */
        .sa-disclosure-btn {
            font-size: 0.75rem;
            font-weight: 700;
            color: #2563eb;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .sa-disclosure-btn:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        .sa-multi-select-wrap {
            position: relative;
        }

        .sa-multi-select-btn {
            width: 100%;
            text-align: left;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sa-multi-select-menu {
            max-height: 220px;
            overflow-y: auto;
            min-width: 100%;
            padding: 0.5rem;
        }

        .sa-multi-select-item {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.25rem 0.4rem;
            font-size: 0.78rem;
            border-radius: 0.25rem;
            cursor: pointer;
        }

        .sa-multi-select-item:hover {
            background: #f1f5f9;
        }
    </style>

    {{-- Flash Messages --}}
    @if (session('error'))
        <div class="alert alert-danger mx-3 mb-3" role="alert">{{ session('error') }}</div>
    @endif

    {{-- =========================================================================
         EXECUTIVE SCOPE & PROGRESSIVE FILTER COMPONENT
         ========================================================================= --}}
    <div class="sa-scope-panel">
        {{-- 1. Prominent Scope Header ("What am I currently analyzing?") --}}
        <div class="sa-scope-header">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="sa-scope-badge-tag">
                        <i class="fas fa-crosshairs"></i> Current Analytical Scope
                    </span>
                    @if ($scope['isSchoolWide'])
                        <span class="badge bg-success bg-opacity-75 text-white" style="font-size: 0.65rem;">
                            School-wide Overview
                        </span>
                    @else
                        <span class="badge bg-info bg-opacity-75 text-white" style="font-size: 0.65rem;">
                            Targeted Drill-down
                        </span>
                    @endif
                </div>

                {{-- Breadcrumbs representation --}}
                <div class="sa-scope-breadcrumb">
                    @foreach ($scope['breadcrumbs'] as $index => $crumb)
                        <span class="sa-scope-breadcrumb__item {{ $loop->last ? 'active' : '' }}">
                            @if ($crumb['url'] && !$loop->last)
                                <a href="{{ $crumb['url'] }}" class="text-light text-decoration-none hover-underline">
                                    {{ $crumb['label'] }}
                                </a>
                            @else
                                {{ $crumb['label'] }}
                            @endif

                            @if (!$loop->last)
                                <i class="fas fa-chevron-right text-muted" style="font-size: 0.65rem;"></i>
                            @endif
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Cohort Metrics & Fast Reset --}}
            <div class="d-flex align-items-center gap-2">
                <div class="sa-scope-cohort">
                    <i class="fas fa-users-viewfinder text-info"></i>
                    <span>Cohort: <strong>{{ number_format($scope['cohortStudentCount']) }}</strong> Student(s)</span>
                    <span class="text-muted">•</span>
                    <span><strong>{{ number_format($scope['cohortSectionCount']) }}</strong> Section(s)</span>
                </div>

                @if (!$scope['isSchoolWide'])
                    <a href="{{ $scope['clearAllUrl'] }}" class="btn btn-outline-light btn-sm px-2 py-1" style="font-size: 0.72rem;" title="Reset all scope filters back to School-wide">
                        <i class="fas fa-rotate-left me-1"></i> Clear all
                    </a>
                @endif
            </div>
        </div>

        {{-- 2. Filter Form with Client-side Cascading --}}
        <div class="sa-scope-body">
            <form id="scopeFilterForm" action="{{ route('school_admin.time-in-time-out-history.analytics') }}" method="GET">

                {{-- PRIMARY ROW: Level -> Grade -> Section (Progressive Hierarchy) --}}
                <div class="row g-2 mb-2 align-items-end">
                    {{-- Academic Level --}}
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="sa-filter-label" for="academic_level">
                            <span>1. Academic Level</span>
                            <span class="hint">Broad Scope</span>
                        </label>
                        <select class="form-select form-select-sm" name="academic_level" id="academic_level">
                            <option value="">All Levels (School-wide)</option>
                            <option value="elementary" @selected($selLevel === 'elementary')>Elementary (Grades 1–6)</option>
                            <option value="jhs" @selected(in_array($selLevel, ['jhs', 'hs', 'highschool']))>Junior High School (Grades 7–10)</option>
                            <option value="shs" @selected(in_array($selLevel, ['shs', 'senior_high_school']))>Senior High School (Grades 11–12)</option>
                        </select>
                    </div>

                    {{-- Grade Level --}}
                    <div class="col-12 col-sm-6 col-md-3">
                        <label class="sa-filter-label" for="grade_level">
                            <span>2. Grade Level</span>
                            <span class="hint" id="gradeCountHint">Select Grade</span>
                        </label>
                        <select class="form-select form-select-sm" name="grade_level" id="grade_level">
                            <option value="">All Grades</option>
                            @for ($g = 1; $g <= 12; $g++)
                                <option value="{{ $g }}" data-grade="{{ $g }}" @selected(in_array((string)$g, array_map('strval', $selGrades)))>
                                    Grade {{ $g }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- Section (Multi-selection enabled) --}}
                    <div class="col-12 col-sm-6 col-md-4">
                        <label class="sa-filter-label">
                            <span>3. Section(s)</span>
                            <span class="hint">Multi-selection</span>
                        </label>
                        <div class="dropdown sa-multi-select-wrap">
                            <button class="btn btn-outline-secondary btn-sm sa-multi-select-btn bg-white border dropdown-toggle" type="button" id="sectionDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                <span id="sectionSelectedSummary">
                                    @if (!empty($selSections))
                                        {{ count($selSections) }} Section(s) Selected
                                    @else
                                        All Sections
                                    @endif
                                </span>
                            </button>
                            <div class="dropdown-menu sa-multi-select-menu shadow-sm" aria-labelledby="sectionDropdownBtn" id="sectionOptionsContainer">
                                <div class="px-2 pb-1 border-bottom d-flex justify-content-between align-items-center mb-1">
                                    <small class="fw-bold text-muted text-uppercase" style="font-size: 0.65rem;">Available Sections</small>
                                    <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none" style="font-size: 0.68rem;" id="clearSectionsBtn">Clear</button>
                                </div>
                                <div id="sectionCheckboxesList">
                                    {{-- Dynamically populated via JS --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="col-12 col-sm-6 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" id="applyScopeBtn">
                            <i class="fas fa-filter me-1"></i> Apply Scope
                        </button>
                    </div>
                </div>

                {{-- PROGRESSIVE DISCLOSURE: Advanced Filters (Subject, Student, Term, Date Presets) --}}
                <div class="d-flex align-items-center justify-content-between mb-2 pt-1">
                    <button type="button" class="sa-disclosure-btn" id="toggleMoreFiltersBtn" data-bs-toggle="collapse" data-bs-target="#advancedScopeCollapse" aria-expanded="{{ (!empty($selSubjects) || $selStudent || $selTerm || $currentFilter !== 'today' || $scanTypeValue !== 'all' || $sessionTypeValue !== 'all' || $flagTypeValue !== 'all' || $queryValue) ? 'true' : 'false' }}">
                        <i class="fas fa-sliders me-1"></i>
                        <span id="moreFiltersLabel">
                            {{ (!empty($selSubjects) || $selStudent || $selTerm || $currentFilter !== 'today' || $scanTypeValue !== 'all') ? 'Hide Detailed Focus & Filters' : 'More Filters: Subject, Student, Term & Time' }}
                        </span>
                        <i class="fas fa-chevron-down ms-1" style="font-size: 0.65rem;"></i>
                    </button>

                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('school_admin.time-in-time-out-history.analytics') }}" class="btn btn-outline-secondary btn-sm px-2 py-0.5" style="font-size: 0.72rem;">
                            Reset All
                        </a>
                        <button type="button" class="btn btn-outline-primary btn-sm px-2 py-0.5" style="font-size: 0.72rem;" data-bs-toggle="modal" data-bs-target="#downloadAnalyticsModal">
                            <i class="fas fa-file-arrow-down me-1"></i> Export Report
                        </button>
                    </div>
                </div>

                <div class="collapse {{ (!empty($selSubjects) || $selStudent || $selTerm || $currentFilter !== 'today' || $scanTypeValue !== 'all' || $sessionTypeValue !== 'all' || $flagTypeValue !== 'all' || $queryValue) ? 'show' : '' }}" id="advancedScopeCollapse">
                    <div class="p-3 bg-light rounded-3 border mb-2">
                        <div class="row g-2 mb-2">
                            {{-- Specific Subject (Multi-selection supported) --}}
                            <div class="col-12 col-md-4">
                                <label class="sa-filter-label">
                                    <span>Subject(s)</span>
                                    <span class="hint">Optional Focus</span>
                                </label>
                                <div class="dropdown sa-multi-select-wrap">
                                    <button class="btn btn-outline-secondary btn-sm sa-multi-select-btn bg-white border dropdown-toggle" type="button" id="subjectDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span id="subjectSelectedSummary">
                                            @if (!empty($selSubjects))
                                                {{ count($selSubjects) }} Subject(s) Selected
                                            @else
                                                All Subjects
                                            @endif
                                        </span>
                                    </button>
                                    <div class="dropdown-menu sa-multi-select-menu shadow-sm" aria-labelledby="subjectDropdownBtn">
                                        <div class="px-2 pb-1 border-bottom d-flex justify-content-between align-items-center mb-1">
                                            <small class="fw-bold text-muted text-uppercase" style="font-size: 0.65rem;">Curricular Subjects</small>
                                            <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none" style="font-size: 0.68rem;" id="clearSubjectsBtn">Clear</button>
                                        </div>
                                        <div id="subjectCheckboxesList">
                                            {{-- Dynamically populated via JS --}}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Specific Student --}}
                            <div class="col-12 col-md-5">
                                <label class="sa-filter-label" for="student_id">
                                    <span>Individual Student Focus</span>
                                    <span class="hint" id="studentCountHint">Scoped by Section</span>
                                </label>
                                <select class="form-select form-select-sm" name="student_id" id="student_id">
                                    <option value="">All Students in Scope</option>
                                    {{-- Dynamically populated via JS based on level/grade/section --}}
                                </select>
                            </div>

                            {{-- Grading Period / Term --}}
                            <div class="col-12 col-md-3">
                                <label class="sa-filter-label" for="term">
                                    <span>Academic Term</span>
                                    <span class="hint">Trimester</span>
                                </label>
                                <select class="form-select form-select-sm" name="term" id="term">
                                    <option value="">All Terms / Cumulative</option>
                                    @foreach ($hierarchy['gradingPeriods'] ?? [] as $gp)
                                        <option value="{{ $gp['sequence'] }}" @selected((string)$selTerm === (string)$gp['sequence'])>
                                            {{ $gp['name'] ?? 'Term ' . $gp['sequence'] }} {{ $gp['is_active'] ? '(Active)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Date Presets & Kiosk Parameters Row --}}
                        <div class="row g-2 align-items-center pt-2 border-top">
                            <div class="col-12 col-lg-7 d-flex flex-wrap align-items-center gap-1">
                                <span class="sa-filter-label m-0 me-2">Date Window:</span>
                                <div class="btn-group btn-group-sm" role="group" aria-label="Date Presets">
                                    @foreach($filters as $val => $lbl)
                                        <input type="radio" class="btn-check" name="date_filter" id="date_{{ $val }}"
                                            value="{{ $val }}" @checked($currentFilter === $val)>
                                        <label class="btn btn-outline-primary btn-sm rounded px-2.5 py-0.5 me-1" style="font-size: 0.72rem;" for="date_{{ $val }}">
                                            {{ $lbl }}
                                        </label>
                                    @endforeach
                                </div>

                                @if ($currentFilter === 'custom')
                                    <div class="d-flex align-items-center gap-1 ms-1">
                                        <input type="date" name="custom_start_date" class="form-control form-control-sm py-0.5 px-2" style="font-size: 0.72rem; width: 130px;"
                                            value="{{ request('custom_start_date') }}" required />
                                        <span class="text-muted" style="font-size: 0.7rem;">to</span>
                                        <input type="date" name="custom_end_date" class="form-control form-control-sm py-0.5 px-2" style="font-size: 0.72rem; width: 130px;"
                                            value="{{ request('custom_end_date') }}" required />
                                    </div>
                                @endif
                            </div>

                            <div class="col-12 col-lg-5 d-flex gap-2 justify-content-lg-end">
                                <div style="min-width: 110px;">
                                    <select class="form-select form-select-sm py-0.5" style="font-size: 0.72rem;" name="scan_type">
                                        <option value="all" @selected($scanTypeValue === 'all')>All Scans</option>
                                        <option value="IN" @selected($scanTypeValue === 'IN')>Time In</option>
                                        <option value="OUT" @selected($scanTypeValue === 'OUT')>Time Out</option>
                                    </select>
                                </div>
                                <div style="min-width: 110px;">
                                    <select class="form-select form-select-sm py-0.5" style="font-size: 0.72rem;" name="session_type">
                                        <option value="all" @selected($sessionTypeValue === 'all')>All Sessions</option>
                                        <option value="morning" @selected($sessionTypeValue === 'morning')>Morning</option>
                                        <option value="afternoon" @selected($sessionTypeValue === 'afternoon')>Afternoon</option>
                                        <option value="whole_day" @selected($sessionTypeValue === 'whole_day')>Whole Day</option>
                                    </select>
                                </div>
                                <div style="min-width: 110px;">
                                    <select class="form-select form-select-sm py-0.5" style="font-size: 0.72rem;" name="flag_type">
                                        <option value="all" @selected($flagTypeValue === 'all')>All Remarks</option>
                                        <option value="late_arrival" @selected($flagTypeValue === 'late_arrival')>Late Only</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Active Filter Chips (Individual Dismissal) --}}
                @if (!empty($scope['activeChips']))
                    <div class="sa-filter-chips">
                        <span class="text-muted small fw-bold text-uppercase me-1" style="font-size: 0.65rem;">Active Filters:</span>
                        @foreach ($scope['activeChips'] as $chip)
                            <span class="sa-chip">
                                {{ $chip['label'] }}
                                <a href="{{ $chip['remove_url'] }}" class="sa-chip__remove" title="Remove this filter">
                                    <i class="fas fa-times"></i>
                                </a>
                            </span>
                        @endforeach

                        <a href="{{ $scope['clearAllUrl'] }}" class="btn btn-link btn-xs text-danger text-decoration-none fw-bold p-0 ms-2" style="font-size: 0.7rem;">
                            <i class="fas fa-trash-can me-1"></i> Clear all
                        </a>
                    </div>
                @endif

            </form>
        </div>
    </div>

    {{-- Analytics Dashboard Section (Charts & Diagnostics Untouched) --}}
    <div class="mx-3 mb-4">
        @include('pov.school-admin.attendance-analytics.partials.attendance-analytics')
    </div>

    {{-- Download Report Modal with Scope Preservation --}}
    <div class="modal fade" id="downloadAnalyticsModal" tabindex="-1" aria-labelledby="downloadAnalyticsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form method="GET" action="{{ route('school_admin.time-in-time-out-history.analytics-pdf') }}">
                    {{-- Active filter preservation across array and scalar inputs --}}
                    @foreach (request()->except(['date_filter', 'custom_start_date', 'custom_end_date', 'page', 'start_date', 'end_date']) as $field => $value)
                        @if (is_array($value))
                            @foreach ($value as $v)
                                <input type="hidden" name="{{ $field }}[]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $field }}" value="{{ $value }}">
                        @endif
                    @endforeach

                    <div class="modal-header bg-light border-bottom py-3 px-4">
                        <h6 class="modal-title fw-bold" id="downloadAnalyticsModalLabel">
                            <i class="fas fa-file-pdf text-danger me-2"></i> Export Scoped Analytics Report
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <i class="fas fa-circle-info me-1"></i>
                            Exporting for scope: <strong>{{ $scope['title'] }}</strong> ({{ $scope['cohortStudentCount'] }} students).
                        </div>

                        <p class="text-muted small mb-3">Select the date window for the PDF export:</p>

                        <div class="row g-3">
                            <div class="col-6">
                                <label for="analytics_start_date" class="form-label text-muted small fw-bold text-uppercase">Start Date</label>
                                <input type="date" class="form-control form-control-sm" id="analytics_start_date"
                                    name="start_date" value="{{ $defaultStartDate }}" required>
                            </div>

                            <div class="col-6">
                                <label for="analytics_end_date" class="form-label text-muted small fw-bold text-uppercase">End Date</label>
                                <input type="date" class="form-control form-control-sm" id="analytics_end_date"
                                    name="end_date" value="{{ $defaultEndDate }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm text-white">
                            <i class="fas fa-download me-1"></i> Download PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- =========================================================================
         CLIENT-SIDE PROGRESSIVE CASCADING SCRIPT (NO UNNECESSARY PAGE RELOADS)
         ========================================================================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Raw hierarchy data from backend
            const hierarchy = @json($hierarchy ?? []);
            const allSections = hierarchy.sections || [];
            const allSubjects = hierarchy.subjects || [];
            const allStudents = hierarchy.students || [];
            const sectionSubjectMap = hierarchy.sectionSubjectMap || {};
            const levels = hierarchy.levels || {};

            // Initial selected states from server
            const initialLevel = @json($selLevel);
            const initialGrades = @json(array_map('intval', $selGrades));
            const initialSections = @json(array_map('intval', $selSections));
            const initialSubjects = @json(array_map('intval', $selSubjects));
            const initialStudent = @json($selStudent ? (int)$selStudent : null);

            // DOM elements
            const levelSelect = document.getElementById('academic_level');
            const gradeSelect = document.getElementById('grade_level');
            const sectionContainer = document.getElementById('sectionCheckboxesList');
            const sectionSummary = document.getElementById('sectionSelectedSummary');
            const clearSectionsBtn = document.getElementById('clearSectionsBtn');

            const subjectContainer = document.getElementById('subjectCheckboxesList');
            const subjectSummary = document.getElementById('subjectSelectedSummary');
            const clearSubjectsBtn = document.getElementById('clearSubjectsBtn');

            const studentSelect = document.getElementById('student_id');
            const studentCountHint = document.getElementById('studentCountHint');

            // Grade Level ranges by academic level
            const levelGradeMap = {
                'elementary': [1, 2, 3, 4, 5, 6],
                'jhs': [7, 8, 9, 10],
                'hs': [7, 8, 9, 10],
                'highschool': [7, 8, 9, 10],
                'shs': [11, 12],
                'senior_high_school': [11, 12]
            };

            /**
             * 1. Populate Grade Dropdown based on selected Academic Level
             */
            function updateGradeOptions(selectedLevel, preserveVal = null) {
                const currentVal = preserveVal !== null ? preserveVal : gradeSelect.value;
                const allowedGrades = selectedLevel && levelGradeMap[selectedLevel] ? levelGradeMap[selectedLevel] : null;

                gradeSelect.innerHTML = '<option value="">All Grades</option>';

                for (let g = 1; g <= 12; g++) {
                    if (!allowedGrades || allowedGrades.includes(g)) {
                        const opt = document.createElement('option');
                        opt.value = g;
                        opt.textContent = 'Grade ' + g;
                        if (currentVal && parseInt(currentVal, 10) === g) {
                            opt.selected = true;
                        }
                        gradeSelect.appendChild(opt);
                    }
                }
            }

            /**
             * 2. Populate Section Checkboxes based on selected Level and Grade
             */
            function updateSectionOptions(selectedLevel, selectedGrade, checkedSections = []) {
                const allowedGrades = selectedGrade ? [parseInt(selectedGrade, 10)] :
                    (selectedLevel && levelGradeMap[selectedLevel] ? levelGradeMap[selectedLevel] : null);

                const filtered = allSections.filter(sec => {
                    if (allowedGrades && !allowedGrades.includes(sec.grade_level)) {
                        return false;
                    }
                    return true;
                });

                sectionContainer.innerHTML = '';

                if (filtered.length === 0) {
                    sectionContainer.innerHTML = '<div class="text-muted small p-2">No sections found for this grade/level.</div>';
                    updateSectionSummary([]);
                    return;
                }

                filtered.forEach(sec => {
                    const isChecked = checkedSections.includes(sec.id);
                    const item = document.createElement('label');
                    item.className = 'sa-multi-select-item';
                    item.innerHTML = `
                        <input type="checkbox" name="section_ids[]" value="${sec.id}" class="form-check-input section-cb" ${isChecked ? 'checked' : ''}>
                        <span>${sec.display}</span>
                    `;
                    sectionContainer.appendChild(item);
                });

                // Attach change listeners to section checkboxes
                sectionContainer.querySelectorAll('.section-cb').forEach(cb => {
                    cb.addEventListener('change', () => {
                        const activeSecs = getSelectedSectionIds();
                        updateSectionSummary(activeSecs);
                        updateSubjectOptions(levelSelect.value, gradeSelect.value, activeSecs, getSelectedSubjectIds());
                        updateStudentOptions(levelSelect.value, gradeSelect.value, activeSecs, studentSelect.value);
                    });
                });

                updateSectionSummary(checkedSections);
            }

            function getSelectedSectionIds() {
                return Array.from(sectionContainer.querySelectorAll('.section-cb:checked')).map(cb => parseInt(cb.value, 10));
            }

            function updateSectionSummary(selectedIds) {
                if (selectedIds.length === 0) {
                    sectionSummary.textContent = 'All Sections';
                } else if (selectedIds.length === 1) {
                    const sec = allSections.find(s => s.id === selectedIds[0]);
                    sectionSummary.textContent = sec ? sec.display : '1 Section';
                } else {
                    sectionSummary.textContent = `${selectedIds.length} Sections Selected`;
                }
            }

            /**
             * 3. Populate Subject Checkboxes based on Level / Section
             */
            function updateSubjectOptions(selectedLevel, selectedGrade, selectedSectionIds = [], checkedSubjects = []) {
                let relevantSubjectIds = new Set();

                // If sections are selected, only show subjects taught in those sections
                if (selectedSectionIds.length > 0) {
                    selectedSectionIds.forEach(secId => {
                        const subList = sectionSubjectMap[secId] || [];
                        subList.forEach(id => relevantSubjectIds.add(id));
                    });
                }

                const filtered = allSubjects.filter(sub => {
                    // If section constraint is present
                    if (relevantSubjectIds.size > 0 && !relevantSubjectIds.has(sub.id)) {
                        return false;
                    }
                    // Level constraint if applicable
                    if (selectedLevel) {
                        const norm = selectedLevel === 'elementary' ? 'elementary' : (['jhs', 'hs', 'highschool'].includes(selectedLevel) ? 'hs' : 'shs');
                        if (sub.level && sub.level !== norm) {
                            return false;
                        }
                    }
                    return true;
                });

                subjectContainer.innerHTML = '';

                if (filtered.length === 0) {
                    subjectContainer.innerHTML = '<div class="text-muted small p-2">No subjects found for current selection.</div>';
                    updateSubjectSummary([]);
                    return;
                }

                filtered.forEach(sub => {
                    const isChecked = checkedSubjects.includes(sub.id);
                    const item = document.createElement('label');
                    item.className = 'sa-multi-select-item';
                    item.innerHTML = `
                        <input type="checkbox" name="subject_ids[]" value="${sub.id}" class="form-check-input subject-cb" ${isChecked ? 'checked' : ''}>
                        <span>${sub.display}</span>
                    `;
                    subjectContainer.appendChild(item);
                });

                subjectContainer.querySelectorAll('.subject-cb').forEach(cb => {
                    cb.addEventListener('change', () => {
                        updateSubjectSummary(getSelectedSubjectIds());
                    });
                });

                updateSubjectSummary(checkedSubjects);
            }

            function getSelectedSubjectIds() {
                return Array.from(subjectContainer.querySelectorAll('.subject-cb:checked')).map(cb => parseInt(cb.value, 10));
            }

            function updateSubjectSummary(selectedIds) {
                if (selectedIds.length === 0) {
                    subjectSummary.textContent = 'All Subjects';
                } else if (selectedIds.length === 1) {
                    const sub = allSubjects.find(s => s.id === selectedIds[0]);
                    subjectSummary.textContent = sub ? sub.name : '1 Subject';
                } else {
                    subjectSummary.textContent = `${selectedIds.length} Subjects Selected`;
                }
            }

            /**
             * 4. Populate Student Dropdown based on Level, Grade, and Section
             */
            function updateStudentOptions(selectedLevel, selectedGrade, selectedSectionIds = [], preserveStudentId = null) {
                const allowedGrades = selectedGrade ? [parseInt(selectedGrade, 10)] :
                    (selectedLevel && levelGradeMap[selectedLevel] ? levelGradeMap[selectedLevel] : null);

                const filtered = allStudents.filter(st => {
                    if (allowedGrades && !allowedGrades.includes(st.grade_level)) {
                        return false;
                    }
                    if (selectedSectionIds.length > 0 && !selectedSectionIds.includes(st.section_id)) {
                        return false;
                    }
                    return true;
                });

                studentSelect.innerHTML = '<option value="">All Students in Scope (' + filtered.length + ')</option>';
                studentCountHint.textContent = filtered.length + ' eligible students';

                filtered.forEach(st => {
                    const opt = document.createElement('option');
                    opt.value = st.student_id;
                    opt.textContent = `${st.name} (${st.student_number})`;
                    if (preserveStudentId && parseInt(preserveStudentId, 10) === st.student_id) {
                        opt.selected = true;
                    }
                    studentSelect.appendChild(opt);
                });
            }

            // Event Listeners for Dynamic Cascading
            levelSelect.addEventListener('change', function () {
                const lvl = this.value;
                updateGradeOptions(lvl, null);
                updateSectionOptions(lvl, null, []);
                updateSubjectOptions(lvl, null, [], []);
                updateStudentOptions(lvl, null, [], null);
            });

            gradeSelect.addEventListener('change', function () {
                const lvl = levelSelect.value;
                const grd = this.value;
                updateSectionOptions(lvl, grd, []);
                updateSubjectOptions(lvl, grd, [], []);
                updateStudentOptions(lvl, grd, [], null);
            });

            clearSectionsBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                sectionContainer.querySelectorAll('.section-cb').forEach(cb => cb.checked = false);
                updateSectionSummary([]);
                const lvl = levelSelect.value;
                const grd = gradeSelect.value;
                updateSubjectOptions(lvl, grd, [], getSelectedSubjectIds());
                updateStudentOptions(lvl, grd, [], studentSelect.value);
            });

            clearSubjectsBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                subjectContainer.querySelectorAll('.subject-cb').forEach(cb => cb.checked = false);
                updateSubjectSummary([]);
            });

            // Date radio buttons trigger submit for instantaneous preview
            document.querySelectorAll('input[name="date_filter"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    if (this.value !== 'custom') {
                        document.getElementById('scopeFilterForm').submit();
                    }
                });
            });

            // INITIALIZE with Server-provided Values
            const initialGradeVal = initialGrades.length > 0 ? initialGrades[0] : null;
            updateGradeOptions(initialLevel, initialGradeVal);
            updateSectionOptions(initialLevel, initialGradeVal, initialSections);
            updateSubjectOptions(initialLevel, initialGradeVal, initialSections, initialSubjects);
            updateStudentOptions(initialLevel, initialGradeVal, initialSections, initialStudent);
        });
    </script>

</x-layouts.school-admin>