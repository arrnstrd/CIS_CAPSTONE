<x-layouts.teacher>
    <x-slot name="pageName">
        <span class="page-title-icon">
            <i class="fa-solid fa-user-graduate"></i>
            Student Management
        </span>
    </x-slot>

    <x-slot name="subtitle">
        <span class="page-title-subtitle">View and manage students in your classes.</span>
    </x-slot>


                    @if (!$sectionId)
                        <div class="sm-classes-card">
                            <div class="sm-classes-header">
                                <div class="sm-classes-icon-box">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div class="sm-classes-header-text">
                                    <h2 class="sm-classes-title">Select a class to view students</h2>
                                    <span class="sm-classes-subtitle">Choose a section from your assigned classes</span>
                                </div>
                            </div>
                            <div class="sm-classes-grid">
                                @forelse ($classCards as $classCard)
                                    @php
                                        $classQuery = request()->except('section_id');
                                        $classQuery['section_id'] = $classCard->id;
                                    @endphp
                                    <a href="{{ route('teacher.student-management') . '?' . http_build_query($classQuery) }}"
                                        class="sm-class-card-link">
                                        <div class="sm-class-card">
                                            <div class="sm-class-card-main">
                                                <div class="sm-class-avatar">
                                                    {{ $classCard->grade_level }}
                                                </div>
                                                <div class="sm-class-info">
                                                    <p class="sm-class-name">
                                                        Grade {{ $classCard->grade_level }} - {{ $classCard->name }}
                                                    </p>
                                                    <span class="sm-class-count">
                                                        {{ $classCard->student_count }} {{ $classCard->student_count == 1 ? 'student' : 'students' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <i class="fa-solid fa-chevron-right sm-class-chevron"></i>
                                        </div>
                                    </a>
                                @empty
                                    <div class="w-100">
                                        <div class="gs-chart-empty">
                                            <i class="fa-solid fa-chalkboard gs-chart-empty-icon"></i>
                                            <p class="mb-0">No active classes found yet.</p>
                                        </div>
                                    </div>
                                @endforelse
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
                                data-profile-url="{{ route('teacher.student-profile', $student->id) . '?fragment=1' }}">
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

                fetch(profileUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (res) {
                        if (!res.ok) throw new Error('Failed to load');
                        return res.text();
                    })
                    .then(function (html) {
                        body.innerHTML = html;
                        // Re-execute any inline scripts, since innerHTML doesn't run them
                        body.querySelectorAll('script').forEach(function (oldScript) {
                            const newScript = document.createElement('script');
                            if (oldScript.src) {
                                newScript.src = oldScript.src;
                            } else {
                                newScript.textContent = oldScript.textContent;
                            }
                            document.body.appendChild(newScript);
                            oldScript.remove();
                        });
                    })
                    .catch(function () {
                        body.innerHTML = '<p class="text-danger text-center py-4">Unable to load student profile.</p>';
                    });
            });

            summaryModal.addEventListener('hidden.bs.modal', function () {
                body.innerHTML = '<p class="text-muted text-center py-4">Loading...</p>';
            });
        })();
    </script>



</x-layouts.teacher>
