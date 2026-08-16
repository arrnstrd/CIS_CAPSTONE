<x-modal>
    <x-slot name="id">editStudentModal</x-slot>
    <x-slot name="modalTitle">Edit Student</x-slot>
    <x-slot name="size">modal-xl</x-slot>

    <form id="editStudentForm" action="" method="POST" data-ajax-form="student">
        @csrf
        @method('PUT')

        <div data-ajax-errors></div>

        {{-- Stepper header --}}
        <ul class="nav nav-pills mb-4 gap-2">
            <li class="nav-item">
                <span class="nav-link active" data-step-label="1">
                    <i class="fas fa-user me-1"></i> Student &amp; Guardian
                </span>
            </li>
            <li class="nav-item">
                <span class="nav-link" data-step-label="2">
                    <i class="fas fa-graduation-cap me-1"></i> Enrollment
                </span>
            </li>
        </ul>

        {{-- STEP 1: Student + Guardian --}}
        <div data-step-panel="1">
            <div class="row g-3">
                <div class="col-lg-7">
                    <h6 class="fw-semibold text-muted text-uppercase small mb-3">Student Information</h6>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">LRN</label>
                            <input type="text" id="edit_lrn" name="lrn" class="form-control"
                                placeholder="e.g. 123456789012" inputmode="numeric" maxlength="13" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Suffix <span
                                    class="text-muted fst-italic">(Optional)</span></label>
                            <input type="text" id="edit_suffix" name="suffix" class="form-control" maxlength="20"
                                placeholder="e.g. Jr., Sr., III">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Middle Name <span
                                    class="text-muted fst-italic">(Optional)</span></label>
                            <input type="text" id="edit_middle_name" name="middle_name" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" id="edit_last_name" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sex</label>
                            <select id="edit_sex" name="sex" class="form-select" required>
                                <option value="female">Female</option>
                                <option value="male">Male</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Age</label>
                            <input type="number" id="edit_age" name="age" class="form-control" min="1" max="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select id="edit_status" name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <input type="text" id="edit_address" name="address" class="form-control"
                                placeholder="House No., Street, Brgy., City" required>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <h6 class="fw-semibold text-muted text-uppercase small mb-3">Guardian Information</h6>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Guardian Name</label>
                            <input type="text" id="edit_guardian_name" name="name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Relationship</label>
                            <select id="edit_guardian_relationship" name="relationship" class="form-select" required>
                                <option value="mother">Mother</option>
                                <option value="father">Father</option>
                                <option value="sibling">Sibling</option>
                                <option value="guardian">Guardian</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Contact Number <span
                                    class="text-muted fst-italic">(Optional)</span></label>
                            <input type="text" id="edit_guardian_contact" name="contact_number" class="form-control"
                                maxlength="20" placeholder="e.g. 0917 123 4567">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" id="edit_guardian_email" name="email" class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-dark px-4" data-next-step>
                    Next <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>

        {{-- STEP 2: Enrollment --}}
        <div data-step-panel="2" class="d-none">
            <h6 class="fw-semibold text-muted text-uppercase small mb-3">Enrollment Information</h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">School Year</label>
                    <select id="edit_school_year_id" name="school_year_id" class="form-select" required>
                        @forelse ($schoolYears as $sy)
                            <option value="{{ $sy->id }}">{{ $sy->school_year }}</option>
                        @empty
                            <option value="" disabled>No school years available</option>
                        @endforelse
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Grade Level</label>
                    <select id="edit_grade_level" name="grade_level" class="form-select" required>
                        @for ($g = 1; $g <= 12; $g++)
                            <option value="{{ $g }}">Grade {{ $g }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Section</label>
                    <select id="edit_section_id" name="section_id" class="form-select" required>
                        <option value="" disabled selected>Select section</option>
                        @foreach ($allSections as $section)
                            <option value="{{ $section->id }}" data-grade-level="{{ $section->grade_level }}">
                                Grade {{ $section->grade_level }} — {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Enrollment Status</label>
                    <select id="edit_enrollment_status" name="enrollment_status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary px-4" data-prev-step>
                    <i class="fas fa-arrow-left me-1"></i> Back
                </button>
                <button type="submit" class="btn btn-dark px-4">Save Changes</button>
            </div>
        </div>
    </form>
</x-modal>