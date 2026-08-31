<div class="col mb-3 mx-2">
    <div class="bg-white rounded p-4 border">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <h3 class="fw-semibold text-dark m-0 fs-5">Teaching Assignment Records</h3>
            <button class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#academicAssignmentModal" onclick="openAcademicCreateAssignmentModal()">
                <i class="fas fa-plus fa-sm"></i>
                <span>Add Assignment</span>
            </button>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="table-panel border bg-white shadow-sm">
        <div class="p-3 border-bottom">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="fas fa-search fa-sm"></i>
                        </span>
                        <input type="search" class="form-control border-start-0 ps-0" name="assignment_search"
                            value="{{ request('assignment_search') }}" placeholder="Search by teacher, subject, or section..."
                            data-tab-filter data-tab-scope="#assignment-table-pane" data-page-param="assignment_page">
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="fas fa-circle-dot fa-xs"></i>
                        </span>
                        <select name="assignment_status" class="form-select border-start-0 ps-0" data-tab-filter
                            data-tab-scope="#assignment-table-pane" data-page-param="assignment_page">
                            <option value="">All Status</option>
                            <option value="active" {{ request('assignment_status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('assignment_status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <table class="table table-hover align-middle table-striped mb-0">
            <thead class="text-uppercase">
                <tr>
                    <th width="24%">Teacher</th>
                    <th width="24%">Subject</th>
                    <th width="20%">Section & Grade</th>
                    <th width="14%">School Year</th>
                    <th width="10%">Status</th>
                    <th width="8%">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($teachingAssignments as $assignment)
                    @php
                        $assignStatus = strtolower($assignment->status ?? 'active');
                    @endphp
                    <tr data-assignment-id="{{ $assignment->id }}">
                        <td>
                            <span class="fw-semibold text-dark">{{ $assignment->teacher?->full_name ?? '—' }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $assignment->subject?->name ?? '—' }}</div>
                            @if($assignment->subject?->code)
                                <div class="text-muted small" style="font-size: 0.72rem;">Code: {{ $assignment->subject->code }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $assignment->section?->name ?? '—' }}</span>
                            @if($assignment->section?->grade_level)
                                <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.7rem;">
                                    Grade {{ $assignment->section->grade_level }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="text-secondary small fw-medium">
                                {{ $assignment->schoolYear?->school_year ?? '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-dot dot-{{ $assignStatus === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($assignStatus) }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown position-static">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button class="dropdown-item" type="button"
                                            data-bs-toggle="modal" data-bs-target="#academicAssignmentModal"
                                            onclick="openAcademicEditAssignmentModal({{ $assignment->id }}, {{ $assignment->teacher_id }}, {{ $assignment->subject_id }}, {{ $assignment->section_id }}, {{ $assignment->school_year_id }}, '{{ $assignment->status }}')">
                                            <i class="fas fa-pen fa-xs me-2 text-muted"></i> Edit
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('teaching-assignments.destroy', $assignment->id) }}"
                                            method="POST" data-ajax-delete="assignment" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"
                                                onclick="return confirm('Are you sure you want to delete this teaching assignment?');">
                                                <i class="fas fa-trash fa-xs me-2"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 opacity-50 d-block"></i>
                            No teaching assignments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="p-3 border-top">
            {{ $teachingAssignments->links() }}
        </div>
    </div>
</div>

{{-- Academic Setup Teaching Assignment Modal --}}
<x-modal id="academicAssignmentModal" modalTitle="Teaching Assignment" size="modal-lg">
    <form id="academicAssignmentForm" method="POST" data-ajax-form>
        @csrf
        <input type="hidden" name="_method" value="POST" id="academicAssignmentFormMethod">
        <div data-ajax-errors></div>

        <div class="row g-3">
            {{-- Teacher Selection --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small">Teacher <span class="text-danger">*</span></label>
                <select name="teacher_id" id="acad_field_teacher_id" class="form-select rounded-3" required>
                    <option value="">Select Teacher</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Subject Selection --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small">Subject <span class="text-danger">*</span></label>
                <select name="subject_id" id="acad_field_subject_id" class="form-select rounded-3" required>
                    <option value="">Select Subject</option>
                    @foreach ($allSubjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Section Selection --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small">Section & Grade Level <span class="text-danger">*</span></label>
                <select name="section_id" id="acad_field_section_id" class="form-select rounded-3" required>
                    <option value="">Select Section</option>
                    @foreach ($allSections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }} (Grade {{ $section->grade_level }})</option>
                    @endforeach
                </select>
            </div>

            {{-- School Year Selection --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small">Academic Year <span class="text-danger">*</span></label>
                <select name="school_year_id" id="acad_field_school_year_id" class="form-select rounded-3" required>
                    <option value="">Select School Year</option>
                    @foreach ($allSchoolYears as $sy)
                        <option value="{{ $sy->id }}">{{ $sy->school_year }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Selection --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold text-secondary small">Status <span class="text-danger">*</span></label>
                <select name="status" id="acad_field_status" class="form-select rounded-3" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end mt-4 pt-2 border-top">
            <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-medium" data-bs-dismiss="modal">
                Cancel
            </button>
            <button type="submit" class="btn btn-dark px-4 py-2 rounded-3 fw-medium" id="academicAssignmentSubmitBtn">
                <span id="academicAssignmentSubmitText">Save Assignment</span>
            </button>
        </div>
    </form>
</x-modal>

@push('scripts')
<script>
    function openAcademicCreateAssignmentModal() {
        const form = document.getElementById('academicAssignmentForm');
        const method = document.getElementById('academicAssignmentFormMethod');
        const submitText = document.getElementById('academicAssignmentSubmitText');

        form.action = @json(route('teaching-assignments.store'));
        method.value = 'POST';
        submitText.textContent = 'Save Assignment';

        ['acad_field_teacher_id', 'acad_field_subject_id', 'acad_field_section_id', 'acad_field_school_year_id', 'acad_field_status'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.selectedIndex = 0;
        });
    }

    function openAcademicEditAssignmentModal(assignmentId, teacherId, subjectId, sectionId, schoolYearId, status) {
        const form = document.getElementById('academicAssignmentForm');
        const method = document.getElementById('academicAssignmentFormMethod');
        const submitText = document.getElementById('academicAssignmentSubmitText');

        const updateUrlTemplate = @json(route('teaching-assignments.update', ':id'));
        form.action = updateUrlTemplate.replace(':id', assignmentId);
        method.value = 'PUT';
        submitText.textContent = 'Save Changes';

        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val;
        };

        setVal('acad_field_teacher_id', teacherId);
        setVal('acad_field_subject_id', subjectId);
        setVal('acad_field_section_id', sectionId);
        setVal('acad_field_school_year_id', schoolYearId);
        setVal('acad_field_status', status);
    }
</script>
@endpush
