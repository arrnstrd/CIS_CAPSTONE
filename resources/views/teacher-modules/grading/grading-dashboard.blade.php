<x-layouts.teacher>
    <x-slot name="pageName">
        Grading System
    </x-slot>

    <x-slot name="subtitle">
        Overview of your sections' grading performance.
    </x-slot>

    @include('teacher-modules.partials.grading-tabs', ['activeTab' => 'grading'])

    <div class="gd-layout">
        @include('teacher-modules.partials.grading-dashboard-sidebar', ['gdActive' => 'dashboard'])

        <div class="gd-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="gs-panel-title mb-0">My Classes</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
                    <i class="fa-solid fa-plus me-1"></i> Add Class/Section
                </button>
            </div>

            <div class="row g-3">
                @forelse ($classes as $class)
                    <div class="col-12 col-md-6 col-xl-4">
                        <a href="{{ route('teacher.grading-system.grade-sheet', $class->teaching_assignment_id) }}" class="text-decoration-none">
                            <div class="gs-panel">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <p class="gs-panel-title mb-0">Grade {{ $class->grade_level }} - {{ $class->section_name }}</p>
                                    <span class="gs-badge {{ $class->format_badge === 'SHS Format' ? 'gs-badge-warning' : 'gs-badge-success' }}">
                                        {{ $class->format_badge }}
                                    </span>
                                </div>
                                <p class="text-muted small mb-2">{{ $class->subject_name }}</p>

                                <div class="gd-progress-wrap">
                                    <div class="d-flex justify-content-between">
                                        <span class="gd-progress-label">Completion</span>
                                        <span class="gd-progress-label">
                                            {{ $class->completion_percent !== null ? $class->completion_percent . '%' : '—' }}
                                        </span>
                                    </div>
                                    <div class="gd-progress-bar">
                                        <div class="gd-progress-fill" style="width: {{ $class->completion_percent ?? 0 }}%"></div>
                                    </div>
                                </div>

                                <div class="gs-row-subtext">
                                    <span><i class="fa-solid fa-users me-1"></i>{{ $class->learner_count }} learners</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="gs-chart-empty">
                            <i class="fa-solid fa-chalkboard gs-chart-empty-icon"></i>
                            <p class="mb-0">No active classes found yet.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Add Class/Section modal — UI only, submit is a no-op until Section/TeachingAssignment
         write access is confirmed safe. --}}
    <div class="modal fade" id="addClassModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Class/Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addClassForm">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="gd-form-label">Region</label>
                                <input type="text" class="form-control" name="region">
                            </div>
                            <div class="col-6">
                                <label class="gd-form-label">Division</label>
                                <input type="text" class="form-control" name="division">
                            </div>
                            <div class="col-6">
                                <label class="gd-form-label">School Name</label>
                                <input type="text" class="form-control" name="school_name">
                            </div>
                            <div class="col-6">
                                <label class="gd-form-label">School ID</label>
                                <input type="text" class="form-control" name="school_id">
                            </div>
                            <div class="col-6">
                                <label class="gd-form-label">School Year</label>
                                <select class="form-select" name="school_year_id">
                                    @foreach ($schoolYears as $sy)
                                        <option value="{{ $sy->id }}">{{ $sy->school_year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="gd-form-label">Grade Level</label>
                                <input type="text" class="form-control" name="grade_level">
                            </div>
                            <div class="col-6">
                                <label class="gd-form-label">Section</label>
                                <input type="text" class="form-control" name="section">
                            </div>
                            <div class="col-6">
                                <label class="gd-form-label">Subject</label>
                                <input type="text" class="form-select" name="subject">
                            </div>
                            <div class="col-12">
                                <label class="gd-form-label">Grading System</label>
                                <select class="form-select" name="grading_system">
                                    <option value="k10">K-10</option>
                                    <option value="shs">Senior High School</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn gd-btn-primary" id="createClassBtn">Create Class</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('createClassBtn').addEventListener('click', function () {
            // No-op placeholder: Section/TeachingAssignment writes are disabled until
            // the backend owner confirms the schema is stable.
            alert('This feature is not yet available. Class creation will be enabled once the section data flow is finalized.');
        });
    </script>
</x-layouts.teacher>