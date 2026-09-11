<x-layouts.school-admin>
    <x-slot name="pageName">
        Academic Setup
    </x-slot>

    <x-slot name="subtitle">Manage curriculum subjects, section setup, and teaching assignments for academic configuration.</x-slot>

    @php
        $activeTab = request('tab');
        if (!$activeTab) {
            if (request()->hasAny(['section_search', 'section_status', 'section_grade_level', 'section_page'])) {
                $activeTab = 'sections';
            } elseif (request()->hasAny(['assignment_search', 'assignment_status', 'assignment_page'])) {
                $activeTab = 'assignments';
            } else {
                $activeTab = 'subjects';
            }
        }

        $academicTabs = [
            ['label' => 'Subjects', 'target' => '#subject-table-pane', 'active' => $activeTab === 'subjects', 'icon' => 'fa-solid fa-book-open'],
            ['label' => 'Sections', 'target' => '#section-table-pane', 'active' => $activeTab === 'sections', 'icon' => 'fa-solid fa-layer-group'],
            ['label' => 'Teaching Assignments', 'target' => '#assignment-table-pane', 'active' => $activeTab === 'assignments', 'icon' => 'fa-solid fa-chalkboard-user'],
        ];
    @endphp

    <x-layouts.school-admin.nav-tabs :tabs="$academicTabs" id="academicTabs" />

    <div class="tab-content mt-2">
        <div class="tab-pane fade {{ $activeTab === 'subjects' ? 'show active' : '' }}" id="subject-table-pane">
            @include('pov.school-admin.academic.subjects')
        </div>
        <div class="tab-pane fade {{ $activeTab === 'sections' ? 'show active' : '' }}" id="section-table-pane">
            @include('pov.school-admin.academic.sections')
        </div>
        <div class="tab-pane fade {{ $activeTab === 'assignments' ? 'show active' : '' }}" id="assignment-table-pane">
            @include('pov.school-admin.teaching-assignments.partials.teaching-assignment-tab')
        </div>
    </div>
</x-layouts.school-admin>