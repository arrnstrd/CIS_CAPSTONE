<x-layouts.admin>
    <x-slot name="pageName">
        Academic Setup
    </x-slot>

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
    </ul>

    <div class="tab-content mt-2">
        <div class="tab-pane fade show active" id="section-table-pane">
            @include('admin-modules.academic.section')
        </div>

        <div class="tab-pane fade" id="subject-table-pane">
            @include('admin-modules.academic.subject')
        </div>
    </div>
</x-layouts.admin>