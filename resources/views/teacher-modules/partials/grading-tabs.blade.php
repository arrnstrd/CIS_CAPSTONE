@php
    $activeTab = $activeTab ?? 'overview';
@endphp
<div class="gs-tab-bar mb-3">
    <a href="{{ route('teacher.grading-system') }}" class="gs-tab {{ $activeTab === 'overview' ? 'gs-tab-active' : '' }}">Overview</a>
    <a href="{{ route('teacher.grading-system.dashboard') }}" class="gs-tab {{ $activeTab === 'grading' ? 'gs-tab-active' : '' }}">Grading</a>
    <a href="{{ route('teacher.grading-system.analytics') }}" class="gs-tab {{ $activeTab === 'analytics' ? 'gs-tab-active' : '' }}">Analytics</a>
    <a href="{{ route('teacher.grading-system.at-risk') }}" class="gs-tab {{ $activeTab === 'atrisk' ? 'gs-tab-active' : '' }}">At-Risk</a>
    <a href="{{ route('teacher.grading-system.attendance') }}" class="gs-tab {{ $activeTab === 'attendance' ? 'gs-tab-active' : '' }}">Attendance</a>
    <a href="{{ route('teacher.grading-system.by-level') }}" class="gs-tab {{ $activeTab === 'bylevel' ? 'gs-tab-active' : '' }}">By Level</a>
    <a href="{{ route('teacher.grading-system.sections') }}" class="gs-tab {{ $activeTab === 'sections' ? 'gs-tab-active' : '' }}">Sections</a>
    <a href="{{ route('teacher.grading-system.subjects') }}" class="gs-tab {{ $activeTab === 'subjects' ? 'gs-tab-active' : '' }}">Subjects</a>
    <a href="{{ route('teacher.grading-system.student-profile') }}" class="gs-tab {{ $activeTab === 'studentprofile' ? 'gs-tab-active' : '' }}">Student Profile</a>
    <a href="{{ route('teacher.grading-system.comp-rules') }}" class="gs-tab {{ $activeTab === 'comprules' ? 'gs-tab-active' : '' }}">Comp. Rules</a>
</div>