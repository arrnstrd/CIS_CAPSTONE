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
                <h5 class="fw-semibold text-dark mb-3">Activity</h5>
                <div class="d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                    <i class="fas fa-chart-line fa-2x mb-3 opacity-50"></i>
                    <p class="mb-0">No activity data available yet.</p>
                </div>
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