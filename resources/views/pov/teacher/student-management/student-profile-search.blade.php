<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-users"></i>
            Students
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">Search and view academic performance records of students from your assigned classes.</span>
    </x-slot>

    <!-- Filters -->
    <form method="GET"
          action="{{ route('teacher.grading-system.student-profile') }}"
          class="gs-filter-bar mb-3">

        <div class="row g-3 align-items-end">

            <!-- Search -->
            <div class="col-12 col-lg-5">
                <label class="gs-filter-label">Search Student</label>

                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white">
                        <i class="fa-solid fa-magnifying-glass text-muted"></i>
                    </span>

                    <input
                        type="text"
                        name="q"
                        value="{{ $query }}"
                        class="form-control"
                        placeholder="Search by name or student number..."
                    >

                    <button type="submit" class="btn btn-primary">
                        Search
                    </button>
                </div>
            </div>

            <!-- Grade Level -->
            <div class="col-6 col-lg-3">
                <label class="gs-filter-label">Grade Level</label>

                <select
                    name="grade_level"
                    id="gradeFilter"
                    class="form-select form-select-sm"
                    onchange="this.form.submit()"
                >
                    <option value="">All Grade Levels</option>

                    @foreach ($gradeLevels as $grade)
                        <option
                            value="{{ $grade }}"
                            @selected((string) $gradeLevel === (string) $grade)
                        >
                            Grade {{ $grade }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Section -->
            <div class="col-6 col-lg-3">
                <label class="gs-filter-label">Section</label>

                <select
                    name="section_id"
                    id="sectionFilter"
                    class="form-select form-select-sm"
                    onchange="this.form.submit()"
                >
                    <option value="">All Sections</option>

                    @foreach ($availableSections as $section)
                        <option
                            value="{{ $section->id }}"
                            @selected((string) $sectionId === (string) $section->id)
                        >
                            {{ $section->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Clear -->
            <div class="col-12 col-lg-1">
                <a
                    href="{{ route('teacher.grading-system.student-profile') }}"
                    class="btn btn-sm btn-outline-secondary w-100"
                    title="Clear filters"
                >
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>

        </div>
    </form>


    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h6 class="mb-1 fw-semibold">
                Student Academic Records
            </h6>

            <span class="text-muted small">
                Showing
                <strong>{{ $results->count() }}</strong>
                student(s)
            </span>
        </div>

        @if ($gradeLevel !== '' || $sectionId !== '' || $query !== '')
            <div class="small text-muted">
                <i class="fa-solid fa-filter me-1"></i>
                Filters applied
            </div>
        @endif

    </div>


    <!-- Student Table -->
    <div class="gs-panel">

        <div class="table-panel">

            <table class="table table-hover mb-0 gs-students-table">

                <thead>
                    <tr>
                        <th>Student</th>
                        <th>LRN</th>
                        <th>Grade</th>
                        <th>Section</th>
                        <th>Average</th>
                        <th>Attendance</th>
                        <th>Trend</th>
                        <th>Risk</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($results as $r)

                        <tr>

                            <!-- Student -->
                            <td>
                                <div class="d-flex align-items-center gap-2">

                                    <span
                                        class="gs-profile-avatar"
                                        style="
                                            width: 36px;
                                            height: 36px;
                                            min-width: 36px;
                                            font-size: 0.8rem;
                                        "
                                    >
                                        {{ strtoupper(substr($r->name ?? '?', 0, 1)) }}
                                    </span>

                                    <div>

                                        <div class="fw-semibold">
                                            {{ $r->name }}
                                        </div>

                                        <div class="gs-row-subtext">
                                            {{ $r->student_number }}
                                        </div>

                                    </div>

                                </div>
                            </td>


                            <!-- LRN -->
                            <td>
                                <span class="small">
                                    {{ $r->lrn ?? '—' }}
                                </span>
                            </td>


                            <!-- Grade -->
                            <td>
                                <span class="fw-medium">
                                    Grade {{ $r->grade_level }}
                                </span>
                            </td>


                            <!-- Section -->
                            <td>
                                {{ $r->section_name }}
                            </td>


                            <!-- Average -->
                            <td>
                                @if ($r->average !== null)

                                    <span class="fw-semibold">
                                        {{ number_format($r->average, 1) }}
                                    </span>

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif
                            </td>


                            <!-- Attendance -->
                            <td>

                                @if ($r->attendance !== null)

                                    {{ number_format($r->attendance, 1) }}%

                                @else

                                    <span class="text-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            <!-- Trend -->
                            <td>

                                @php
                                    $trendConfig = [
                                        'Improving' => [
                                            'class' => 'text-success',
                                            'icon' => 'fa-arrow-trend-up',
                                        ],
                                        'Declining' => [
                                            'class' => 'text-danger',
                                            'icon' => 'fa-arrow-trend-down',
                                        ],
                                        'Stable' => [
                                            'class' => 'text-warning',
                                            'icon' => 'fa-minus',
                                        ],
                                        'N/A' => [
                                            'class' => 'text-muted',
                                            'icon' => 'fa-minus',
                                        ],
                                    ];

                                    $trend = $trendConfig[$r->trend]
                                        ?? $trendConfig['N/A'];
                                @endphp

                                <span class="{{ $trend['class'] }} small fw-medium">

                                    <i class="fa-solid {{ $trend['icon'] }} me-1"></i>

                                    {{ $r->trend }}

                                </span>

                            </td>


                            <!-- Risk -->
                            <td>

                                @php
                                    $riskClass = match ($r->risk_level) {
                                        'High' => 'gs-badge-danger',
                                        'Moderate' => 'gs-badge-warning',
                                        'Low' => 'gs-badge-success',
                                        default => 'gs-badge-warning',
                                    };
                                @endphp

                                <span class="gs-badge {{ $riskClass }}">
                                    {{ $r->risk_level }}
                                </span>

                            </td>


                            <!-- Action -->
                            <td class="text-center">

                                <a
                                    href="{{ route(
                                        'teacher.grading-system.student-profile.show',
                                        $r->enrollment_id
                                    ) }}"
                                    class="btn btn-sm btn-outline-secondary"
                                    title="View full academic record and performance"
                                >
                                    <i class="fa-solid fa-graduation-cap me-1"></i>
                                    View Academic Record
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="9"
                                class="text-center text-muted py-5"
                            >

                                <div class="mb-2">
                                    <i class="fa-solid fa-user-graduate fa-2x opacity-50"></i>
                                </div>

                                <div class="fw-medium">
                                    No students found
                                </div>

                                <div class="small mt-1">
                                    No students match your current
                                    search or filters.
                                </div>

                                @if ($gradeLevel !== '' || $sectionId !== '' || $query !== '')

                                    <a
                                        href="{{ route('teacher.grading-system.student-profile') }}"
                                        class="btn btn-sm btn-outline-secondary mt-3"
                                    >
                                        Clear Filters
                                    </a>

                                @endif

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</x-layouts.teacher>