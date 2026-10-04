{{-- Assign Teacher Offcanvas Slide-Over Panel --}}
<div class="offcanvas offcanvas-end class-hub-drawer border-0 shadow-lg" tabindex="-1" id="assignTeacherDrawer" aria-labelledby="assignTeacherDrawerLabel">
    {{-- Header --}}
    <div class="offcanvas-header border-bottom py-3 px-4 bg-light-subtle">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="fas fa-user-tag fs-5"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fw-bold text-dark mb-0 fs-6" id="assignTeacherDrawerLabel">Assign Subject Teacher</h5>
                <p class="text-muted small mb-0">Allocate faculty to teach a subject in this section</p>
            </div>
        </div>
        <button type="button" class="btn-close text-secondary shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    {{-- Body --}}
    <div class="offcanvas-body p-4 bg-light-subtle">
        <form id="assignTeacherForm" action="{{ route('teaching-assignments.store') }}" method="POST" data-ajax-form="assignment">
            @csrf

            <div data-ajax-errors></div>

            @php
                $gVal = $selectedGrade ?? $grade ?? 1;
                $sec = $activeSection ?? $section ?? null;
            @endphp

            {{-- Locked Static Context Banner --}}
            <div class="class-hub-context-badge card border-0 bg-primary bg-opacity-10 p-3 mb-3 rounded-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-lock text-primary small"></i>
                        <span class="small fw-semibold text-primary">Class Section Scope</span>
                    </div>
                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small" id="teacherContextBadge">
                        Grade {{ $gVal }} · {{ $sec?->name ?? 'None' }}
                    </span>
                </div>
                <div class="small text-muted mt-1">
                    Teaching assignment will apply to <span class="fw-semibold text-dark" id="teacherSectionText">Section {{ $sec?->name ?? 'N/A' }}</span>.
                </div>
            </div>

            {{-- Hidden Context Inputs --}}
            <input type="hidden" name="section_id" id="assign_teacher_section_id" value="{{ $sec?->id ?? '' }}">

            {{-- Allocation Form Card --}}
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="badge rounded-pill bg-dark">1</span>
                        <h6 class="fw-bold text-dark mb-0 small text-uppercase tracking-wide">Assignment Details</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Academic Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" id="assign_teacher_subject_id" class="form-select form-select-sm" required>
                                <option value="" disabled selected>-- Select Subject --</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" data-level="{{ $subject->level }}">
                                        {{ $subject->name }} ({{ $subject->code }}) — {{ $subject->level_label }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small">Subjects are configured under Academic Setup.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Teacher / Instructor <span class="text-danger">*</span></label>
                            <select name="teacher_id" id="assign_teacher_id" class="form-select form-select-sm" required>
                                <option value="" disabled selected>-- Select Teacher --</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">
                                        {{ $teacher->full_name }} ({{ $teacher->employee_id ?? 'No ID' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">School Year <span class="text-danger">*</span></label>
                            <select name="school_year_id" id="assign_teacher_school_year_id" class="form-select form-select-sm" required>
                                @forelse ($schoolYears as $sy)
                                    <option value="{{ $sy->id }}" {{ $sy->id == $activeSchoolYear?->id ? 'selected' : '' }}>
                                        S.Y. {{ $sy->school_year }} {{ $sy->id == $activeSchoolYear?->id ? '(Active Term)' : '' }}
                                    </option>
                                @empty
                                    <option value="" disabled>No school years available</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Status <span class="text-danger">*</span></label>
                            <select name="status" id="assign_teacher_status" class="form-select form-select-sm" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Anchored Footer --}}
    <div class="offcanvas-footer border-top bg-white p-3 shadow-sm d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-medium small" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" form="assignTeacherForm" class="btn btn-dark px-4 py-2 rounded-3 fw-medium small d-inline-flex align-items-center gap-1.5" data-loading-text="Assigning Teacher...">
            <i class="fas fa-plus fa-sm"></i>
            <span>Assign Teacher</span>
        </button>
    </div>
</div>
