{{-- Add Student to Section Offcanvas Slide-Over Panel --}}
<div class="offcanvas offcanvas-end class-hub-drawer border-0 shadow-lg" tabindex="-1" id="addStudentDrawer" aria-labelledby="addStudentDrawerLabel">
    {{-- Modern Header --}}
    <div class="offcanvas-header border-bottom py-3 px-4 bg-light-subtle">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="fas fa-user-plus fs-5"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fw-bold text-dark mb-0 fs-6" id="addStudentDrawerLabel">Add Student to Section</h5>
                <p class="text-muted small mb-0">Enroll student directly into the active section</p>
            </div>
        </div>
        <button type="button" class="btn-close text-secondary shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    {{-- Body --}}
    <div class="offcanvas-body p-4 bg-light-subtle">
        <form id="addStudentToSectionForm" action="{{ route('student.store') }}" method="POST" data-ajax-form="student">
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
                        <span class="small fw-semibold text-primary">Pre-Filled Class Context</span>
                    </div>
                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small" id="addStudentContextBadge">
                        Grade {{ $gVal }} · {{ $sec?->name ?? 'None' }}
                    </span>
                </div>
                <div class="small text-muted mt-1">
                    Student will be enrolled directly into <span class="fw-semibold text-dark" id="addStudentSectionText">Section {{ $sec?->name ?? 'N/A' }}</span> (Grade <span id="addStudentGradeText">{{ $gVal }}</span>). No manual selector needed.
                </div>
            </div>

            {{-- Hidden Context Inputs --}}
            <input type="hidden" name="grade_level" id="add_student_grade_level" value="{{ $gVal }}">
            <input type="hidden" name="section_id" id="add_student_section_id" value="{{ $sec?->id ?? '' }}">
            <input type="hidden" name="enrollment_status" value="active">

            {{-- 1. Student Information Card --}}
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="badge rounded-pill bg-dark">1</span>
                        <h6 class="fw-bold text-dark mb-0 small text-uppercase tracking-wide">Student Profile</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Learner Reference Number (LRN) <span class="text-danger">*</span></label>
                            <input type="text" id="add_hub_lrn" name="lrn" class="form-control form-control-sm"
                                placeholder="12-digit DepEd LRN (e.g. 123456789012)" inputmode="numeric" maxlength="12" required>
                        </div>

                        <div class="col-8">
                            <label class="form-label small fw-medium text-secondary">First Name <span class="text-danger">*</span></label>
                            <input type="text" id="add_hub_first_name" name="first_name" class="form-control form-control-sm" placeholder="Juan" required>
                        </div>

                        <div class="col-4">
                            <label class="form-label small fw-medium text-secondary">Suffix <span class="text-muted fst-italic">(Opt)</span></label>
                            <input type="text" id="add_hub_suffix" name="suffix" class="form-control form-control-sm" maxlength="20" placeholder="Jr., III">
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Middle Name <span class="text-muted fst-italic">(Opt)</span></label>
                            <input type="text" id="add_hub_middle_name" name="middle_name" class="form-control form-control-sm" placeholder="Reyes">
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Last Name <span class="text-danger">*</span></label>
                            <input type="text" id="add_hub_last_name" name="last_name" class="form-control form-control-sm" placeholder="Dela Cruz" required>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Sex <span class="text-danger">*</span></label>
                            <select id="add_hub_sex" name="sex" class="form-select form-select-sm" required>
                                <option value="female">Female</option>
                                <option value="male">Male</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Age <span class="text-danger">*</span></label>
                            <input type="number" id="add_hub_age" name="age" class="form-control form-control-sm" min="1" max="100" placeholder="e.g. 13" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Status <span class="text-danger">*</span></label>
                            <select id="add_hub_status" name="status" class="form-select form-select-sm" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Residential Address <span class="text-danger">*</span></label>
                            <input type="text" id="add_hub_address" name="address" class="form-control form-control-sm"
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
                        <h6 class="fw-bold text-dark mb-0 small text-uppercase tracking-wide">Guardian & Emergency Contact</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Guardian Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="add_hub_guardian_name" name="name" class="form-control form-control-sm" placeholder="e.g. Maria Dela Cruz" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Relationship to Student <span class="text-danger">*</span></label>
                            <select id="add_hub_guardian_relationship" name="relationship" class="form-select form-select-sm" required>
                                <option value="mother" selected>Mother</option>
                                <option value="father">Father</option>
                                <option value="guardian">Legal Guardian</option>
                                <option value="sibling">Elder Sibling</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Mobile Contact Number <span class="text-muted fst-italic">(Optional)</span></label>
                            <input type="text" id="add_hub_guardian_contact" name="contact_number" class="form-control form-control-sm"
                                maxlength="20" placeholder="e.g. 0917 123 4567">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Email Address (For Notifications) <span class="text-danger">*</span></label>
                            <input type="email" id="add_hub_guardian_email" name="email" class="form-control form-control-sm" placeholder="guardian@example.com" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Academic Term / School Year Card --}}
            <div class="card border-0 shadow-sm rounded-3 mb-2">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="badge rounded-pill bg-dark">3</span>
                        <h6 class="fw-bold text-dark mb-0 small text-uppercase tracking-wide">Academic Term</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">School Year <span class="text-danger">*</span></label>
                            <select id="add_hub_school_year_id" name="school_year_id" class="form-select form-select-sm" required>
                                @forelse ($schoolYears as $sy)
                                    <option value="{{ $sy->id }}" {{ $sy->id == $activeSchoolYear?->id ? 'selected' : '' }}>
                                        S.Y. {{ $sy->school_year }} {{ $sy->id == $activeSchoolYear?->id ? '(Active Term)' : '' }}
                                    </option>
                                @empty
                                    <option value="" disabled>No active school year</option>
                                @endforelse
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Anchored Footer (No modal confirmation!) --}}
    <div class="offcanvas-footer border-top bg-white p-3 shadow-sm d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-medium small" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" form="addStudentToSectionForm" class="btn btn-dark px-4 py-2 rounded-3 fw-medium small d-inline-flex align-items-center gap-1.5" data-loading-text="Enrolling Student...">
            <i class="fas fa-plus fa-sm"></i>
            <span>Save & Enroll Student</span>
        </button>
    </div>
</div>
