<x-layouts.teacher>
    <x-slot name="pageName">
        Student Management
    </x-slot>

    <x-slot name="subtitle">
        <span class="ra-page-subtitle">View and manage students in your classes.</span>
    </x-slot>


                    @if (!$sectionId)
                        <div class="card border mx-3 mb-3">
                            <div class="card-body p-4">
                                <h2 class="h5 mb-4">Select a class to view students</h2>
                                <label class="form-label text-muted text-uppercase small fw-bold">My Classes</label>
                                <div class="row g-3">
                                    @forelse ($classCards as $classCard)
                                        @php
                                            $classQuery = request()->except('section_id');
                                            $classQuery['section_id'] = $classCard->id;
                                        @endphp
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <a href="{{ route('teacher.student-management') . '?' . http_build_query($classQuery) }}"
                                                class="text-decoration-none">
                                                <div class="gs-panel">
                                                    <p class="gs-panel-title mb-2">
                                                        Grade {{ $classCard->grade_level }} - {{ $classCard->name }}
                                                    </p>
                                                    <div class="gs-row-subtext">
                                                        <span>
                                                            <i class="fa-solid fa-users me-1"></i>
                                                            {{ $classCard->student_count }} students
                                                        </span>
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
                    @else
                        @php
                            $selectedClass = $classCards->firstWhere('id', $sectionId);
                        @endphp

                        <div class="mx-3">
                            <div class="mb-3">
                            <a href="{{ route('teacher.student-management') }}" class="btn btn-outline-primary mb-3">
                                <i class="fa-solid fa-arrow-left me-1"></i> Back to my classes
                            </a>
                            <p class="ra-section-card-name">
                                Grade {{ $selectedClass?->grade_level }} - {{ $selectedClass?->name }}
                            </p>
                            </div>

                        <div class="card border mb-3">
                            <div class="card-body p-5" style="padding: 1.25rem 1.5rem !important;">
                                <form action="{{ route('teacher.student-management') }}" method="GET">
                                    <input type="hidden" name="section_id" value="{{ $sectionId }}">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label text-muted text-uppercase small fw-bold">Search</label>
                                        <div class="input-group">
                                            <input type="search" name="query" class="form-control" value="{{ request('query') }}"
                                                placeholder="Student number, name or LRN">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="bi bi-search"></i> Search
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <x-ui.table>
                        <thead>
                            <tr>
                                <th style="width: 30%">Student</th>
                                <th style="width: 22%">LRN</th>
                    <th style="width: 28%">Grade & Section</th>
                    <th style="width: 20%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($students as $student)
                    <tr>
                        <td class="table-name-cell">
                            @php
                                $firstName = $student->first_name ?? '';
                                $lastName = $student->last_name ?? '';
                                $studentName = trim($firstName . ' ' . $lastName) ?: '-';
                                $initials = strtoupper(trim(substr($firstName, 0, 1) . substr($lastName, 0, 1))) ?: '--';
                            @endphp
                            <div class="table-name-wrap">
                                <div class="table-name-avatar">{{ $initials }}</div>
                                <div class="table-name-copy">
                                    <span class="table-name-main">{{ $studentName }}</span>
                                    <span class="table-name-sub">{{ $student->student_number ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{ $student->lrn }}</td>
                        <td>
                            @if ($student->section_name)
                                Grade {{ $student->grade_level }} - {{ $student->section_name }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#studentSummaryModal"
                                data-profile-url="{{ route('teacher.student-profile', $student->id) . '?embedded=1' }}">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            No students found for the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        @if (method_exists($students, 'links'))
            <div class="px-3 py-3">
                {{ $students->links() }}
            </div>
        @endif
        </div>
    @endif

    <div class="modal fade" id="studentSummaryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Student Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0" id="studentSummaryModalBody">
                    <p class="text-muted text-center py-4">Loading...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const summaryModal = document.getElementById('studentSummaryModal');
            const body = document.getElementById('studentSummaryModalBody');

            summaryModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const profileUrl = button.getAttribute('data-profile-url');

                body.innerHTML = '<p class="text-muted text-center py-4">Loading...</p>';
                const iframe = document.createElement('iframe');
                iframe.src = profileUrl;
                iframe.title = 'Student Profile';
                iframe.className = 'w-100 border-0';
                iframe.style.minHeight = '70vh';
                iframe.addEventListener('load', function () {
                    body.innerHTML = '';
                    body.appendChild(iframe);
                }, { once: true });
                iframe.addEventListener('error', function () {
                    body.innerHTML = '<p class="text-danger text-center py-4">Unable to load student profile.</p>';
                }, { once: true });
                body.appendChild(iframe);
            });

            summaryModal.addEventListener('hidden.bs.modal', function () {
                body.innerHTML = '<p class="text-muted text-center py-4">Loading...</p>';
            });
        })();
    </script>



</x-layouts.teacher>
