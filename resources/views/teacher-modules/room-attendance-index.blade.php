<x-layouts.teacher>
    <x-slot name="pageName">
        Room Attendance
    </x-slot>

    <x-slot name="subtitle">
        Select a section to view and verify today's classroom attendance.
    </x-slot>

    <div class="row g-3">
        @forelse ($sections as $section)
            <div class="col-12 col-md-6">
                <div class="ra-section-card">
                    <div class="mb-3">
                        <p class="ra-section-card-name">{{ $section->name }}</p>
                        <p class="ra-section-card-meta">{{ $section->total_students }} students enrolled</p>
                    </div>
                    <div class="d-flex gap-4 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="ra-stat-icon ra-stat-icon-present">
                                <i class="fa-solid fa-check"></i>
                            </span>
                            <div>
                                <p class="ra-stat-value ra-stat-present">{{ $section->present_count }}</p>
                                <p class="ra-stat-label">Present</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="ra-stat-icon ra-stat-icon-absent">
                                <i class="fa-solid fa-xmark"></i>
                            </span>
                            <div>
                                <p class="ra-stat-value ra-stat-absent">{{ $section->absent_count }}</p>
                                <p class="ra-stat-label">Absent</p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('room-attendance.show', $section) }}" class="btn-view-history w-100 justify-content-center">
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

</x-layouts.teacher>