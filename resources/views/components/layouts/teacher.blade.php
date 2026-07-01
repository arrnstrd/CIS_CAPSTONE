<x-layouts.admin>

    <x-slot name="title">
        Teacher Management
    </x-slot>

    <x-slot name="pageName">
        Teacher
    </x-slot>

    <x-slot name="subtitle">
        Manage teacher accounts, contact information, and account status.
    </x-slot>

    <div class="col mb-3 mx-2">
        <div class="bg-white rounded p-4 border">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-semibold text-dark m-0 fs-5">
                    Teacher Records
                </h3>

                <button
                    class="btn btn-dark px-3 py-2 rounded-3 fw-medium d-flex align-items-center gap-1"
                    data-bs-toggle="modal"
                    data-bs-target="#addTeacherModal">

                    <span>+ Add Teacher</span>
                </button>
            </div>

            <form method="GET">

                <div class="row g-3 align-items-center">

                    <div class="col-lg-9">

                        <div class="input-group">

                            <input
                                type="search"
                                name="search"
                                class="form-control"
                                placeholder="Search by employee ID, name or email..."
                                value="{{ request('search') }}">

                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i>
                                Search
                            </button>

                        </div>

                    </div>

                    <div class="col-lg-3">

                        <select
                            name="status"
                            class="form-select"
                            onchange="this.form.submit()">

                            <option value="">
                                All Status
                            </option>

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

    <x-ui.table>

        <x-slot>

            <thead class="text-uppercase">

                <tr>

                    <th width="18%">
                        Employee ID
                    </th>

                    <th width="28%">
                        Full Name
                    </th>

                    <th width="28%">
                        Email
                    </th>

                    <th width="12%">
                        Status
                    </th>

                    <th width="14%">
                        Actions
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse ($teachers as $teacher)

                    <tr>

                        <td>
                            {{ $teacher->employee_id }}
                        </td>

                        <td>
                            {{ $teacher->full_name }}
                        </td>

                        <td>
                            {{ $teacher->email }}
                        </td>

                        <td>
                            {{ ucfirst($teacher->status) }}
                        </td>

                        <td>

                            <div class="dropdown position-static">

                                <button
                                    class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="dropdown">

                                    <i class="fa-solid fa-ellipsis-vertical"></i>

                                </button>

                                <ul class="dropdown-menu">

                                    @if ($teacher->status === 'active')

                                        <li>

                                            <a
                                                href="#"
                                                class="dropdown-item"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editTeacherModal">

                                                Edit

                                            </a>

                                        </li>

                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>

                                        <li>

                                            <form action="#" method="POST">

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    class="dropdown-item text-danger">

                                                    Archive

                                                </button>

                                            </form>

                                        </li>

                                    @else

                                        <li>

                                            <form action="#" method="POST">

                                                @csrf

                                                <button
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

                        <td colspan="5" class="text-center text-muted py-5">

                            <div class="d-flex flex-column align-items-center">

                                <i class="fas fa-folder-open fa-2x mb-3 opacity-50"></i>

                                <p class="mb-0">

                                    No teacher records found.

                                </p>

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </x-slot>

    </x-ui.table>

    <div class="pagination">
        {{ $teachers->links() }}
    </div>

    {{-- Add Teacher Modal --}}
    {{-- TODO --}}

    {{-- Edit Teacher Modal --}}
    {{-- TODO --}}

</x-layouts.admin>