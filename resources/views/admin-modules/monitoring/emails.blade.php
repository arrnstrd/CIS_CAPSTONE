<x-layouts.admin>
    <x-slot name="pageName">
        Email Monitoring
    </x-slot>

    <x-slot name="title">
        Email Monitoring
    </x-slot>

    <div class="main-content mx-3">
        <div class="row mb-3 mx-2">
            <div class="d-flex justify-content-center align-items-center gap-3">
                <x-card title="sent" value="0" icon="fa-solid fa-paper-plane" variants="success" />
                <x-card title="pending" value="0" icon="fa-solid fa-clock" variants="warning" />
                <x-card title="failed" value="0" icon="fa-solid fa-envelope-circle-check" variants="danger" />
            </div>
        </div>

        <div class="col mb-4 mx-2">
            <!-- Maintained your exact padding -->
            <div class="bg-white rounded p-4 pt-5 mx-2 shadow-sm">
                <div class="row g-3 align-items-center">
                    {{-- Search --}}
                    <!-- col-md makes this dynamically stretch to fill all leftover space on the left -->
                    <div class="col-12 col-md">
                        <form action="#" method="GET">
                            <div class="input-group">
                                <input type="search" name="query" class="form-control"
                                    placeholder="Search by student number or name..."
                                    aria-label="Search by student number or name..." />
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Level Filter --}}
                    <!-- col-6 on mobile, hugs its content tightly on tablet/desktop -->
                    <div class="col-6 col-md-auto">
                        <select class="form-select" name="level">
                            <option value="" disabled selected>Status</option>
                            <option value="send">Sent</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>

                    {{-- Scan Type Filter --}}
                    <!-- col-6 on mobile, hugs its content tightly on tablet/desktop -->
                    <div class="col-6 col-md-auto">
                        <select class="form-select" name="scan_type">
                            <option value="" disabled selected>Scan Type</option>
                            <option value="IN">IN</option>
                            <option value="OUT">OUT</option>
                            <option value="RE_ENTRY">RE ENTRY</option>
                            <option value="RE_EXIT">RE EXIT</option>
                        </select>
                    </div>

                    {{-- Resend All Action --}}
                    <!-- Squeezes this column to the exact width of the button text on desktop -->
                    <div class="col-12 col-md-auto">
                        <form action="#" method="POST">
                            {{-- @csrf --}}
                            <button type="submit" class="btn btn-danger w-100 text-nowrap"
                                onclick="return confirm('Are you sure you want to resend all?')">
                                <i class="bi bi-arrow-clockwise"></i>
                                <span>Resend All</span>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <div class="table-section">
            <x-ui.table>
                <thead>
                    <tr>
                        <th style="width: 12%">Date</th>

                        <th style="width: 15% ">Student Name</th>
                        <th style="width: 23%">Guardian Email</th>
                        <th>Scan Type</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($emailLogs as $emailLog)
                        <tr>
                            <td>{{$emailLog->last_attempt_at?->format('Y-m-d')}} </td>

                            <td>{{ $emailLog->student->first_name ?? '' }}
                                {{ $emailLog->student->last_name ?? '' }}
                            </td>
                            <td>{{$emailLog->email}}</td>
                            <td>{{$emailLog->scan_type }}</td>
                            <td>{{ $emailLog->status }}</td>
                            <td> {{ $emailLog->last_attempt_at?->format('h:i A') }}</td>
                            <td> </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No logs yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            {{-- pagination --}}
            <div class="mx-3 mt-3 mb-3">
                {{ $emailLogs->links() }}
            </div>
        </div>

    </div>
</x-layouts.admin>