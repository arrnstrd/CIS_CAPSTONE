@php
    $gdActive = $gdActive ?? 'dashboard';
@endphp
<div class="gd-sidebar">
    <a href="{{ route('teacher.grading-system.dashboard') }}" class="gd-sidebar-link {{ $gdActive === 'dashboard' ? 'gd-sidebar-link-active' : '' }}">
        <i class="fa-solid fa-table-columns"></i> Dashboard
    </a>
    <a href="{{ route('teacher.grading-system.dashboard') }}" class="gd-sidebar-link {{ $gdActive === 'grade-sheet' ? 'gd-sidebar-link-active' : '' }}">
        <i class="fa-solid fa-table"></i> Grade Sheet
    </a>
    <a href="{{ route('teacher.grading-system.import-data') }}" class="gd-sidebar-link {{ $gdActive === 'import-data' ? 'gd-sidebar-link-active' : '' }}">
        <i class="fa-solid fa-file-import"></i> Import Data
    </a>
    <a href="{{ route('teacher.grading-system.reports') }}" class="gd-sidebar-link {{ $gdActive === 'reports' ? 'gd-sidebar-link-active' : '' }}">
        <i class="fa-solid fa-file-lines"></i> Reports
    </a>
    <span class="gd-sidebar-link gd-sidebar-link-disabled" title="See Comp. Rules tab instead">
        <i class="fa-solid fa-gear"></i> Settings
    </span>
</div>