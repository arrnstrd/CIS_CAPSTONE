@props(['student', 'attendance' => []])

<div class="tab-pane fade" id="attendance" role="tabpanel">

    <p class="text-uppercase text-muted fw-semibold mb-3" style="font-size: 10px; letter-spacing: 0.07em;">
        Attendance Summary
    </p>

    <form method="GET" action="{{ request()->url() }}" class="d-flex gap-1 mb-2 flex-wrap">
        @foreach (['today'=>'Today','yesterday'=>'Yesterday','week'=>'Week'] as $val=>$label)
            <button type="submit" name="date_filter" value="{{ $val }}" class="btn btn-sm btn-outline-secondary {{ request('date_filter',$val)==$val ? 'active' : '' }}">{{ $label }}</button>
        @endforeach
        <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm w-auto" />
        <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm w-auto" />
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        <a href="{{ request()->url() }}" class="btn btn-sm btn-link">Reset</a>
    </form>

    <x-ui.table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time In</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendance ?? [] as $record)
                <tr>
                    <td>{{ $record->date }}</td>
                    <td>{{ $record->time_in ?? '-' }}</td>
                    <td>{{ $record->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">
                        <i class="fa-regular fa-calendar-xmark me-2"></i>
                        No attendance records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-ui.table>

</div>