<x-layouts.admin>
    <x-slot name="pageName">
        Email Monitoring
    </x-slot>

    <x-slot name="title">
        Email Monitoring
    </x-slot>

    <div class="main-content mx-2">
        <!-- Overview Cards Section -->


        <div class="row mb-3 mx-4">
            <div class="d-flex justify-content-center align-items center gap-3">
                <x-card title="total emails today" value="{{ $emailCounts['total'] ?? 0 }} " icon="fas fa-envelope"
                    variants="primary" />
                <x-card title="sent emails today" value="{{ $emailCounts['sent'] ?? 0 }} " icon="fas fa-paper-plane"
                    variants="success" />
                <x-card title="pending emails today" value="{{ $emailCounts['pending'] ?? 0 }} "
                    icon="fa-solid fa-hourglass-half" variants="warning" />
                <x-card title="failed emails today" value="{{ $emailCounts['failed'] ?? 0 }} "
                    icon="fas fa-times-circle" variants="danger" />
            </div>
        </div>


        <!-- Filters and Controls Section -->
        <div class="col mb-4 mx-2">
            <div class="bg-white rounded p-4 shadow-sm">

                {{-- Main GET filter form --}}
                <form action="{{ route('emails.index') }}" method="GET" id="filterForm">

                    {{-- ROW 1: Search (col-7) | Status (col-2) | Scan Type (col-2) --}}
                    <div class="row g-2 pt-2 align-items-center mb-3">
                        <div class="col-12 col-lg-8">
                             <label class="form-label text-muted text-uppercase small fw-bold">Search</label>
                            <div class="input-group">
                                <input type="search" name="query" class="form-control"
                                    placeholder="Search by student, email..." value="{{ request('query') }}" />
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>

                        <div class="col-6 col-lg-2">
                              <label class="form-label text-muted text-uppercase small fw-bold">status</label>
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

                        <div class="col-6 col-lg-2">
                              <label class="form-label text-muted text-uppercase small fw-bold">scan type</label>
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
                    </div>

                    {{-- ROW 2: Date filter (left) | Custom range (expands inline) | Help + Resend All (right, fixed)
                    --}}
                    <div class="row g-2 align-items-center">

                        {{-- Configuration --}}
                        @php
                            $filters = [
                                'today' => 'Today',
                                'week' => 'This Week',
                                'month' => 'This Month',
                                'custom' => 'Custom'
                            ];
                            $currentFilter = request('date_filter', 'today');
                        @endphp

                        {{-- Date Filter Pills --}}
                        <div class="col-12 col-md-auto">
                            <div class="btn-group" role="group" aria-label="Date Filter">
                                @foreach($filters as $value => $label)
                                    <input type="radio" class="btn-check" name="date_filter" id="date_{{ $value }}"
                                        value="{{ $value }}" @checked($currentFilter === $value)
                                        onchange="this.form.submit()">

                                    <label class="btn btn-outline-primary nav-pill rounded px-3 me-2" for="date_{{ $value }}">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Custom Range Fields --}}
                        @if ($currentFilter === 'custom')
                            <div class="col-12 col-md-auto d-flex gap-2 animate__animated animate__fadeIn">
                                <input type="date" name="custom_start_date" class="form-control nav-pill"
                                    value="{{ request('custom_start_date') }}" required />

                                <input type="date" name="custom_end_date" class="form-control nav-pill"
                                    value="{{ request('custom_end_date') }}" required />

                                <button type="submit" class="btn btn-success nav-pill px-3">
                                    <i class="bi bi-funnel"></i> Apply
                                </button>
                            </div>
                        @endif

                        {{-- Help button (right side, ms-auto pushes it to the end) --}}
                        <div class="col ms-auto d-flex gap-2 justify-content-end">
                          
                        <a href="{{ route('emails.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                            {{-- Resend All — triggers a separate hidden POST form via JS to avoid form nesting --}}
                            <button type="button" class="btn btn-outline-danger" onclick="confirmResendAll()">
                                <i class="bi bi-arrow-clockwise"></i> Resend All
                            </button>
                        </div>

                        

                    </div>

                </form>

                {{-- Separate POST form for Resend All, outside the GET form --}}
                <form id="resendAllForm" action="{{ route('retryAll.email') }}" method="POST" class="d-none">
                    @csrf
                </form>

            </div>
        </div>

        <script>
            function confirmResendAll() {
                if (confirm('Are you sure you want to resend all failed emails?')) {
                    document.getElementById('resendAllForm').submit();
                }
            }
        </script>

        <!-- Email Logs Table Section -->
        <x-ui.table>
            <thead>
                <tr>
                    <th style="width: 12%">Date</th>
                    <th style="width: 15%">Student Name</th>
                    <th style="width: 20%">Recipient Email</th>
                    <th style="width: 12%">Scan Type</th>
                    <th style="width: 12%">Status</th>
                    <th style="width: 12%">Time</th>
                    <th style="width: 15%">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($emailLogs as $emailLog)
                    <tr>
                        <td>
                            {{ $emailLog->last_attempt_at?->format('M d, Y') }}
                        </td>

                        <td>
                            <span class="fw-500">
                                {{ $emailLog->student?->first_name ?? '' }}
                                {{ $emailLog->student?->last_name ?? '' }}
                            </span>
                        </td>

                        <td>
                            <span class="text-break">{{ $emailLog->email }}</span>
                        </td>

                        <td>
                            <span class="badge bg-light text-dark">{{ $emailLog->scan_type }}</span>
                        </td>

                        <td>
                            @php
                                $statusColors = [
                                    'sent' => 'success',
                                    'failed' => 'danger',
                                    'pending' => 'warning'
                                ];
                                $statusColor = $statusColors[$emailLog->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }}">
                                {{ ucfirst($emailLog->status) }}
                            </span>
                        </td>

                        <td>
                            {{ $emailLog->last_attempt_at?->format('h:i A') }}
                        </td>

                        <td>
                            @if ($emailLog->status === 'failed')
                                <form action="{{ route('retry.email', $emailLog->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3"
                                        onclick="return confirm('Retry sending this email?')">
                                        <i class="bi bi-arrow-repeat"></i> Retry
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">No actions</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <i class="fas fa-inbox fa-2x mb-3 opacity-50"></i>
                                <p class="mb-0">No email logs found for the selected criteria</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>

        <!-- Pagination -->
        <div class=" mx-2 mt-3 ">
            {{ $emailLogs->links() }}
        </div>

    </div>


</x-layouts.admin>