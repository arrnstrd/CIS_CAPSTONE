<x-layouts.admin>
    <x-slot name="pageName">
        Academic Setup
    </x-slot>

    <ul class="modern-tabs mx-2" id="academicTabs" role="tablist">
        <li class="modern-tabs__item">
            <button class="modern-tabs__link active" data-bs-toggle="tab" data-bs-target="#enrollment-table-pane" type="button">
                Enrollment
            </button>
        </li>

        <li class="modern-tabs__item">
            <button class="modern-tabs__link" data-bs-toggle="tab" data-bs-target="#section-table-pane" type="button">
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
        <div class="tab-pane fade show active" id="enrollment-table-pane">
            @include('admin-modules.academic.enrollment')
        </div>

        <div class="tab-pane fade" id="section-table-pane">
            @include('admin-modules.academic.section')
        </div>

        <div class="tab-pane fade" id="subject-table-pane">
            @include('admin-modules.academic.subject')
        </div>
    </div>

    <x-modal>
        <x-slot name="id">addEnrollmentModal</x-slot>
        <x-slot name="modalTitle">Add Enrollment</x-slot>

        <form id="enrollmentForm" action="{{ route('enrollment.store') }}" method="POST" data-ajax-scope="#enrollment-table-pane">
            @csrf
            <div data-ajax-errors></div>

            <div class="mb-3">
                <label class="form-label">Search Student</label>
                <div style="position: relative;">
                    <input type="text" id="enrollmentStudentSearch" class="form-control"
                        placeholder="Search by student number or name..." autocomplete="off">
                    <div id="enrollmentSearchResults" style="
                        display: none;
                        position: absolute;
                        top: 100%; left: 0; right: 0;
                        background: white;
                        border: 1px solid #dee2e6;
                        border-radius: 6px;
                        z-index: 9999;
                        max-height: 200px;
                        overflow-y: auto;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                    "></div>
                </div>
                <input type="hidden" name="student_id" id="enrollmentStudentId">
                <div id="enrollmentSelectedStudent" class="mt-2 px-2 py-1 bg-light rounded d-flex align-items-center gap-2"
                    style="display:none!important;">
                    <small class="text-muted">Selected:</small>
                    <span id="enrollmentSelectedName" class="fw-semibold small"></span>
                    <button type="button" id="enrollmentClearStudent"
                        class="btn btn-sm btn-link text-danger p-0 ms-auto">x</button>
                </div>
                <div class="invalid-feedback">Please select a student from the list.</div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Grade Level</label>
                    <select class="form-select" id="add_grade_level">
                        <option value="" selected disabled>Select grade level</option>
                        @foreach (range(1, 12) as $gradeLevel)
                            <option value="{{ $gradeLevel }}">Grade {{ $gradeLevel }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Section</label>
                    <select class="form-select" name="section_id" id="add_section_id" required disabled>
                        <option value="" disabled selected>Select section</option>
                        @foreach ($activeSections as $section)
                            <option value="{{ $section->id }}" data-grade-level="{{ $section->grade_level }}">
                                Grade {{ $section->grade_level }} - {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Session Type</label>
                    <select class="form-select" name="session_type" required>
                        <option value="" disabled selected>Select session</option>
                        <option value="morning">Morning</option>
                        <option value="afternoon">Afternoon</option>
                        <option value="whole_day">Whole Day</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="" disabled selected>Select status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-dark w-100" data-loading-text="Adding...">Add Enrollment</button>
        </form>
    </x-modal>
</x-layouts.admin>
