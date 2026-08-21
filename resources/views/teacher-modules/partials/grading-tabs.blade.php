@php
    $activeTab = $activeTab ?? 'myclasses';
@endphp
<div class="gs-tab-bar mb-3">
    <a href="{{ route('teacher.grading-system.dashboard') }}" class="gs-tab {{ $activeTab === 'myclasses' ? 'gs-tab-active' : '' }}">My Classes</a>
    <a href="{{ route('teacher.grading-system.dashboard') }}" class="gs-tab {{ $activeTab === 'gradesheet' ? 'gs-tab-active' : '' }}">Grade Sheet</a>
    <a href="{{ route('teacher.grading-system.import-data') }}" class="gs-tab {{ $activeTab === 'import-data' ? 'gs-tab-active' : '' }}">Import Data</a>
    <a href="{{ route('teacher.grading-system.analytics') }}" class="gs-tab {{ $activeTab === 'analytics' ? 'gs-tab-active' : '' }}">Analytics</a>
    <a href="{{ route('teacher.grading-system.at-risk') }}" class="gs-tab {{ $activeTab === 'atrisk' ? 'gs-tab-active' : '' }}">At-Risk</a>
    <a href="{{ route('teacher.grading-system.reports') }}" class="gs-tab {{ $activeTab === 'reports' ? 'gs-tab-active' : '' }}">Reports</a>
    <a href="{{ route('teacher.grading-system.student-profile') }}" class="gs-tab {{ $activeTab === 'students' ? 'gs-tab-active' : '' }}">Students</a>
    <a href="{{ route('teacher.grading-system.comp-rules') }}" class="gs-tab {{ $activeTab === 'gradingrules' ? 'gs-tab-active' : '' }}">Grading Rules</a>
</div>