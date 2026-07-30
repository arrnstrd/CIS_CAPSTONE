<x-layouts.admin>
    <x-slot name="pageName">Subjects</x-slot>

    <div class="col mb-3 mx-2">
        <div class="bg-white rounded p-4 border">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-semibold text-dark m-0 fs-5">Subject Records</h3>
                <a href="{{ route('subjects.create') }}" class="btn btn-dark px-3 py-2 rounded-3 fw-medium">
                    + Add Subject
                </a>
            </div>

            <form method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-8">
                        <input type="search" name="subject_search" class="form-control"
                            placeholder="Search subject code, name, or level..."
                            value="{{ request('subject_search') }}">
                    </div>

                    <div class="col-lg-3">
                        <select name="subject_level" class="form-select">
                            <option value="">All Levels</option>
                            @foreach (\App\Models\Subject::levelOptions() as $value => $label)
                                <option value="{{ $value }}" {{ \App\Models\Subject::normalizeLevel(request('subject_level')) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-1">
                        <button class="btn btn-primary w-100" type="submit">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="container-fluid">
        <div class="table-panel shadow-sm">
            <table class="table table-hover align-middle table-striped mb-0">
                <thead class="text-uppercase">
                    <tr>
                        <th>Code</th>
                        <th>Subject</th>
                        <th>Level</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subjects as $subject)
                        <tr>
                            <td>{{ $subject->code }}</td>
                            <td>{{ $subject->name }}</td>
                            <td>{{ $subject->level_label }}</td>
                            <td>
                                <a href="{{ route('subjects.edit', $subject) }}"
                                    class="btn btn-sm btn-outline-secondary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">No subjects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-3 py-3">
                {{ $subjects->links() }}
            </div>
        </div>
    </div>
</x-layouts.admin>