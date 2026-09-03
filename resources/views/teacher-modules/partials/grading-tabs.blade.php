@php
    $activeTab = $activeTab ?? (
        request()->routeIs('teacher.grading-system.dashboard') || request()->routeIs('teacher.grading-system') || request()->routeIs('teacher.grading-system.grades*') || request()->routeIs('teacher.grading-system.grade-sheet*') || request()->routeIs('teacher.grading-system.assessments.*') ? 'myclasses' :
        (request()->routeIs('teacher.grading-system.import-data*') ? 'import-data' :
        (request()->routeIs('teacher.grading-system.analytics*') || request()->routeIs('teacher.grading-system.by-level*') || request()->routeIs('teacher.grading-system.sections*') || request()->routeIs('teacher.grading-system.subjects*') || request()->routeIs('teacher.grading-system.attendance') ? 'analytics' :
        (request()->routeIs('teacher.grading-system.at-risk*') ? 'atrisk' :
        (request()->routeIs('teacher.grading-system.reports*') ? 'reports' :
        (request()->routeIs('teacher.grading-system.student-profile*') || request()->routeIs('teacher.grading-system.students*') ? 'students' :
        (request()->routeIs('teacher.grading-system.grading-rules*') || request()->routeIs('teacher.grading-system.comp-rules*') ? 'gradingrules' : 'myclasses'))))))
    );
@endphp
<div class="gs-tab-bar mb-3">
    <a href="{{ route('teacher.grading-system.dashboard') }}" class="gs-tab {{ $activeTab === 'myclasses' ? 'gs-tab-active' : '' }}">My Classes</a>

    <a href="{{ route('teacher.grading-system.import-data') }}" class="gs-tab {{ $activeTab === 'import-data' ? 'gs-tab-active' : '' }}">Import Data</a>
    <a href="{{ route('teacher.grading-system.analytics') }}" class="gs-tab {{ $activeTab === 'analytics' ? 'gs-tab-active' : '' }}">Analytics</a>
    <a href="{{ route('teacher.grading-system.at-risk') }}" class="gs-tab {{ $activeTab === 'atrisk' ? 'gs-tab-active' : '' }}">At-Risk</a>
    <a href="{{ route('teacher.grading-system.reports') }}" class="gs-tab {{ $activeTab === 'reports' ? 'gs-tab-active' : '' }}">Reports</a>
    <a href="{{ route('teacher.grading-system.student-profile') }}" class="gs-tab {{ $activeTab === 'students' ? 'gs-tab-active' : '' }}">Students</a>
    <a href="{{ route('teacher.grading-system.comp-rules') }}" class="gs-tab {{ $activeTab === 'gradingrules' ? 'gs-tab-active' : '' }}">Grading Rules</a>
</div>