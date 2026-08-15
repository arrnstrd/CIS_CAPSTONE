<x-layouts.admin>

    <x-slot name="pageName">
        QR Code Generation
    </x-slot>

    <x-slot name="subtitle">
        Generate printable QR cards for each section
    </x-slot>

    <div class="qr-generation-container row g-4 mb-5">
        {{-- Section Management --}}
        <div class="col-lg-12">
            <div class="bg-white rounded p-4 border mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-semibold text-dark m-0 fs-5">Section List</h3>
                </div>

                <form method="GET">
                    <div class="row g-3 align-items-center">

                        {{-- Search --}}
                        <div class="col-lg-8">
                            <div class="input-group">
                                <input type="search" name="search" class="form-control"
                                    placeholder="Search by section name or grade level..."
                                    value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>

                        {{-- Grade Level --}}
                        <div class="col-lg-4">
                            <select name="grade_level" class="form-select" onchange="this.form.submit()">
                                <option value="">All Grade Levels</option>
                                @for ($grade = 1; $grade <= 12; $grade++)
                                    <option value="{{ $grade }}" {{ request('grade_level') == $grade ? 'selected' : '' }}>
                                        Grade {{ $grade }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                    </div>
                </form>
            </div>

            <x-ui.table>
                <thead class="table-light">
                    <tr>
                        <th>Grade</th>
                        <th>Section</th>
                        <th>Adviser</th>
                        <th>Students</th>
                        <th>Status</th>
                        <th width="180">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sections as $section)
                        <tr>
                            <td>Grade  {{ $section->grade_level }}</td>
                            <td>{{ $section->name }}</td>
                            <td>{{ $section->advisor?->full_name ?? '-' }}</td>
                            <td>{{ $section->students_count }}</td>
                            <td>
                                @if($section->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                               @if($section->students_count > 0)
                                    <a href="{{ route('sections.qr.download', $section) }}"
                                    class="btn btn-success btn-sm">
                                        <i class="fa-solid fa-download me-1"></i>
                                        Download QR
                                    </a>
                                @else
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        No Students
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">No sections available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mx-3 mt-3 mb-3">
                {{ $sections->links() }}
            </div>
        </div>
    </div>

</x-layouts.admin>