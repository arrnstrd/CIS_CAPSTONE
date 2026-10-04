{{-- Edit Student Offcanvas Slide-Over Panel --}}
<div class="offcanvas offcanvas-end class-hub-drawer border-0 shadow-lg" tabindex="-1" id="editStudentDrawer" aria-labelledby="editStudentDrawerLabel">
    {{-- Header --}}
    <div class="offcanvas-header border-bottom py-3 px-4 bg-light-subtle">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-secondary bg-opacity-10 text-dark p-2.5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="fas fa-user-pen fs-5"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fw-bold text-dark mb-0 fs-6" id="editStudentDrawerLabel">Edit Student Record</h5>
                <p class="text-muted small mb-0">Update personal, guardian, and enrollment information</p>
            </div>
        </div>
        <button type="button" class="btn-close text-secondary shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    {{-- Body --}}
    <div class="offcanvas-body p-4 bg-light-subtle">
        <form id="editStudentForm" method="POST" data-ajax-form="student">
            @csrf
            @method('PUT')

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
                            <input type="text" id="edit_hub_lrn" name="lrn" class="form-control form-control-sm"
                                inputmode="numeric" maxlength="12" required>
                        </div>

                        <div class="col-8">
                            <label class="form-label small fw-medium text-secondary">First Name <span class="text-danger">*</span></label>
                            <input type="text" id="edit_hub_first_name" name="first_name" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-4">
                            <label class="form-label small fw-medium text-secondary">Suffix <span class="text-muted fst-italic">(Opt)</span></label>
                            <input type="text" id="edit_hub_suffix" name="suffix" class="form-control form-control-sm" maxlength="20">
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Middle Name <span class="text-muted fst-italic">(Opt)</span></label>
                            <input type="text" id="edit_hub_middle_name" name="middle_name" class="form-control form-control-sm">
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Last Name <span class="text-danger">*</span></label>
                            <input type="text" id="edit_hub_last_name" name="last_name" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Sex <span class="text-danger">*</span></label>
                            <select id="edit_hub_sex" name="sex" class="form-select form-select-sm" required>
                                <option value="female">Female</option>
                                <option value="male">Male</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Age <span class="text-danger">*</span></label>
                            <input type="number" id="edit_hub_age" name="age" class="form-control form-control-sm" min="1" max="100" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Status <span class="text-danger">*</span></label>
                            <select id="edit_hub_status" name="status" class="form-select form-select-sm" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Address <span class="text-danger">*</span></label>
                            <input type="text" id="edit_hub_address" name="address" class="form-control form-control-sm" required>
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
                            <input type="text" id="edit_hub_guardian_name" name="name" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Relationship <span class="text-danger">*</span></label>
                            <select id="edit_hub_guardian_relationship" name="relationship" class="form-select form-select-sm" required>
                                <option value="mother">Mother</option>
                                <option value="father">Father</option>
                                <option value="sibling">Sibling</option>
                                <option value="guardian">Guardian</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Contact Number</label>
                            <input type="text" id="edit_hub_guardian_contact" name="contact_number" class="form-control form-control-sm" maxlength="20">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="edit_hub_guardian_email" name="email" class="form-control form-control-sm" required>
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
                            <select id="edit_hub_school_year_id" name="school_year_id" class="form-select form-select-sm" required>
                                @foreach ($schoolYears as $sy)
                                    <option value="{{ $sy->id }}">{{ $sy->school_year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Grade Level <span class="text-danger">*</span></label>
                            <select id="edit_hub_grade_level" name="grade_level" class="form-select form-select-sm" required>
                                @for ($g = 1; $g <= 12; $g++)
                                    <option value="{{ $g }}">Grade {{ $g }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Section <span class="text-danger">*</span></label>
                            <select id="edit_hub_section_id" name="section_id" class="form-select form-select-sm" required>
                                @foreach ($allSections as $sec)
                                    <option value="{{ $sec->id }}" data-grade-level="{{ $sec->grade_level }}">
                                        Grade {{ $sec->grade_level }} — {{ $sec->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Enrollment Status <span class="text-danger">*</span></label>
                            <select id="edit_hub_enrollment_status" name="enrollment_status" class="form-select form-select-sm" required>
                                <option value="active">Active</option>
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
        <button type="submit" form="editStudentForm" class="btn btn-dark px-4 py-2 rounded-3 fw-medium small d-inline-flex align-items-center gap-1.5" data-loading-text="Updating Student...">
            <i class="fas fa-check fa-sm"></i>
            <span>Update Student</span>
        </button>
    </div>
</div>
