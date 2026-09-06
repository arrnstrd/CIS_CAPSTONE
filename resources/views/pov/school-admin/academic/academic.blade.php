<x-layouts.school-admin>
    <x-slot name="pageName">
        Academic Setup
    </x-slot>

    <x-slot name="subtitle">Manage sections, subjects, and teaching assignments for the academic setup.</x-slot>

    @php
        $academicTabs = [
            ['label' => 'Section', 'target' => '#section-table-pane', 'active' => true, 'icon' => 'fa-solid fa-layer-group'],
            ['label' => 'Subject', 'target' => '#subject-table-pane', 'icon' => 'fa-solid fa-book-open'],
            ['label' => 'Teaching Assignment', 'target' => '#assignment-table-pane', 'icon' => 'fa-solid fa-chalkboard-user'],
        ];
    @endphp

    <x-layouts.school-admin.nav-tabs :tabs="$academicTabs" id="academicTabs" />

    <div class="tab-content mt-2">
        <div class="tab-pane fade show active" id="section-table-pane">
            @include('pov.school-admin.academic.sections')
        </div>

        <div class="tab-pane fade" id="subject-table-pane">
            @include('pov.school-admin.academic.subjects')
        </div>

        <div class="tab-pane fade" id="assignment-table-pane">
            @include('pov.school-admin.teaching-assignments.partials.teaching-assignment-tab')
        </div>
    </div>
</x-layouts.school-admin>