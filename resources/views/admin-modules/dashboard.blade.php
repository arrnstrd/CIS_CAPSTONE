<x-layouts.admin>
    <x-slot name="title">
        Dashboard
    </x-slot>

    <x-slot name="subtitle">
        Overview of school activity.
    </x-slot>

    <x-slot name="pageName">
        Dashboard
    </x-slot>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mb-3">
        @php
            $stats = [
                ['label' => 'Total Students', 'value' => $totalStudents ?? 0, 'icon' => 'fas fa-user-graduate'],
                ['label' => 'Total Teachers', 'value' => $totalTeachers ?? 0, 'icon' => 'fas fa-chalkboard-teacher'],
                ['label' => 'Active Enrollments', 'value' => $activeEnrollments ?? 0, 'icon' => 'fas fa-file-signature'],
                ['label' => 'Scans Today', 'value' => $scansToday ?? 0, 'icon' => 'fas fa-qrcode'],
            ];
        @endphp

        @foreach ($stats as $stat)
            <div class="col">
                <div class="bg-white rounded p-4 border h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-1">{{ $stat['label'] }}</p>
                            <h3 class="fw-bold text-dark mb-0">{{ $stat['value'] }}</h3>
                        </div>
                        <i class="{{ $stat['icon'] }} text-primary opacity-50 fs-4"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="bg-white rounded p-4 border h-100">
                <h5 class="fw-semibold text-dark mb-3">Latest Time In / Time Out Scans</h5>
                @php
                    $scanTypeDotMap = [
                        'IN' => 'success',
                        'OUT' => 'primary',
                    ];
                @endphp
                @if ($latestScans->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-striped mb-0">
                            <thead class="table-light text-uppercase small">
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col">Student</th>
                                    <th scope="col">Grade</th>
                                    <th scope="col">Section</th>
                                    <th scope="col">Scan Type</th>
                                    <th scope="col">Session</th>
                                    <th scope="col">Gate Time</th>
                                    <th scope="col">Flag</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($latestScans as $scan)
                                    @php
                                        $dotScanType = $scanTypeDotMap[$scan['scan_type']] ?? 'secondary';
                                    @endphp
                                    <tr>
                                        <td>{{ $scan['scan_date'] ?? '-' }}</td>
                                        <td class="fw-semibold">{{ $scan['student_name'] ?? 'Unknown' }}</td>
                                        <td>Grade {{ $scan['grade_level'] ?? '-' }}</td>
                                        <td>{{ $scan['section_name'] ?? '-' }}</td>
                                        <td>
                                            <span
                                                class="badge-dot dot-{{ $dotScanType }}">{{ $scan['scan_type'] ?? '-' }}</span>
                                        </td>
                                        <td>{{ $scan['session_type'] ?? '-' }}</td>
                                        <td>{{ $scan['scan_time'] ?? '-' }}</td>
                                        <td class="fw-semibold text-danger">
                                            {{ !empty($scan['flag_types']) ? $scan['flag_types'] : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('time-in-time-out-history.index') }}" class="text-primary text-decoration-none small">
                            <i class="fas fa-history me-1"></i> View full Time In / Time Out history
                        </a>
                    </div>
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                        <i class="fas fa-qrcode fa-2x mb-3 opacity-50"></i>
                        <p class="mb-0">No Time In / Time Out scans recorded yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="bg-white rounded p-4 border h-100">
                <h5 class="fw-semibold text-dark mb-3">Quick Links</h5>
                <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                    <i class="fas fa-link fa-2x mb-3 opacity-50"></i>
                    <p class="mb-0">Nothing here yet.</p>
                </div>
            </div>
        </div>
    </div>

</x-layouts.admin>