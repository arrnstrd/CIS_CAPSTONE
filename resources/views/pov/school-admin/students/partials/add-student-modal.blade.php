{{-- Add Student Offcanvas Side Panel --}}
<div class="offcanvas offcanvas-end border-0 shadow-lg" tabindex="-1" id="addStudentSidePanel" aria-labelledby="addStudentSidePanelLabel" style="width: 30%; min-width: 360px;">
    {{-- Modern Header --}}
    <div class="offcanvas-header bg-light-subtle border-bottom py-3 px-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="fas fa-user-plus fs-5"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fw-bold text-dark mb-0 fs-6" id="addStudentSidePanelLabel">Add New Student</h5>
                <p class="text-muted small mb-0">Fill in the student, guardian, and section details</p>
            </div>
        </div>
        <button type="button" class="btn-close text-secondary shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    {{-- Scrollable Form Body --}}
    <div class="offcanvas-body p-4 bg-light-subtle">
        <form id="addStudentForm" action="{{ route('student.store') }}" method="POST" data-ajax-form="student">
            @csrf

            <div data-ajax-errors></div>

            {{-- 1. Student Information Card --}}
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="badge rounded-pill bg-dark">1</span>
                        <h6 class="fw-bold text-dark mb-0 small text-uppercase tracking-wide">Student Profile</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">LRN <span class="text-danger">*</span></label>
                            <input type="text" id="add_lrn" name="lrn" class="form-control form-control-sm"
                                placeholder="e.g. 123456789012" inputmode="numeric" maxlength="13" required>
                        </div>

                        <div class="col-8">
                            <label class="form-label small fw-medium text-secondary">First Name <span class="text-danger">*</span></label>
                            <input type="text" id="add_first_name" name="first_name" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-4">
                            <label class="form-label small fw-medium text-secondary">Suffix <span class="text-muted fst-italic">(Opt)</span></label>
                            <input type="text" id="add_suffix" name="suffix" class="form-control form-control-sm" maxlength="20"
                                placeholder="Jr., Sr.">
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Middle Name <span class="text-muted fst-italic">(Opt)</span></label>
                            <input type="text" id="add_middle_name" name="middle_name" class="form-control form-control-sm">
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Last Name <span class="text-danger">*</span></label>
                            <input type="text" id="add_last_name" name="last_name" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Sex <span class="text-danger">*</span></label>
                            <select id="add_sex" name="sex" class="form-select form-select-sm" required>
                                <option value="female">Female</option>
                                <option value="male">Male</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Age <span class="text-danger">*</span></label>
                            <input type="number" id="add_age" name="age" class="form-control form-control-sm" min="1" max="100" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Status <span class="text-danger">*</span></label>
                            <select id="add_status" name="status" class="form-select form-select-sm" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Address <span class="text-danger">*</span></label>
                            <input type="text" id="add_address" name="address" class="form-control form-control-sm"
                                placeholder="House No., Street, Brgy., City" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Guardian Information Card --}}
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="badge rounded-pill bg-dark">2</span>
                        <h6 class="fw-bold text-dark mb-0 small text-uppercase tracking-wide">Guardian Information</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Guardian Name <span class="text-danger">*</span></label>
                            <input type="text" id="add_guardian_name" name="name" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Relationship <span class="text-danger">*</span></label>
                            <select id="add_guardian_relationship" name="relationship" class="form-select form-select-sm" required>
                                <option value="mother">Mother</option>
                                <option value="father">Father</option>
                                <option value="sibling">Sibling</option>
                                <option value="guardian">Guardian</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Contact Number <span class="text-muted fst-italic">(Optional)</span></label>
                            <input type="text" id="add_guardian_contact" name="contact_number" class="form-control form-control-sm"
                                maxlength="20" placeholder="e.g. 0917 123 4567">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="add_guardian_email" name="email" class="form-control form-control-sm" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Enrollment Information Card --}}
            <div class="card border-0 shadow-sm rounded-3 mb-2">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="badge rounded-pill bg-dark">3</span>
                        <h6 class="fw-bold text-dark mb-0 small text-uppercase tracking-wide">Enrollment Details</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">School Year <span class="text-danger">*</span></label>
                            <select id="add_school_year_id" name="school_year_id" class="form-select form-select-sm" required>
                                @forelse ($schoolYears as $sy)
                                    <option value="{{ $sy->id }}" {{ $sy->id == $activeSchoolYear?->id ? 'selected' : '' }}>
                                        {{ $sy->school_year }}
                                    </option>
                                @empty
                                    <option value="" disabled>No school years available</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Grade Level <span class="text-danger">*</span></label>
                            <select id="add_grade_level" name="grade_level" class="form-select form-select-sm" required>
                                @for ($g = 1; $g <= 12; $g++)
                                    <option value="{{ $g }}" {{ (isset($grade) && $g == $grade) ? 'selected' : '' }}>
                                        Grade {{ $g }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Section Assignment <span class="text-danger">*</span></label>
                            <select id="add_section_id" name="section_id" class="form-select form-select-sm" required>
                                <option value="" disabled selected>Select section</option>
                                @foreach ($allSections as $section)
                                    <option value="{{ $section->id }}" data-grade-level="{{ $section->grade_level }}">
                                        Grade {{ $section->grade_level }} — {{ $section->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Enrollment Status <span class="text-danger">*</span></label>
                            <select id="add_enrollment_status" name="enrollment_status" class="form-select form-select-sm" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Anchored Fixed Footer --}}
    <div class="offcanvas-footer border-top bg-white p-3 shadow-sm d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-medium small" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" form="addStudentForm" class="btn btn-dark px-4 py-2 rounded-3 fw-medium small d-inline-flex align-items-center gap-1.5">
            <i class="fas fa-plus fa-sm"></i>
            <span>Save Student</span>
        </button>
    </div>
</div>

{{-- Confirmation Modal --}}
<x-modal>
    <x-slot name="id">confirmAddStudentModal</x-slot>
    <x-slot name="modalTitle">Confirm Student Addition</x-slot>
    <x-slot name="size">modal-md</x-slot>

    <div class="text-center py-3">
        <div class="rounded-circle bg-primary bg-opacity-10 text-primary mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
            <i class="fas fa-user-check fs-3"></i>
        </div>
        <h5 class="fw-bold text-dark mb-2">Are you sure you want to add this student?</h5>
        <p class="text-muted small mb-0 px-3" id="confirmAddStudentDetails">
            Please confirm that the student information and enrollment details are correct before saving.
        </p>
    </div>

    <x-slot name="footer">
        <button type="button" class="btn btn-outline-secondary px-4 rounded-3" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmAddStudentBtn" class="btn btn-dark px-4 rounded-3 fw-medium">
            <i class="fas fa-check me-1.5"></i> Yes, Add Student
        </button>
    </x-slot>
</x-modal>