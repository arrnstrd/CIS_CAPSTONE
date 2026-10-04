<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-chart-line"></i>
            Analytics
        </span>
    </x-slot>
    <x-slot name="subtitle">
        Class performance overview and insights for your assigned classes.
    </x-slot>

    {{-- Analytics Sub-Navigation --}}
    <div class="d-flex align-items-center gap-2 mb-3" data-tour="teacher-analytics-overview">
        <a href="{{ route('teacher.grading-system.analytics') }}" class="btn btn-sm {{ request()->routeIs('teacher.grading-system.analytics*') ? 'btn-primary text-white' : 'btn-outline-secondary' }}" style="border-radius: 20px; font-weight: 600; font-size: 0.82rem; padding: 5px 14px;">
            <i class="fa-solid fa-graduation-cap me-1"></i> Academic Analytics
        </a>
        <a href="{{ route('teacher.grading-system.attendance') }}" class="btn btn-sm {{ request()->routeIs('teacher.grading-system.attendance*') ? 'btn-primary text-white' : 'btn-outline-secondary' }}" style="border-radius: 20px; font-weight: 600; font-size: 0.82rem; padding: 5px 14px;">
            <i class="fa-solid fa-clipboard-user me-1"></i> Attendance Analytics
        </a>
        <a href="{{ route('teacher.grading-system.correlation') }}" class="btn btn-sm {{ request()->routeIs('teacher.grading-system.correlation*') ? 'btn-primary text-white' : 'btn-outline-secondary' }}" style="border-radius: 20px; font-weight: 600; font-size: 0.82rem; padding: 5px 14px;">
            <i class="fa-solid fa-chart-simple me-1"></i> Attendance vs Performance
        </a>
    </div>

    <form method="GET" action="{{ route('teacher.grading-system.analytics') }}" class="gs-filter-bar mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="gs-filter-label">School Year</label>
                <select name="school_year_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All School Years</option>
                    @foreach ($schoolYears as $schoolYear)
                        <option value="{{ $schoolYear->id }}" @selected((string) request('school_year_id') === (string) $schoolYear->id)>
                            {{ $schoolYear->school_year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Student</label>
                <select name="student_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Students</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected((string) request('student_id') === (string) $student->id)>
                            {{ $student->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Grade Level</label>
                <select name="grade_level" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Levels</option>
                    @foreach ($gradeLevels as $gl)
                        <option value="{{ $gl }}" @selected(request('grade_level') == $gl)>Grade {{ $gl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Section</label>
                <select name="section_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Sections</option>
                    @foreach ($sections as $s)
                        <option value="{{ $s->id }}" @selected(request('section_id') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Subject</label>
                <select name="subject_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Subjects</option>
                    @foreach ($subjects as $subj)
                        <option value="{{ $subj->id }}" @selected(request('subject_id') == $subj->id)>{{ $subj->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="gs-filter-label">Term</label>
                <select name="term" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Terms</option>
                    @for ($t = 1; $t <= 3; $t++)
                        <option value="{{ $t }}" @selected(request('term') == $t)>Term {{ $t }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </form>

    @if (! $overview)
        <div class="gs-panel text-center text-muted py-4">
            No active teaching assignments found.
        </div>
    @else
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="gs-stat-card">
                    <p class="gs-grade-card-title">Class Average</p>
                    <p class="gs-grade-card-avg">{{ $overview['class_average'] !== null ? number_format($overview['class_average'], 1) : '—' }}</p>
                    @if (isset($performanceStatus))
                        <span class="gs-status-badge gs-status-{{ $performanceStatus['level'] }}">{{ $performanceStatus['label'] }}</span>
                    @endif
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="gs-stat-card">
                    <p class="gs-grade-card-title">Passing Rate</p>
                    <p class="gs-grade-card-avg">{{ $overview['passing_rate'] !== null ? $overview['passing_rate'].'%' : '—' }}</p>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="gs-stat-card">
                    <p class="gs-grade-card-title">Highest Average</p>
                    <p class="gs-grade-card-avg">{{ $overview['highest'] ?? '—' }}</p>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="gs-stat-card">
                    <p class="gs-grade-card-title">Lowest Average</p>
                    <p class="gs-grade-card-avg">{{ $overview['lowest'] ?? '—' }}</p>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="gs-stat-card">
                    <p class="gs-grade-card-title">Students Assessed</p>
                    <p class="gs-grade-card-avg">{{ $overview['students_assessed'] }}</p>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="gs-stat-card">
                    <p class="gs-grade-card-title">Completion Rate</p>
                    <p class="gs-grade-card-avg">{{ $overview['completion_rate'] !== null ? $overview['completion_rate'].'%' : '—' }}</p>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="gs-panel">
                    <p class="gs-panel-title">Grade Distribution</p>
                    @php $maxCount = max(1, max($gradeDistribution)); @endphp
                    @foreach ($gradeDistribution as $label => $count)
                        <div class="gs-dist-row">
                            <span class="gs-dist-label">{{ $label }}</span>
                            <div class="gs-dist-bar-track">
                                <div class="gs-dist-bar-fill" style="width: {{ $count > 0 ? ($count / $maxCount * 100) : 0 }}%"></div>
                            </div>
                            <span class="gs-dist-count">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="gs-panel">
                    <p class="gs-panel-title">Term Performance Trend</p>
                    @php
                        $maxTrend = max(1, ...array_filter($termTrend, fn ($v) => $v !== null) ?: [1]);
                        $prev = null;
                    @endphp
                    @foreach ($termTrend as $term => $avg)
                        <div class="gs-dist-row">
                            <span class="gs-dist-label">Term {{ $term }}</span>
                            <div class="gs-dist-bar-track">
                                <div class="gs-dist-bar-fill" style="width: {{ $avg !== null ? ($avg / $maxTrend * 100) : 0 }}%"></div>
                            </div>
                            <span class="gs-dist-count">
                                {{ $avg ?? '—' }}
                                @if ($avg !== null && $prev !== null)
                                    @if ($avg > $prev) <i class="fa-solid fa-arrow-up text-success"></i>
                                    @elseif ($avg < $prev) <i class="fa-solid fa-arrow-down text-danger"></i>
                                    @else <i class="fa-solid fa-arrow-right text-muted"></i>
                                    @endif
                                @endif
                            </span>
                        </div>
                        @php $prev = $avg ?? $prev; @endphp
                    @endforeach
                </div>
            </div>
        </div>

        @if ($classHealth)
            <div class="row g-3 mt-0">
                <div class="col-12">
                    <div class="gs-panel gs-health-panel">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <p class="gs-panel-title mb-0">Class Health Indicators</p>
                            @if ($classHealth['has_comparison'])
                                <span class="gs-health-period-badge">Comparing Term {{ $classHealth['current_term'] }} vs Term {{ $classHealth['previous_term'] }}</span>
                            @else
                                <span class="gs-health-period-badge text-muted">Term 1 (Baseline Period)</span>
                            @endif
                        </div>

                        @if ($classHealth['has_comparison'])
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <div class="gs-health-card gs-health-improving">
                                        <div class="gs-health-card-header">
                                            <span class="gs-health-badge"><i class="fa-solid fa-arrow-trend-up"></i> Improving</span>
                                            <span class="gs-health-threshold">&ge; +2.0 pts</span>
                                        </div>
                                        <div class="gs-health-count">{{ $classHealth['improving'] }}</div>
                                        <div class="gs-health-label">{{ Str::plural('student', $classHealth['improving']) }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="gs-health-card gs-health-stable">
                                        <div class="gs-health-card-header">
                                            <span class="gs-health-badge"><i class="fa-solid fa-minus"></i> Stable</span>
                                            <span class="gs-health-threshold">&plusmn;1.9 pts</span>
                                        </div>
                                        <div class="gs-health-count">{{ $classHealth['stable'] }}</div>
                                        <div class="gs-health-label">{{ Str::plural('student', $classHealth['stable']) }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="gs-health-card gs-health-declining">
                                        <div class="gs-health-card-header">
                                            <span class="gs-health-badge"><i class="fa-solid fa-arrow-trend-down"></i> Declining</span>
                                            <span class="gs-health-threshold">&le; -2.0 pts</span>
                                        </div>
                                        <div class="gs-health-count">{{ $classHealth['declining'] }}</div>
                                        <div class="gs-health-label">{{ Str::plural('student', $classHealth['declining']) }}</div>
                                    </div>
                                </div>
                            </div>
                            @if ($classHealth['insufficient_data'] > 0)
                                <div class="gs-health-footer text-muted small mt-2">
                                    <i class="fa-solid fa-circle-info me-1"></i> {{ $classHealth['insufficient_data'] }} {{ Str::plural('student', $classHealth['insufficient_data']) }} excluded due to missing previous term comparison data.
                                </div>
                            @endif
                        @else
                            <div class="gs-health-empty-notice">
                                <i class="fa-solid fa-circle-info text-primary me-2"></i>
                                <span>Term-over-term health comparison is unavailable for Term 1 (baseline period). Indicators will activate in Term 2.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-3 mt-0">
            <div class="col-12">
                <div class="gs-panel">
                    <p class="gs-panel-title">Assessment Performance</p>
                    @php
                        $validAssess = array_filter($assessmentPerformance, fn ($v) => $v !== null);
                        $maxAssess = count($validAssess) ? max($validAssess) : 1;
                    @endphp
                    @foreach ($assessmentPerformance as $label => $avg)
                        <div class="gs-dist-row">
                            <span class="gs-dist-label" style="flex-basis: 160px;">{{ $label }}</span>
                            <div class="gs-dist-bar-track">
                                <div class="gs-dist-bar-fill" style="width: {{ $avg !== null ? ($avg / $maxAssess * 100) : 0 }}%"></div>
                            </div>
                            <span class="gs-dist-count">{{ $avg !== null ? $avg.'%' : '—' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row g-3 mt-0">
            <div class="col-12">
                <div class="gs-panel gs-top-panel">
                    <p class="gs-panel-title">Top Performing Students</p>
                    @if ($topPerformers->isEmpty())
                        <div class="text-muted small py-2">No qualified students found for this period.</div>
                    @elseif ($topPerformers->count() === 1 || request()->filled('grade_level'))
                        @php
                            $singleGroup = $topPerformers->first() ?? collect();
                        @endphp
                        @foreach ($singleGroup as $student)
                            <div class="gs-top-row">
                                <span class="gs-top-rank rank-{{ $student['rank'] }}">{{ $student['rank'] }}</span>
                                <span class="gs-top-name">{{ $student['name'] }}</span>
                                <span class="gs-top-section">{{ $student['section'] }}</span>
                                <span class="gs-top-avg">{{ number_format($student['average'], 1) }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="row g-3">
                            @foreach ($topPerformers as $gradeLevel => $students)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <p class="gs-top-group-label">Grade {{ $gradeLevel }}</p>
                                    @foreach ($students as $student)
                                        <div class="gs-top-row">
                                            <span class="gs-top-rank rank-{{ $student['rank'] }}">{{ $student['rank'] }}</span>
                                            <span class="gs-top-name">{{ $student['name'] }}</span>
                                            <span class="gs-top-section">{{ $student['section'] }}</span>
                                            <span class="gs-top-avg">{{ number_format($student['average'], 1) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if ($sectionComparison->isNotEmpty())
            <div class="row g-3 mt-0">
                <div class="col-12">
                    <div class="gs-panel gs-compare-panel">
                        <p class="gs-panel-title">Section Comparison</p>
                        @foreach ($sectionComparison as $row)
                            <div class="gs-compare-row">
                                <div class="gs-compare-section">
                                    <span>{{ $row['section'] }}</span>
                                    <span class="gs-compare-level-badge">Grade {{ $row['grade_level'] }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-4 flex-wrap ms-auto">
                                    <div class="gs-compare-stat">
                                        <span class="gs-compare-stat-label">Class Average</span>
                                        <span class="gs-compare-stat-value">{{ $row['class_average'] !== null ? $row['class_average'] : '—' }}</span>
                                    </div>
                                    <div class="gs-compare-stat">
                                        <span class="gs-compare-stat-label">Passing Rate</span>
                                        <span class="gs-compare-stat-value">{{ $row['passing_rate'] !== null ? $row['passing_rate'].'%' : '—' }}</span>
                                    </div>
                                    <div class="gs-compare-stat">
                                        <span class="gs-compare-stat-label">Students Assessed</span>
                                        <span class="gs-compare-stat-value">{{ $row['students_assessed'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if ($studentSnapshot && $studentSnapshot['class_average'] !== null)
            <div class="row g-3 mt-0">
                <div class="col-12">
                    <div class="gs-panel">
                        <p class="gs-panel-title">Student Performance Snapshot</p>
                        <div class="d-flex align-items-center gap-4 flex-wrap">
                            <div class="gs-compare-stat">
                                <span class="gs-compare-stat-label">Highest</span>
                                <span class="gs-compare-stat-value">{{ $studentSnapshot['highest'] !== null ? $studentSnapshot['highest'] : '—' }}</span>
                            </div>
                            <div class="gs-compare-stat">
                                <span class="gs-compare-stat-label">Lowest</span>
                                <span class="gs-compare-stat-value">{{ $studentSnapshot['lowest'] !== null ? $studentSnapshot['lowest'] : '—' }}</span>
                            </div>
                            <div class="gs-compare-stat">
                                <span class="gs-compare-stat-label">Class Average</span>
                                <span class="gs-compare-stat-value">{{ $studentSnapshot['class_average'] !== null ? $studentSnapshot['class_average'] : '—' }}</span>
                            </div>
                            <div class="gs-compare-stat">
                                <span class="gs-compare-stat-label">Median</span>
                                <span class="gs-compare-stat-value">{{ $studentSnapshot['median'] !== null ? $studentSnapshot['median'] : '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row g-3 mt-0">
            <div class="col-12">
                <div class="gs-panel gs-insights-panel">
                    <p class="gs-panel-title">Performance Insights</p>
                    @foreach ($performanceInsights as $insight)
                        @php
                            $iconClass = match($insight['type']) {
                                'positive' => 'fa-solid fa-circle-check',
                                'warning' => 'fa-solid fa-triangle-exclamation',
                                default => 'fa-solid fa-circle-info',
                            };
                        @endphp
                        <div class="gs-insight-row insight-{{ $insight['type'] }}">
                            <span class="gs-insight-icon">
                                <i class="{{ $iconClass }}"></i>
                            </span>
                            <span class="gs-insight-text">{{ $insight['text'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</x-layouts.teacher>
