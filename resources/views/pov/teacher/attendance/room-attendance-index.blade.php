<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-clipboard-check"></i>
            Room Attendance
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">Select a section to view and verify today's classroom attendance.</span>
    </x-slot>

    <div class="row g-3">
        @forelse ($sections as $section)
            <div class="col-12 col-md-6">
                <div class="ra-section-card">
                    <span class="ra-grade-pill">Grade {{ $section->grade_level }}</span>
                    <p class="ra-section-card-name mt-2">{{ $section->name }}</p>

                    <div class="ra-enrolled-row">
                        <span class="ra-stat-icon ra-stat-icon-neutral">
                            <i class="fa-solid fa-users"></i>
                        </span>
                        <div>
                            <p class="ra-enrolled-value">{{ $section->total_students }}</p>
                            <p class="ra-enrolled-label">students enrolled</p>
                        </div>
                    </div>

                    <div class="ra-mini-stats">
                        <div class="ra-mini-stat ra-mini-stat-present">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-user-check ra-mini-stat-icon"></i>
                                <div>
                                    <p class="ra-mini-stat-value">{{ $section->present_count }}</p>
                                    <p class="ra-mini-stat-label">Present</p>
                                </div>
                            </div>
                        </div>
                        <div class="ra-mini-stat ra-mini-stat-absent">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-user-xmark ra-mini-stat-icon"></i>
                                <div>
                                    <p class="ra-mini-stat-value">{{ $section->absent_count }}</p>
                                    <p class="ra-mini-stat-label">Absent</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('room-attendance.show', $section) }}" class="btn-view-history w-100 justify-content-center text-decoration-none">
                        Open Section
                    </a>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="ra-filter-bar text-center text-muted py-4">
                    No active teaching assignments found. Contact your admin if this seems wrong.
                </div>
            </div>
        @endforelse
    </div>

    <div class="ra-help-section mt-4">
        <div class="ra-help-header">
            <span class="ra-filter-chip">
                <i class="fa-solid fa-circle-info"></i>
            </span>
            <div>
                <p class="ra-help-title mb-0">How Room Attendance Works</p>
                <p class="ra-help-subtitle mb-0">A quick reference for statuses and daily use</p>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-12 col-lg-6">
                <p class="ra-help-column-label">
                    <i class="fa-solid fa-tag"></i> Status meanings
                </p>
                <div class="ra-status-item">
                    <span class="badge-dot dot-success">Present</span>
                    <span class="ra-status-text">Student scanned in with a valid QR "IN" scan for the day.</span>
                </div>
                <div class="ra-status-item">
                    <span class="badge-dot dot-danger">Absent</span>
                    <span class="ra-status-text">No scan recorded for the day, and attendance tracking has already started for this section.</span>
                </div>
                <div class="ra-status-item">
                    <span class="badge-dot dot-warning">Late / Not in Classroom</span>
                    <span class="ra-status-text">Set manually by the teacher when a student's actual status differs from their scan.</span>
                </div>
                <div class="ra-status-item">
                    <span class="badge-dot dot-info">Excused</span>
                    <span class="ra-status-text">Set manually by the teacher for an approved absence.</span>
                </div>
                <div class="ra-status-item ra-status-item-last">
                    <span class="badge-dot dot-secondary">No Data Yet</span>
                    <span class="ra-status-text">The date falls before scanning ever started for this section, or is a future date. Not counted as absent.</span>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <p class="ra-help-column-label">
                    <i class="fa-solid fa-list-check"></i> How to use this page
                </p>
                <div class="ra-step-item">
                    <span class="ra-step-number">1</span>
                    <span class="ra-status-text">Select a section card below and click <strong>Open Section</strong> to view its full class roster.</span>
                </div>
                <div class="ra-step-item">
                    <span class="ra-step-number">2</span>
                    <span class="ra-status-text">Use the <strong>Today / Yesterday / This Week / Custom</strong> filters to review attendance for a specific date or range.</span>
                </div>
                <div class="ra-step-item">
                    <span class="ra-step-number">3</span>
                    <span class="ra-status-text">On a single-day view, click <strong>Edit</strong> next to a student to manually set or correct their status, with optional remarks.</span>
                </div>
                <div class="ra-step-item">
                    <span class="ra-step-number">4</span>
                    <span class="ra-status-text">Click <strong>View full history</strong> inside the edit dialog to see all past scans and teacher edits for that student.</span>
                </div>
                <div class="ra-step-item ra-status-item-last">
                    <span class="ra-step-number">5</span>
                    <span class="ra-status-text">Multi-day views (This Week / a Custom range spanning more than one day) are read-only — switch to a single-day filter to make edits.</span>
                </div>
            </div>
        </div>
    </div>

</x-layouts.teacher>