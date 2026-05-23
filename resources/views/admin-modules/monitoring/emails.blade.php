<x-layouts.admin>
    <x-slot name="pageName">
        Email Monitoring
    </x-slot>

    <x-slot name="title">
        Email Monitoring
    </x-slot>

    <div class="main-content mx-2">
        <div class="row mb-3 mx-2">
            <div class="d-flex justify-content-center align-items-center gap-3">
                <x-card title="sent" value="{{$emailCounts['sent']}}" icon="fa-solid fa-paper-plane" variants="success" />
                <x-card title="pending" value="{{$emailCounts['pending']}}" icon="fa-solid fa-clock" variants="warning" />
                <x-card title="failed" value="{{$emailCounts['failed']}}" icon="fa-solid fa-envelope-circle-check" variants="danger" />
            </div>
        </div>

        <div class="col mb-4 mx-2">
            <div class="bg-white rounded p-4 pt-5 mx-2 shadow-sm">
                <div class="row g-3  align-items-center">
                    <!-- Fixed the form container to act as a proper flex row wrapper on medium/large screens -->
                    <form action="{{ route('emails.index') }}" method="GET"
                        class="col-12 col-md d-md-flex align-items-center m-0 p-0">
                        <div class="col-12 col-md col-lg-8 px-2 mb-2 mb-md-0">
                            <div class="input-group">
                                <input type="search" name="query" class="form-control"
                                    placeholder="Search by student number, student name or email recipient..."
                                    aria-label="Search by student number, student name or email recipient..." value="{{ request('query') }}" />
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>

                        <div class="col-6 col-md-auto col-lg-2 px-2 mb-2 mb-md-0">
                            <select class="form-select" name="status" onchange="this.form.submit()">
                                <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status
                                </option>
                                <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed
                                </option>
                            </select>
                        </div>

                        <div class="col-6  col-md-auto col-lg-2 px-2 mb-2 mb-md-0">
                            <select class="form-select" name="scan_type" onchange="this.form.submit()">
                                <option value="all" {{ request('scan_type', 'all') === 'all' ? 'selected' : '' }}>All Scan
                                    Type</option>
                                <option value="IN" {{ request('scan_type') === 'IN' ? 'selected' : '' }}>IN</option>
                                <option value="OUT" {{ request('scan_type') === 'OUT' ? 'selected' : '' }}>OUT</option>
                                <option value="RE_ENTRY" {{ request('scan_type') === 'RE_ENTRY' ? 'selected' : '' }}>RE
                                    ENTRY</option>
                                <option value="RE_EXIT" {{ request('scan_type') === 'RE_EXIT' ? 'selected' : '' }}>RE EXIT
                                </option>
                            </select>
                        </div>
                    </form>

                    <!-- Resend Button aligns perfectly with the flex layout -->
                    <div class="col-12  col-md-auto mb-3  px-2">
                        <form action="{{route('retryAll.email')}}" method="POST" class="m-0">
                            @csrf
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
                            <td>
                                @if ($emailLog->status == 'failed')
                                    <form action="{{ route('retry.email', $emailLog->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-3">
                                            Resend
                                        </button>
                                    </form>
                                @endif

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