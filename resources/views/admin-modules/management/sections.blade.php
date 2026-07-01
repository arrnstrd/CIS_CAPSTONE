<x-layouts.admin>
    <x-slot name="title">
        Section List
    </x-slot>

    <x-slot name="pageName">
        Section
    </x-slot>

    <x-slot name="subtitle">
        Manage school sections, advisers, and enrollment capacity.
    </x-slot>

    {{-- Filters / Search --}}
    <div class="col mb-3 mx-2">
        <div class="bg-white rounded p-4 border">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-semibold text-dark m-0 fs-5">
                    Section Records
                </h3>

                <button
                    class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                    data-bs-toggle="modal"
                    data-bs-target="#addSectionModal">

                    <span>+ Add Section</span>
                </button>
            </div>

            <form method="GET">

                <div class="row g-3 align-items-center">

                    {{-- Search --}}
                    <div class="col-lg-6">
                        <div class="input-group">
                            <input
                                type="search"
                                name="search"
                                class="form-control"
                                placeholder="Search by section name or grade level..."
                                value="{{ request('search') }}">

                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i>
                                Search
                            </button>
                        </div>
                    </div>

                    {{-- Grade Level --}}
                    <div class="col-lg-3">
                        <select
                            name="grade_level"
                            class="form-select"
                            onchange="this.form.submit()">

                            <option value="">All Grade Levels</option>

                            @for ($grade = 1; $grade <= 12; $grade++)
                                <option
                                    value="{{ $grade }}"
                                    {{ request('grade_level') == $grade ? 'selected' : '' }}>
                                    Grade {{ $grade }}
                                </option>
                            @endfor

                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-lg-3">
                        <select
                            name="status"
                            class="form-select"
                            onchange="this.form.submit()">

                            <option value="">All Status</option>

                            <option
                                value="active"
                                {{ request('status') == 'active' ? 'selected' : '' }}>
                                Active
                            </option>

                            <option
                                value="inactive"
                                {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>

                        </select>
                    </div>

                </div>

            </form>

        </div>
    </div>

    {{-- Table --}}
    <x-ui.table>

        <thead class="text-uppercase">

            <tr>

                <th width="20%">
                    <i class="fas fa-layer-group me-1"></i>
                    Section
                </th>

                <th width="10%">
                    <i class="fas fa-graduation-cap me-1"></i>
                    Grade
                </th>

                <th width="15%">
                    <i class="fas fa-school me-1"></i>
                    Level
                </th>

                <th width="22%">
                    <i class="fas fa-user-tie me-1"></i>
                    Adviser
                </th>

                <th width="10%">
                    <i class="fas fa-users me-1"></i>
                    Capacity
                </th>

                <th width="10%">
                    <i class="fas fa-circle me-1"></i>
                    Status
                </th>

                <th width="8%">
                    <i class="fas fa-sliders-h me-1"></i>
                    Actions
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse ($sections as $section)

                <tr>

                    <td>
                        {{ $section->name }}
                    </td>

                    <td>
                        Grade {{ $section->grade_level }}
                    </td>

                    <td>
                        {{ Str::headline(str_replace('_', ' ', $section->level)) }}
                    </td>

                    <td>
                        {{ $section->advisor?->full_name ?? 'Not Assigned' }}
                    </td>

                    <td>
                        {{ $section->capacity }}
                    </td>

                    <td>

                        @if ($section->status === 'active')

                            <span class="badge bg-success">
                                Active
                            </span>

                        @else

                            <span class="badge bg-secondary">
                                Inactive
                            </span>

                        @endif

                    </td>

                    <td>

                        <div class="dropdown position-static">

                            <button
                                class="btn btn-sm btn-outline-secondary"
                                type="button"
                                data-bs-toggle="dropdown">

                                <i class="fa-solid fa-ellipsis-vertical"></i>

                            </button>

                            <ul class="dropdown-menu">

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="#">
                                        View
                                    </a>
                                </li>

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editSectionModal">

                                        Edit

                                    </a>
                                </li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                @if ($section->status === 'active')

                                    <li>

                                        <form
                                            action="#"
                                            method="POST">

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="dropdown-item text-danger">

                                                Archive

                                            </button>

                                        </form>

                                    </li>

                                @else

                                    <li>

                                        <form
                                            action="#"
                                            method="POST">

                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="dropdown-item text-success">

                                                Restore

                                            </button>

                                        </form>

                                    </li>

                                @endif

                            </ul>

                        </div>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="7" class="text-center text-muted py-5">

                        <div class="d-flex flex-column align-items-center">

                            <i class="fas fa-folder-open fa-2x opacity-50 mb-3"></i>

                            <p class="mb-0">
                                No sections found for the selected criteria.
                            </p>

                        </div>

                    </td>

                </tr>

            @endforelse

        </tbody>

    </x-ui.table>

    <div class="pagination">
        {{ $sections->links() }}
    </div>

</x-layouts.admin>