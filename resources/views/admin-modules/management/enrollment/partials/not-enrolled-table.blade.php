<x-ui.table>
    <thead>
        <tr>
            <th style="width: 15%">Student No. </th>
            <th style="width: 70%">Full name</th>

            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($notEnrolledStudents as $student)
            <tr>
                <td>{{ $student->student_number ?? '-' }}</td>
                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal"
                        data-bs-target="#addEnrollmentModal" data-ajax-scope="#not-enrolled-tab"
                        data-student-id="{{ $student->id }}" data-student-number="{{ $student->student_number ?? '' }}"
                        data-student-name="{{ $student->first_name }} {{ $student->last_name }}">
                        Enroll
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center text-muted py-4">No unenrolled students found for the active school year.
                </td>
            </tr>
        @endforelse
    </tbody>
</x-ui.table>

<!-- Pagination -->
<div class="px-3 py-3">
    {{ $notEnrolledStudents->links() }}
</div>