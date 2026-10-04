<x-layouts.teacher>
    <x-slot name="pageName">
        Analytics
    </x-slot>
    <x-slot name="subtitle">
        Class performance overview and insights for your assigned classes.
    </x-slot>

    <form method="GET" action="{{ route('teacher.grading-system.analytics') }}" class="gs-filter-bar mb-3">
        <div class="row g-2 align-items-end">
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
                    <p class="gs-grade-card-avg">{{ $overview['class_average'] ?? '—' }}</p>
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
                    <p class="gs-grade-card-avg">—</p>
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

        <div class="row g-3 mt-0">
            <div class="col-12">
                <div class="gs-panel">
                    <p class="gs-panel-title">Assessment Performance</p>
                    @php $maxAssess = max(1, ...array_filter($assessmentPerformance, fn ($v) => $v !== null) ?: [1]); @endphp
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
                <div class="gs-panel">
                    <p class="gs-panel-title">Assessment Performance</p>
                    @php $maxAssess = max(1, ...array_filter($assessmentPerformance, fn ($v) => $v !== null) ?: [1]); @endphp
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
                <div class="gs-panel">
                    <p class="gs-panel-title">Assessment Performance</p>
                    @php $maxAssess = max(1, ...array_filter($assessmentPerformance, fn ($v) => $v !== null) ?: [1]); @endphp
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
                <div class="gs-panel">
                    <p class="gs-panel-title">Assessment Performance</p>
                    @php $maxAssess = max(1, ...array_filter($assessmentPerformance, fn ($v) => $v !== null) ?: [1]); @endphp
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
    @endif
</x-layouts.teacher>