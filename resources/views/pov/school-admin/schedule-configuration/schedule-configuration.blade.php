<x-layouts.school-admin>
    <x-slot name="pageName">
        Schedule Configuration
    </x-slot>

    <x-slot name="subtitle">Configure Time In and Time Out attendance schedules and scan windows for the QR scanning stations.</x-slot>

    @php
        $levels = [
            'elementary' => [
                'name' => 'Elementary Level',
                'grades' => 'Grades 1 – 6',
                'icon' => 'fa-solid fa-shapes',
                'color' => '#2563eb',
                'bg' => '#eff6ff',
            ],
            'hs' => [
                'name' => 'Junior High School',
                'grades' => 'Grades 7 – 10',
                'icon' => 'fa-solid fa-book-open',
                'color' => '#0891b2',
                'bg' => '#ecfeff',
            ],
            'shs' => [
                'name' => 'Senior High School',
                'grades' => 'Grades 11 – 12',
                'icon' => 'fa-solid fa-graduation-cap',
                'color' => '#6366f1',
                'bg' => '#eef2ff',
            ],
        ];

        $sessionTypeLabels = [
            'morning' => 'Morning',
            'afternoon' => 'Afternoon',
            'whole_day' => 'Whole Day',
        ];

        $sessionOrder = [
            'morning' => 1,
            'afternoon' => 2,
            'whole_day' => 3,
        ];
    @endphp

    <style>
        .sched-level-card {
            background: #fdfdfdff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04), 0 1px 3px rgba(15, 23, 42, 0.03);
            margin-bottom: 1.75rem;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }

        .sched-level-card:hover {
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.06), 0 2px 5px rgba(15, 23, 42, 0.04);
        }

        .sched-level-header {
            background: #f8fafc;
            border-bottom: 1px solid #e9eff5;
            padding: 1.15rem 1.5rem;
        }

        .sched-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }

        .sched-session-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            padding: 1.25rem;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04), 0 1px 2px rgba(15, 23, 42, 0.02);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .sched-session-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.07), 0 2px 6px rgba(15, 23, 42, 0.04);
            transform: translateY(-1px);
        }

        .sched-time-box {
            border-radius: 0.75rem;
            padding: 0.9rem 1.1rem;
            border: 1px solid transparent;
            height: 100%;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .sched-time-box:hover {
            transform: translateY(-1px);
        }

        .sched-time-box--entry {
            background: #f0fdf4;
            border-color: #bbf7d0;
            box-shadow: 0 2px 6px rgba(34, 197, 94, 0.06);
        }

        .sched-time-box--late {
            background: #fffbeb;
            border-color: #fef08a;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.06);
        }

        .sched-time-box--exit {
            background: #fef2f2;
            border-color: #fecaca;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.06);
        }

        .sched-time-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .sched-time-value {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0.25rem;
            margin-bottom: 0.15rem;
        }

        .sched-time-subtext {
            font-size: 0.72rem;
            color: #64748b;
        }
    </style>

    <div class="main-content mx-3 pb-4">

        @foreach ($levels as $levelKey => $levelMeta)
            @php
                $levelConfigs = $scheduleConfigs->where('level', $levelKey)->sortBy(function ($cfg) use ($sessionOrder) {
                    return $sessionOrder[$cfg->session_type] ?? 99;
                });
                $sessionCount = $levelConfigs->count();

                $sessionLabels = $levelConfigs->pluck('session_type')
                    ->map(fn($t) => $sessionTypeLabels[$t] ?? ucfirst($t))
                    ->unique()
                    ->values();

                if ($sessionLabels->isEmpty()) {
                    $dynamicSubtitle = "{$levelMeta['grades']} · No sessions configured";
                } elseif ($sessionLabels->count() === 1) {
                    $dynamicSubtitle = "{$levelMeta['grades']} · {$sessionLabels->first()} schedule";
                } else {
                    $last = $sessionLabels->pop();
                    $joined = $sessionLabels->implode(', ') . ' & ' . $last;
                    $dynamicSubtitle = "{$levelMeta['grades']} · {$joined} schedules";
                }

                // Dynamic column classes (auto-adjusts to 3 across when 3 sessions exist)
                $colClass = match($sessionCount) {
                    2 => 'col-12 col-md-6',
                    default => 'col-12 col-md-6 col-xl-4',
                };
            @endphp

            <div class="sched-level-card">
                @if ($sessionCount === 1)
                    {{-- ================================================================= --}}
                    {{-- CASE A: SINGLE SESSION LEVEL (e.g. Elementary / 1 Schedule)       --}}
                    {{-- The container directly hosts the single schedule (no nested card) --}}
                    {{-- ================================================================= --}}
                    @php
                        $singleSched = $levelConfigs->first();
                    @endphp

                    <div class="sched-level-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="sched-icon-box" style="background-color: {{ $levelMeta['bg'] }}; color: {{ $levelMeta['color'] }};">
                                <i class="{{ $levelMeta['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="mb-0 fw-bold text-dark">{{ $levelMeta['name'] }}</h5>
                                    <span data-field-badge="session_type">
                                        @if($singleSched->session_type === 'morning')
                                            <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle px-2.5 py-1 rounded-pill">
                                                <i class="fa-solid fa-sun me-1"></i> Morning Session
                                            </span>
                                        @elseif($singleSched->session_type === 'afternoon')
                                            <span class="badge bg-info bg-opacity-10 text-info-emphasis border border-info-subtle px-2.5 py-1 rounded-pill">
                                                <i class="fa-solid fa-cloud-sun me-1"></i> Afternoon Session
                                            </span>
                                        @else
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2.5 py-1 rounded-pill">
                                                <i class="fa-solid fa-calendar-day me-1"></i> Whole Day Session
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <span class="text-muted small">{{ $dynamicSubtitle }}</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#addScheduleModal" data-level="{{ $levelKey }}">
                                <i class="fa-solid fa-plus me-1"></i> Add Session
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                data-edit-toggle="sched-{{ $singleSched->id }}">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Schedule
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger js-delete-schedule"
                                data-bs-toggle="modal" data-bs-target="#deleteScheduleModal"
                                data-id="{{ $singleSched->id }}"
                                data-level="{{ $singleSched->level }}"
                                data-session-type="{{ $singleSched->session_type }}"
                                data-level-label="{{ $levelMeta['name'] }}"
                                data-session-type-label="{{ $sessionTypeLabels[$singleSched->session_type] ?? $singleSched->session_type }}"
                                title="Delete Schedule">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-4" data-edit-container="sched-{{ $singleSched->id }}" data-sched-id="{{ $singleSched->id }}">
                        {{-- VIEW MODE --}}
                        <div data-edit-view="sched-{{ $singleSched->id }}">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <div class="sched-time-box sched-time-box--entry">
                                        <div class="sched-time-label text-success">
                                            <i class="fa-solid fa-right-to-bracket me-1"></i> Entry Scan Window
                                        </div>
                                        <div class="sched-time-value" data-field-display="in_window">
                                            {{ $singleSched->in_start ? \Carbon\Carbon::parse($singleSched->in_start)->format('g:i A') : '—' }} – {{ $singleSched->in_end ? \Carbon\Carbon::parse($singleSched->in_end)->format('g:i A') : '—' }}
                                        </div>
                                        <span class="sched-time-subtext">Earliest to latest allowed scan-in</span>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="sched-time-box sched-time-box--late">
                                        <div class="sched-time-label text-warning-emphasis">
                                            <i class="fa-solid fa-clock me-1 text-warning"></i> Late Threshold
                                        </div>
                                        <div class="sched-time-value text-warning-emphasis" data-field-display="late_threshold">
                                            {{ $singleSched->late_threshold ? \Carbon\Carbon::parse($singleSched->late_threshold)->format('g:i A') : '—' }}
                                        </div>
                                        <span class="sched-time-subtext">Marked late after this time</span>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="sched-time-box sched-time-box--exit">
                                        <div class="sched-time-label text-danger">
                                            <i class="fa-solid fa-right-from-bracket me-1"></i> Exit Scan Window
                                        </div>
                                        <div class="sched-time-value" data-field-display="out_window">
                                            {{ $singleSched->out_start ? \Carbon\Carbon::parse($singleSched->out_start)->format('g:i A') : '—' }} – {{ $singleSched->out_end ? \Carbon\Carbon::parse($singleSched->out_end)->format('g:i A') : '—' }}
                                        </div>
                                        <span class="sched-time-subtext">Earliest to latest allowed scan-out</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- INLINE EDIT FORM --}}
                        <form class="d-none" data-edit-form="sched-{{ $singleSched->id }}"
                              action="{{ route('schedconfig.update', $singleSched->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="level" value="{{ $singleSched->level }}">

                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-dark mb-0">
                                        <i class="fa-solid fa-pen-to-square text-primary me-1"></i> Edit {{ $levelMeta['name'] }} Schedule
                                    </h6>
                                    <div class="col-auto">
                                        <select name="session_type" class="form-select form-select-sm fw-semibold" required>
                                            <option value="morning" {{ $singleSched->session_type === 'morning' ? 'selected' : '' }}>Morning Session</option>
                                            <option value="afternoon" {{ $singleSched->session_type === 'afternoon' ? 'selected' : '' }}>Afternoon Session</option>
                                            <option value="whole_day" {{ $singleSched->session_type === 'whole_day' ? 'selected' : '' }}>Whole Day Session</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card p-3 border-0 bg-white shadow-sm h-100">
                                            <div class="fw-bold text-success small text-uppercase mb-2">
                                                <i class="fa-solid fa-right-to-bracket me-1"></i> Entry &amp; Late Settings
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted fw-semibold">Entry Start</label>
                                                    <input type="time" name="in_start" class="form-control"
                                                        value="{{ $singleSched->in_start ? \Carbon\Carbon::parse($singleSched->in_start)->format('H:i') : '' }}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted fw-semibold">Entry End</label>
                                                    <input type="time" name="in_end" class="form-control"
                                                        value="{{ $singleSched->in_end ? \Carbon\Carbon::parse($singleSched->in_end)->format('H:i') : '' }}" required>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <label class="form-label small text-muted fw-semibold">Late Threshold</label>
                                                    <input type="time" name="late_threshold" class="form-control"
                                                        value="{{ $singleSched->late_threshold ? \Carbon\Carbon::parse($singleSched->late_threshold)->format('H:i') : '' }}" required>
                                                    <div class="form-text small">Students scanning after this time are marked late.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card p-3 border-0 bg-white shadow-sm h-100">
                                            <div class="fw-bold text-danger small text-uppercase mb-2">
                                                <i class="fa-solid fa-right-from-bracket me-1"></i> Exit Scan Settings
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted fw-semibold">Exit Start</label>
                                                    <input type="time" name="out_start" class="form-control"
                                                        value="{{ $singleSched->out_start ? \Carbon\Carbon::parse($singleSched->out_start)->format('H:i') : '' }}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted fw-semibold">Exit End</label>
                                                    <input type="time" name="out_end" class="form-control"
                                                        value="{{ $singleSched->out_end ? \Carbon\Carbon::parse($singleSched->out_end)->format('H:i') : '' }}" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-danger small mb-3" data-edit-error="sched-{{ $singleSched->id }}"></div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary px-4"
                                    data-edit-cancel="sched-{{ $singleSched->id }}">Cancel</button>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fa-solid fa-check me-1"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                @elseif ($sessionCount > 1)
                    {{-- ================================================================= --}}
                    {{-- CASE B: MULTI-SESSION LEVEL (2, 3, or more session cards in grid) --}}
                    {{-- ================================================================= --}}
                    <div class="sched-level-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="sched-icon-box" style="background-color: {{ $levelMeta['bg'] }}; color: {{ $levelMeta['color'] }};">
                                <i class="{{ $levelMeta['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="mb-0 fw-bold text-dark">{{ $levelMeta['name'] }}</h5>
                                    <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill">
                                        {{ $sessionCount }} {{ \Illuminate\Support\Str::plural('Session', $sessionCount) }}
                                    </span>
                                </div>
                                <span class="text-muted small">{{ $dynamicSubtitle }}</span>
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal" data-bs-target="#addScheduleModal" data-level="{{ $levelKey }}">
                            <i class="fa-solid fa-plus me-1"></i> Add Session
                        </button>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">
                            @foreach ($levelConfigs as $sched)
                                <div class="{{ $colClass }}">
                                    <div class="sched-session-card"
                                         data-edit-container="sched-{{ $sched->id }}"
                                         data-sched-id="{{ $sched->id }}">

                                        {{-- VIEW MODE --}}
                                        <div data-edit-view="sched-{{ $sched->id }}">
                                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span data-field-badge="session_type">
                                                        @if($sched->session_type === 'morning')
                                                            <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle px-2.5 py-1.5 rounded-pill">
                                                                <i class="fa-solid fa-sun me-1"></i> Morning Session
                                                            </span>
                                                        @elseif($sched->session_type === 'afternoon')
                                                            <span class="badge bg-info bg-opacity-10 text-info-emphasis border border-info-subtle px-2.5 py-1.5 rounded-pill">
                                                                <i class="fa-solid fa-cloud-sun me-1"></i> Afternoon Session
                                                            </span>
                                                        @else
                                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2.5 py-1.5 rounded-pill">
                                                                <i class="fa-solid fa-calendar-day me-1"></i> Whole Day Session
                                                            </span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="d-flex align-items-center gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2"
                                                        data-edit-toggle="sched-{{ $sched->id }}" title="Edit Schedule">
                                                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 js-delete-schedule"
                                                        data-bs-toggle="modal" data-bs-target="#deleteScheduleModal"
                                                        data-id="{{ $sched->id }}"
                                                        data-level="{{ $sched->level }}"
                                                        data-session-type="{{ $sched->session_type }}"
                                                        data-level-label="{{ $levelMeta['name'] }}"
                                                        data-session-type-label="{{ $sessionTypeLabels[$sched->session_type] ?? $sched->session_type }}"
                                                        title="Delete Schedule">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="row g-2">
                                                <div class="col-12 col-sm-6">
                                                    <div class="sched-time-box sched-time-box--entry">
                                                        <div class="sched-time-label text-success">
                                                            <i class="fa-solid fa-right-to-bracket me-1"></i> Entry Window
                                                        </div>
                                                        <div class="sched-time-value" data-field-display="in_window">
                                                            {{ $sched->in_start ? \Carbon\Carbon::parse($sched->in_start)->format('g:i A') : '—' }} – {{ $sched->in_end ? \Carbon\Carbon::parse($sched->in_end)->format('g:i A') : '—' }}
                                                        </div>
                                                        <span class="sched-time-subtext">Allowed scan-in</span>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-sm-6">
                                                    <div class="sched-time-box sched-time-box--exit">
                                                        <div class="sched-time-label text-danger">
                                                            <i class="fa-solid fa-right-from-bracket me-1"></i> Exit Window
                                                        </div>
                                                        <div class="sched-time-value" data-field-display="out_window">
                                                            {{ $sched->out_start ? \Carbon\Carbon::parse($sched->out_start)->format('g:i A') : '—' }} – {{ $sched->out_end ? \Carbon\Carbon::parse($sched->out_end)->format('g:i A') : '—' }}
                                                        </div>
                                                        <span class="sched-time-subtext">Allowed scan-out</span>
                                                    </div>
                                                </div>

                                                <div class="col-12 mt-2">
                                                    <div class="sched-time-box sched-time-box--late d-flex align-items-center justify-content-between">
                                                        <div>
                                                            <div class="sched-time-label text-warning-emphasis">
                                                                <i class="fa-solid fa-clock me-1 text-warning"></i> Late Threshold
                                                            </div>
                                                            <div class="sched-time-value text-warning-emphasis" data-field-display="late_threshold">
                                                                {{ $sched->late_threshold ? \Carbon\Carbon::parse($sched->late_threshold)->format('g:i A') : '—' }}
                                                            </div>
                                                        </div>
                                                        <span class="sched-time-subtext text-end">
                                                            Marked late after this time
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- INLINE EDIT FORM --}}
                                        <form class="d-none" data-edit-form="sched-{{ $sched->id }}"
                                              action="{{ route('schedconfig.update', $sched->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="level" value="{{ $sched->level }}">

                                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                                <h6 class="mb-0 fw-bold text-dark">
                                                    <i class="fa-solid fa-pen-to-square text-primary me-1"></i> Edit Schedule
                                                </h6>
                                                <div class="col-auto">
                                                    <select name="session_type" class="form-select form-select-sm fw-semibold" required>
                                                        <option value="morning" {{ $sched->session_type === 'morning' ? 'selected' : '' }}>Morning Session</option>
                                                        <option value="afternoon" {{ $sched->session_type === 'afternoon' ? 'selected' : '' }}>Afternoon Session</option>
                                                        <option value="whole_day" {{ $sched->session_type === 'whole_day' ? 'selected' : '' }}>Whole Day Session</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row g-2 mb-3">
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold mb-1 text-success">
                                                        <i class="fa-solid fa-right-to-bracket me-1"></i> Entry Start
                                                    </label>
                                                    <input type="time" name="in_start" class="form-control form-control-sm"
                                                        value="{{ $sched->in_start ? \Carbon\Carbon::parse($sched->in_start)->format('H:i') : '' }}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold mb-1 text-success">
                                                        <i class="fa-solid fa-right-to-bracket me-1"></i> Entry End
                                                    </label>
                                                    <input type="time" name="in_end" class="form-control form-control-sm"
                                                        value="{{ $sched->in_end ? \Carbon\Carbon::parse($sched->in_end)->format('H:i') : '' }}" required>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label small fw-semibold mb-1 text-warning-emphasis">
                                                        <i class="fa-solid fa-clock me-1 text-warning"></i> Late Threshold
                                                    </label>
                                                    <input type="time" name="late_threshold" class="form-control form-control-sm"
                                                        value="{{ $sched->late_threshold ? \Carbon\Carbon::parse($sched->late_threshold)->format('H:i') : '' }}" required>
                                                </div>

                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold mb-1 text-danger">
                                                        <i class="fa-solid fa-right-from-bracket me-1"></i> Exit Start
                                                    </label>
                                                    <input type="time" name="out_start" class="form-control form-control-sm"
                                                        value="{{ $sched->out_start ? \Carbon\Carbon::parse($sched->out_start)->format('H:i') : '' }}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold mb-1 text-danger">
                                                        <i class="fa-solid fa-right-from-bracket me-1"></i> Exit End
                                                    </label>
                                                    <input type="time" name="out_end" class="form-control form-control-sm"
                                                        value="{{ $sched->out_end ? \Carbon\Carbon::parse($sched->out_end)->format('H:i') : '' }}" required>
                                                </div>
                                            </div>

                                            <div class="text-danger small mb-2" data-edit-error="sched-{{ $sched->id }}"></div>

                                            <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                                <button type="button" class="btn btn-sm btn-outline-secondary px-3"
                                                    data-edit-cancel="sched-{{ $sched->id }}">Cancel</button>
                                                <button type="submit" class="btn btn-sm btn-primary px-3">
                                                    <i class="fa-solid fa-check me-1"></i> Save
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                @else
                    {{-- ================================================================= --}}
                    {{-- CASE C: ZERO SESSIONS LEVEL                                       --}}
                    {{-- ================================================================= --}}
                    <div class="sched-level-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="sched-icon-box" style="background-color: {{ $levelMeta['bg'] }}; color: {{ $levelMeta['color'] }};">
                                <i class="{{ $levelMeta['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="mb-0 fw-bold text-dark">{{ $levelMeta['name'] }}</h5>
                                    <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill">0 Sessions</span>
                                </div>
                                <span class="text-muted small">{{ $dynamicSubtitle }}</span>
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal" data-bs-target="#addScheduleModal" data-level="{{ $levelKey }}">
                            <i class="fa-solid fa-plus me-1"></i> Add Session
                        </button>
                    </div>

                    <div class="card-body p-4">
                        <div class="text-center py-4 px-3 border border-dashed rounded-3 bg-light bg-opacity-50">
                            <i class="fa-regular fa-clock fa-2x text-muted mb-2 opacity-50"></i>
                            <p class="mb-1 fw-semibold text-dark">No schedules configured for {{ $levelMeta['name'] }}</p>
                            <p class="text-muted small mb-3">Set entry/exit scan windows and late cutoff for this level.</p>
                            <button type="button" class="btn btn-sm btn-primary"
                                data-bs-toggle="modal" data-bs-target="#addScheduleModal" data-level="{{ $levelKey }}">
                                <i class="fa-solid fa-plus me-1"></i> Configure Schedule
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach

    </div>

    {{-- Add Schedule Modal --}}
    <x-modal>
        <x-slot name="id">addScheduleModal</x-slot>
        <x-slot name="modalTitle">Add Schedule Configuration</x-slot>

        <form id="addScheduleForm" action="{{ route('schedconfig.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Education Level</label>
                    <select class="form-select" name="level" id="add_schedule_level" required>
                        <option value="" disabled selected>Select education level</option>
                        <option value="elementary">Elementary Level</option>
                        <option value="hs">Junior High School</option>
                        <option value="shs">Senior High School</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Session Type</label>
                    <select class="form-select" name="session_type" id="add_schedule_session_type" required>
                        <option value="" disabled selected>Select session type</option>
                        <option value="morning">Morning Session</option>
                        <option value="afternoon">Afternoon Session</option>
                        <option value="whole_day">Whole Day Session</option>
                    </select>
                </div>
            </div>

            <div class="border rounded-3 p-3 mb-3">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fa-solid fa-right-to-bracket text-success"></i>
                    <h6 class="fw-bold mb-0">Entry Scan Window</h6>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Entry Start Time</label>
                        <input type="time" class="form-control" name="in_start" required>
                        <div class="form-text">Earliest allowed time for entry scanning.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Entry End Time</label>
                        <input type="time" class="form-control" name="in_end" required>
                        <div class="form-text">Latest allowed time for entry scanning.</div>
                    </div>
                </div>

                <div>
                    <label class="form-label">Late Threshold</label>
                    <input type="time" class="form-control" name="late_threshold" required>
                    <div class="form-text">Students scanning after this time will be marked late.</div>
                </div>
            </div>

            <div class="border rounded-3 p-3 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fa-solid fa-right-from-bracket text-danger"></i>
                    <h6 class="fw-bold mb-0">Exit Scan Window</h6>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Exit Start Time</label>
                        <input type="time" class="form-control" name="out_start" required>
                        <div class="form-text">Earliest allowed time for exit scanning.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Exit End Time</label>
                        <input type="time" class="form-control" name="out_end" required>
                        <div class="form-text">Latest allowed time for exit scanning.</div>
                    </div>
                </div>
            </div>

            <div class="text-danger small mb-3" id="addScheduleError"></div>

            <div class="modal-footer px-0 pb-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark" id="addScheduleSubmitBtn">Save Schedule Configuration</button>
            </div>
        </form>
    </x-modal>

    {{-- Delete Schedule Modal --}}
    <x-modal size="modal-md">
        <x-slot name="id">deleteScheduleModal</x-slot>
        <x-slot name="modalTitle">Delete Schedule Configuration</x-slot>

        <form id="deleteScheduleForm" method="POST" action="">
            @csrf
            @method('DELETE')

            <div class="text-danger small mb-2" id="deleteScheduleError"></div>

            <p class="mb-2">This will permanently delete the selected schedule configuration.</p>
            <p class="mb-0 text-muted small">
                <strong>Level:</strong> <span id="delete_schedule_level">-</span><br>
                <strong>Session:</strong> <span id="delete_schedule_session_type">-</span>
            </p>

            <div class="modal-footer px-0 pb-0 mt-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger" id="deleteScheduleSubmitBtn">Delete</button>
            </div>
        </form>
    </x-modal>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            function formatTime(timeStr) {
                if (!timeStr) return '—';
                const parts = timeStr.split(':');
                if (parts.length < 2) return timeStr;
                let h = parseInt(parts[0], 10);
                const m = parts[1];
                const ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12 || 12;
                return `${h}:${m} ${ampm}`;
            }

            function getSessionBadge(sessionType) {
                if (sessionType === 'morning') {
                    return '<span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle px-2.5 py-1.5 rounded-pill"><i class="fa-solid fa-sun me-1"></i> Morning Session</span>';
                } else if (sessionType === 'afternoon') {
                    return '<span class="badge bg-info bg-opacity-10 text-info-emphasis border border-info-subtle px-2.5 py-1.5 rounded-pill"><i class="fa-solid fa-cloud-sun me-1"></i> Afternoon Session</span>';
                } else {
                    return '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2.5 py-1.5 rounded-pill"><i class="fa-solid fa-calendar-day me-1"></i> Whole Day Session</span>';
                }
            }

            function toggleEdit(container, showForm) {
                if (!container) return;
                const view = container.querySelector('[data-edit-view]');
                const form = container.querySelector('[data-edit-form]');
                if (!view || !form) return;
                view.classList.toggle('d-none', showForm);
                form.classList.toggle('d-none', !showForm);
                const err = container.querySelector('[data-edit-error]');
                if (err) err.textContent = '';
            }

            // Toggle Edit Mode
            document.querySelectorAll('[data-edit-toggle]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = btn.dataset.editToggle;
                    const container = document.querySelector(`[data-edit-container="${id}"]`);
                    if (container) toggleEdit(container, true);
                });
            });

            // Cancel Edit Mode
            document.querySelectorAll('[data-edit-cancel]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = btn.dataset.editCancel;
                    const container = document.querySelector(`[data-edit-container="${id}"]`);
                    if (container) toggleEdit(container, false);
                });
            });

            // Inline Edit Form AJAX Submission
            document.querySelectorAll('[data-edit-form]').forEach(function (form) {
                form.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const container = form.closest('[data-edit-container]');
                    const errEl = container ? container.querySelector('[data-edit-error]') : null;
                    if (errEl) errEl.textContent = '';
                    const submitBtn = form.querySelector('[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: new FormData(form),
                        });
                        const data = await res.json().catch(() => ({}));

                        if (!res.ok) {
                            const firstErr = data.errors ? Object.values(data.errors)[0] : null;
                            const msg = Array.isArray(firstErr) && firstErr.length
                                ? firstErr[0]
                                : (data.message || 'Could not save changes.');
                            if (errEl) errEl.textContent = msg;
                            return;
                        }

                        const sched = data.data || {};
                        const inWindowEl = container.querySelector('[data-field-display="in_window"]');
                        const outWindowEl = container.querySelector('[data-field-display="out_window"]');
                        const lateThreshEl = container.querySelector('[data-field-display="late_threshold"]');
                        const badgeEl = container.querySelector('[data-field-badge="session_type"]');

                        if (inWindowEl) inWindowEl.textContent = `${formatTime(sched.in_start)} – ${formatTime(sched.in_end)}`;
                        if (outWindowEl) outWindowEl.textContent = `${formatTime(sched.out_start)} – ${formatTime(sched.out_end)}`;
                        if (lateThreshEl) lateThreshEl.textContent = formatTime(sched.late_threshold);
                        if (badgeEl && sched.session_type) badgeEl.innerHTML = getSessionBadge(sched.session_type);

                        toggleEdit(container, false);
                    } catch (err) {
                        if (errEl) errEl.textContent = 'Network error. Please try again.';
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                });
            });

            // Add Schedule Modal handler
            const addModalEl = document.getElementById('addScheduleModal');
            const addForm = document.getElementById('addScheduleForm');
            const addErrEl = document.getElementById('addScheduleError');
            const addSubmitBtn = document.getElementById('addScheduleSubmitBtn');

            function resetAddModal() {
                if (addErrEl) addErrEl.textContent = '';
                if (addSubmitBtn) {
                    addSubmitBtn.disabled = false;
                    addSubmitBtn.innerHTML = 'Save Schedule Configuration';
                }
            }

            if (addModalEl) {
                addModalEl.addEventListener('show.bs.modal', function (event) {
                    resetAddModal();
                    if (addForm) addForm.reset();

                    const btn = event.relatedTarget ? event.relatedTarget.closest('button, a') : null;
                    const level = btn?.dataset?.level;
                    const levelSelect = addModalEl.querySelector('#add_schedule_level');
                    const sessionSelect = addModalEl.querySelector('#add_schedule_session_type');
                    if (level && levelSelect) {
                        levelSelect.value = level;
                        if (level === 'elementary' && sessionSelect) {
                            sessionSelect.value = 'whole_day';
                        }
                    }
                });

                addModalEl.addEventListener('hidden.bs.modal', function () {
                    resetAddModal();
                    if (addForm) addForm.reset();
                });

                if (addForm) {
                    addForm.addEventListener('submit', async function (e) {
                        e.preventDefault();
                        if (addErrEl) addErrEl.textContent = '';
                        addSubmitBtn.disabled = true;
                        addSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                        try {
                            const res = await fetch(addForm.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                },
                                body: new FormData(addForm),
                            });
                            const data = await res.json().catch(() => ({}));

                            if (!res.ok) {
                                const firstErr = data.errors ? Object.values(data.errors)[0] : null;
                                const msg = Array.isArray(firstErr) && firstErr.length
                                    ? firstErr[0]
                                    : (data.message || 'Could not create schedule.');
                                if (addErrEl) addErrEl.textContent = msg;
                                return;
                            }

                            window.location.reload();
                        } catch (err) {
                            if (addErrEl) addErrEl.textContent = 'Network error. Please try again.';
                        } finally {
                            addSubmitBtn.disabled = false;
                            addSubmitBtn.innerHTML = 'Save Schedule Configuration';
                        }
                    });
                }
            }

            // Delete Schedule Modal handler
            const deleteModalEl = document.getElementById('deleteScheduleModal');
            const deleteForm = document.getElementById('deleteScheduleForm');
            const deleteErrEl = document.getElementById('deleteScheduleError');
            const deleteSubmitBtn = document.getElementById('deleteScheduleSubmitBtn');

            function resetDeleteModal() {
                if (deleteErrEl) deleteErrEl.textContent = '';
                if (deleteSubmitBtn) {
                    deleteSubmitBtn.disabled = false;
                    deleteSubmitBtn.innerHTML = 'Delete';
                }
            }

            if (deleteModalEl) {
                deleteModalEl.addEventListener('show.bs.modal', function (event) {
                    resetDeleteModal();

                    const btn = event.relatedTarget ? event.relatedTarget.closest('button, a') : null;
                    if (!btn) return;
                    const id = btn.dataset.id;
                    const levelLabel = btn.dataset.levelLabel;
                    const sessionTypeLabel = btn.dataset.sessionTypeLabel;

                    document.getElementById('delete_schedule_level').textContent = levelLabel || '-';
                    document.getElementById('delete_schedule_session_type').textContent = sessionTypeLabel || '-';

                    if (deleteForm) {
                        deleteForm.dataset.targetId = id;
                        deleteForm.action = `/schedule-configuration/${id}`;
                    }
                });

                deleteModalEl.addEventListener('hidden.bs.modal', function () {
                    resetDeleteModal();
                });

                if (deleteForm) {
                    deleteForm.addEventListener('submit', async function (e) {
                        e.preventDefault();
                        if (deleteErrEl) deleteErrEl.textContent = '';
                        deleteSubmitBtn.disabled = true;
                        deleteSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

                        try {
                            const targetId = deleteForm.dataset.targetId;
                            const deleteUrl = targetId ? `/schedule-configuration/${targetId}` : deleteForm.action;

                            const res = await fetch(deleteUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                    'X-HTTP-Method-Override': 'DELETE',
                                },
                                body: new FormData(deleteForm),
                            });
                            const data = await res.json().catch(() => ({}));

                            if (!res.ok) {
                                const msg = data.message || 'Could not delete schedule.';
                                if (deleteErrEl) deleteErrEl.textContent = msg;
                                return;
                            }

                            window.location.reload();
                        } catch (err) {
                            if (deleteErrEl) deleteErrEl.textContent = 'Network error. Please try again.';
                        } finally {
                            deleteSubmitBtn.disabled = false;
                            deleteSubmitBtn.innerHTML = 'Delete';
                        }
                    });
                }
            }
        });
    </script>

</x-layouts.school-admin>