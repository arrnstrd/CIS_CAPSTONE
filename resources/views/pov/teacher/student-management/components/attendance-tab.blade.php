@props(['student'])

<div class="tab-pane fade" id="attendance" role="tabpanel">

    <p class="text-uppercase text-muted fw-semibold mb-3" style="font-size: 10px; letter-spacing: 0.07em;">
        Attendance Summary
    </p>

    <x-ui.table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time In</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            {{-- Loop attendance logs here --}}
            <tr>
                <td colspan="3" class="text-center text-muted py-4">
                    <i class="fa-regular fa-calendar-xmark me-2"></i>
                    No attendance records found.
                </td>
            </tr>
        </tbody>
    </x-ui.table>

</div>