<x-layouts.admin>
    <x-slot name="pageName">
        Academic Setup
    </x-slot>

    <x-slot name="subtitle">Manage sections, subjects, and teaching assignments for the academic setup.</x-slot>

    <ul class="modern-tabs mx-2" id="academicTabs" role="tablist">
        <li class="modern-tabs__item">
            <button class="modern-tabs__link active" data-bs-toggle="tab" data-bs-target="#section-table-pane"
                type="button">
                Section
            </button>
        </li>

        <li class="modern-tabs__item">
            <button class="modern-tabs__link" data-bs-toggle="tab" data-bs-target="#subject-table-pane" type="button">
                Subject
            </button>
        </li>

        <li class="modern-tabs__item">
            <button class="modern-tabs__link" data-bs-toggle="tab" data-bs-target="#assignment-table-pane" type="button">
                Teaching Assignment
            </button>
        </li>
    </ul>

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
</x-layouts.admin>