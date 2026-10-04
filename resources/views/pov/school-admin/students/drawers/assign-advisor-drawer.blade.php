{{-- Assign Advisor Offcanvas Slide-Over Panel --}}
<div class="offcanvas offcanvas-end class-hub-drawer border-0 shadow-lg" tabindex="-1" id="assignAdvisorDrawer" aria-labelledby="assignAdvisorDrawerLabel">
    {{-- Header --}}
    <div class="offcanvas-header border-bottom py-3 px-4 bg-light-subtle">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="fas fa-chalkboard-user fs-5"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fw-bold text-dark mb-0 fs-6" id="assignAdvisorDrawerLabel">Class Adviser Assignment</h5>
                <p class="text-muted small mb-0">Designate the primary faculty adviser for this section</p>
            </div>
        </div>
        <button type="button" class="btn-close text-secondary shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    {{-- Body --}}
    <div class="offcanvas-body p-4 bg-light-subtle">
        @php
            $gVal = $selectedGrade ?? $grade ?? 1;
            $sec = $activeSection ?? $section ?? null;
        @endphp

        <form id="assignAdvisorForm" action="{{ $sec ? route('sections.update-advisor', $sec->id) : '#' }}" method="POST" data-ajax-form="advisor">
            @csrf
            @method('PUT')

            <div data-ajax-errors></div>

            {{-- Locked Static Context Banner --}}
            <div class="class-hub-context-badge card border-0 bg-primary bg-opacity-10 p-3 mb-3 rounded-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-lock text-primary small"></i>
                        <span class="small fw-semibold text-primary">Class Cohort Scope</span>
                    </div>
                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small" id="advisorContextBadge">
                        Grade {{ $gVal }} · {{ $sec?->name ?? 'None' }}
                    </span>
                </div>
                <div class="small text-muted mt-1">
                    Assigning adviser for <span class="fw-semibold text-dark" id="advisorSectionText">Section {{ $sec?->name ?? 'N/A' }}</span>.
                </div>
            </div>

            {{-- Current Adviser Status Card --}}
            <div class="card border-0 shadow-sm rounded-3 mb-3">
                <div class="card-body p-3.5">
                    <h6 class="fw-bold text-dark mb-2 small text-uppercase tracking-wide">Current Designation</h6>
                    <div class="d-flex align-items-center gap-3 p-2.5 rounded-3 bg-light border">
                        <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center fw-bold small" style="width: 38px; height: 38px;" id="currentAdvisorAvatar">
                            @if ($sec?->advisor)
                                {{ strtoupper(substr($sec->advisor->first_name ?? '', 0, 1) . substr($sec->advisor->last_name ?? '', 0, 1)) }}
                            @else
                                <i class="fas fa-user-slash text-muted small"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-dark text-truncate small" id="currentAdvisorName">
                                {{ $sec?->advisor?->full_name ?? 'No adviser assigned' }}
                            </div>
                            <div class="text-muted small text-truncate" style="font-size: 0.75rem;" id="currentAdvisorEmail">
                                {{ $sec?->advisor?->user?->email ?? 'Class currently has no primary faculty in charge' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Adviser Selection Card --}}
            <div class="card border-0 shadow-sm rounded-3 mb-2">
                <div class="card-body p-3.5">
                    <h6 class="fw-bold text-dark mb-2 small text-uppercase tracking-wide">Select Faculty Member</h6>

                    <div class="mb-3">
                        <label class="form-label small fw-medium text-secondary">Class Adviser <span class="text-danger">*</span></label>
                        <select name="advisor_id" id="assign_advisor_id" class="form-select form-select-sm">
                            <option value="">-- Remove Adviser / Unassigned --</option>
                            @foreach ($teachers as $teacher)
                                @php
                                    $alreadyAdvising = $allSections->first(fn($s) => $s->advisor_id == $teacher->id && $s->id !== $sec?->id);
                                    $isCurrent = $sec?->advisor_id == $teacher->id;
                                @endphp
                                <option value="{{ $teacher->id }}"
                                    {{ $isCurrent ? 'selected' : '' }}
                                    {{ $alreadyAdvising ? 'disabled' : '' }}
                                    data-teacher-name="{{ $teacher->full_name }}"
                                    data-teacher-email="{{ $teacher->user?->email ?? '' }}">
                                    {{ $teacher->full_name }}
                                    @if ($isCurrent)
                                        (Current Adviser)
                                    @elseif ($alreadyAdvising)
                                        (Already advising: Grade {{ $alreadyAdvising->grade_level }} - {{ $alreadyAdvising->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small">
                            A teacher can only be assigned as the primary adviser of one active section.
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Anchored Footer --}}
    <div class="offcanvas-footer border-top bg-white p-3 shadow-sm d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary px-3 py-2 rounded-3 fw-medium small" data-bs-dismiss="offcanvas">Cancel</button>
        <button type="submit" form="assignAdvisorForm" class="btn btn-dark px-4 py-2 rounded-3 fw-medium small d-inline-flex align-items-center gap-1.5" data-loading-text="Updating Adviser...">
            <i class="fas fa-check fa-sm"></i>
            <span>Confirm Adviser</span>
        </button>
    </div>
</div>
