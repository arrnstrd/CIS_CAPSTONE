<x-layouts.school-admin>

    <x-slot name="title">Teachers</x-slot>

    <x-slot name="pageName">Teachers</x-slot>

    <x-slot name="subtitle">
        Select a teacher to view their workspace, teaching assignments, and advisory information.
    </x-slot>

    <div class="col mb-3 mx-3">
        <div class="bg-white rounded p-4 border">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-semibold text-dark m-0 fs-5">Teacher Directory</h3>
            </div>

            <form method="GET" id="teacherFilterForm">
                <div class="row g-3 align-items-center">

                    <div class="col-lg-8 col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="fas fa-search fa-sm"></i>
                            </span>
                            <input type="search" name="search" class="form-control border-start-0 ps-0"
                                placeholder="Search by employee ID, name, or email..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-5">
                        <div class="d-flex gap-2">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted">
                                    <i class="fas fa-circle-dot fa-xs"></i>
                                </span>
                                <select name="status" class="form-select border-start-0 ps-0" onchange="document.getElementById('teacherFilterForm').submit()">
                                    <option value="">All Status</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            @if(request('search') || request('status'))
                                <a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary px-2.5 d-inline-flex align-items-center" title="Clear Filters">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <x-ui.table>
        <x-slot>

            <thead class="text-uppercase">
                <tr>
                    <th width="40%">Teacher</th>
                    <th width="32%">Email</th>
                    <th width="14%">Status</th>
                    <th width="14%">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($teachers as $teacher)
                    <tr>
                        @php
                            $teacherFirst = trim($teacher->user?->first_name ?? '');
                            $teacherLast = trim($teacher->user?->last_name ?? '');
                            $teacherName = trim($teacherFirst . ' ' . $teacherLast) ?: ($teacher->full_name ?? '-');
                            $teacherInitials = collect(preg_split('/\s+/', $teacherName, -1, PREG_SPLIT_NO_EMPTY))
                                ->take(2)
                                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                ->implode('');
                        @endphp
                        <td class="table-name-cell">
                            <div class="table-name-wrap">
                                <div class="table-name-avatar" aria-hidden="true">
                                    {{ $teacherInitials ?: 'T' }}
                                </div>
                                <div class="table-name-copy">
                                    <div class="table-name-main">{{ $teacherName }}</div>
                                    <div class="table-name-sub">{{ $teacher->user?->employee_id ?? 'No employee ID' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $teacher->user?->email ?? '-' }}</td>
                        <td>{{ ucfirst($teacher->status ?? 'inactive') }}</td>
                        <td>
                            <a href="{{ route('teachers.show', $teacher->id) }}"
                                class="btn btn-sm btn-outline-secondary">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fas fa-folder-open fa-2x mb-3 opacity-50"></i>
                                <p class="mb-0">No teacher records found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </x-slot>
    </x-ui.table>

    <!-- Pagination -->
    <div class="px-3 py-3">
        {{ $teachers->links() }}
    </div>

</x-layouts.school-admin>
