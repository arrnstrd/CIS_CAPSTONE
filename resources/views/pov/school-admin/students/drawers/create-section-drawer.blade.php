{{-- Create Section Offcanvas Slide-Over Panel --}}
<div class="offcanvas offcanvas-end class-hub-drawer border-0 shadow-lg" tabindex="-1" id="createSectionDrawer" aria-labelledby="createSectionDrawerLabel">
    {{-- Header --}}
    <div class="offcanvas-header border-bottom py-3 px-4 bg-light-subtle">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="fas fa-layer-group fs-5"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fw-bold text-dark mb-0 fs-6" id="createSectionDrawerLabel">Add New Section</h5>
                <p class="text-muted small mb-0">Create a new section under the active Grade Level</p>
            </div>
        </div>
        <button type="button" class="btn-close text-secondary shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    {{-- Body --}}
    <div class="offcanvas-body p-4 bg-light-subtle">
        <form id="createSectionForm" action="{{ route('sections.store') }}" method="POST" data-ajax-form="section">
            @csrf

            <div data-ajax-errors></div>

            @php
                $gVal = $selectedGrade ?? $grade ?? 1;
                $initialLevel = match(true) {
                    $gVal >= 1 && $gVal <= 6 => 'elementary',
                    $gVal >= 7 && $gVal <= 10 => 'highschool',
                    default => 'senior_high_school',
                };
            @endphp

            {{-- Locked Static Context Banner --}}
            <div class="class-hub-context-badge card border-0 bg-primary bg-opacity-10 p-3 mb-3 rounded-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-lock text-primary small"></i>
                        <span class="small fw-semibold text-primary">Active Operational Scope</span>
                    </div>
                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small" id="createSectionGradeBadge">
                        Grade {{ $gVal }}
                    </span>
                </div>
                <div class="small text-muted mt-1">
                    Inherits Grade <span class="fw-semibold text-dark" id="createSectionGradeText">{{ $gVal }}</span>. Section will be provisioned directly under this cohort.
                </div>
            </div>

            {{-- Hidden Pre-filled Values --}}
            <input type="hidden" name="grade_level" id="create_section_grade_level" value="{{ $gVal }}">
            <input type="hidden" name="level" id="create_section_level" value="{{ $initialLevel }}">

            {{-- Section Configuration Card --}}
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="badge rounded-pill bg-dark">1</span>
                        <h6 class="fw-bold text-dark mb-0 small text-uppercase tracking-wide">Section Attributes</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Section Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="create_section_name" class="form-control form-control-sm"
                                placeholder="e.g. Diamond, Archimedes, Rizal" maxlength="40" required>
                            <div class="form-text small">Must be unique within Grade <span id="create_section_grade_label">{{ $gVal }}</span>.</div>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Session Schedule <span class="text-danger">*</span></label>
                            <select name="session_type" id="create_section_session_type" class="form-select form-select-sm" required>
                                <option value="morning">Morning Session</option>
                                <option value="afternoon">Afternoon Session</option>
                                <option value="whole_day" selected>Whole Day</option>
                            </select>
                        </div>

                        <div class="col-6">
                            <label class="form-label small fw-medium text-secondary">Class Capacity <span class="text-danger">*</span></label>
                            <input type="number" name="capacity" id="create_section_capacity" class="form-control form-control-sm"
                                min="1" max="100" value="40" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Status <span class="text-danger">*</span></label>
                            <select name="status" id="create_section_status" class="form-select form-select-sm" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Class Advisor Card (Optional) --}}
            <div class="card border-0 shadow-sm rounded-3 mb-2">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="badge rounded-pill bg-dark">2</span>
                        <h6 class="fw-bold text-dark mb-0 small text-uppercase tracking-wide">Initial Class Adviser (Optional)</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-medium text-secondary">Designate Adviser</label>
                            <select name="advisor_id" id="create_section_advisor_id" class="form-select form-select-sm">
                                <option value="">-- No adviser assigned yet --</option>
                                @foreach ($teachers as $teacher)
                                    @php
                                        $isAssigned = $allSections->contains(fn($s) => $s->advisor_id == $teacher->id);
                                    @endphp
                                    <option value="{{ $teacher->id }}" {{ $isAssigned ? 'disabled' : '' }}>
                                        {{ $teacher->full_name }} {{ $isAssigned ? '(Already an Adviser)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small">Only teachers without an existing advisory section can be assigned.</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Anchored Footer --}}
    <div class="offcanvas-footer border-top bg-white p-3 shadow-sm d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-medium small" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" form="createSectionForm" class="btn btn-dark px-4 py-2 rounded-3 fw-medium small d-inline-flex align-items-center gap-1.5" data-loading-text="Creating Section...">
            <i class="fas fa-plus fa-sm"></i>
            <span>Create Section</span>
        </button>
    </div>
</div>
