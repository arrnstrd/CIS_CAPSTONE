<x-layouts.admin>

    <x-slot name="pageName">
        QR Code Generation
    </x-slot>

    <x-slot name="subtitle">
        Generate printable QR cards for each section and scan QR codes
    </x-slot>

    {{-- QR Scanner Interface --}}
    <div class="qr-generation-container row g-4 mb-5">
        <div class="col-lg-5">
            <div id="qrStationApp" class="qr-terminal state-idle" data-scan-url="{{ route('qr-station.scan') }}" data-csrf="{{ csrf_token() }}">

                {{-- Hidden anchor input for HID keyboard-style QR scanners --}}
                <input type="text" id="scannerInput" class="qr-scanner-input" tabindex="-1" autocomplete="off"
                    autocapitalize="off" autocorrect="off" spellcheck="false" aria-hidden="true">

                {{-- Scanner Header --}}
                <div class="qr-terminal__header">
                    <div class="qr-terminal__status">
                        <span class="qr-terminal__dot"></span>
                        <span class="qr-terminal__status-text" id="fbHeaderTitle">Scanner Active</span>
                    </div>
                    <span class="qr-terminal__header-sub" id="fbHeaderSub">Awaiting input…</span>
                </div>

                {{-- Scanner Content --}}
                <div class="qr-terminal__body">
                    <div id="qrContent" class="qr-content">
                        <div class="text-center ready-bg w-full h-full flex flex-col items-center justify-center rounded-2xl px-6">
                            <div class="relative mb-8">
                                <div class="w-24 h-24 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fa-solid fa-qrcode text-4xl text-indigo-600"></i>
                                </div>
                                <div class="absolute -top-1 -right-1 w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center">
                                    <div class="w-2 h-2 bg-white rounded-full pulse-dot"></div>
                                </div>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-800 mb-2">Ready to Scan</h3>
                            <p class="text-sm text-slate-500">Point your QR scanner at the code or use manual input below</p>
                        </div>
                    </div>

                    {{-- Pop-up result card --}}
                    <div id="qrPopup" class="qr-popup" style="display: none;">
                        <div class="qr-popup__card" id="qrPopupCard"></div>
                    </div>
                </div>

                {{-- Processing progress --}}
                <div class="qr-terminal__progress">
                    <div class="qr-progress">
                        <div class="qr-progress__fill" id="fbFill"></div>
                    </div>
                    <div class="qr-progress__label" id="fbLabel"></div>
                </div>

                {{-- Footer --}}
                <div class="qr-terminal__footer ms-3">
                    <div class="qr-terminal__device">
                        <i class="fas fa-keyboard me-1"></i>
                        <span>USB HID Scanner</span>
                        <span class="qr-terminal__sep"></span>
                        <span class="qr-terminal__buffer">buffer: <span id="fbBuffer">—</span></span>
                    </div>
                    <span class="qr-terminal__auto" id="fbAutoReady">Auto-ready</span>
                </div>

                {{-- Countdown bar --}}
                <div id="countdownBar" class="qr-terminal__countdown" style="display: none;"></div>
            </div>

            {{-- Manual QR input --}}
            <div class="qr-manual mt-3">
                <div class="qr-manual__label"><i class="fas fa-keyboard me-1"></i> Manual QR String</div>
                <div class="input-group flex-grow-1" style="min-width: 220px;">
                    <input type="text" id="manualQrInput" class="form-control qr-manual__input"
                        placeholder="Paste QR string here" autocomplete="off" autocapitalize="off">
                    <button class="btn btn-dark" type="button" id="manualQrSend">
                        <i class="fas fa-paper-plane me-1"></i> Send
                    </button>
                </div>
            </div>
        </div>

        {{-- Section Management --}}
        <div class="col-lg-7">
            <div class="bg-white rounded p-4 border mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-semibold text-dark m-0 fs-5">Section List</h3>
                </div>

                <form method="GET">
                    <div class="row g-3 align-items-center">

                        {{-- Search --}}
                        <div class="col-lg-8">
                            <div class="input-group">
                                <input type="search" name="search" class="form-control"
                                    placeholder="Search by section name or grade level..."
                                    value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>

                        {{-- Grade Level --}}
                        <div class="col-lg-4">
                            <select name="grade_level" class="form-select" onchange="this.form.submit()">
                                <option value="">All Grade Levels</option>
                                @for ($grade = 1; $grade <= 12; $grade++)
                                    <option value="{{ $grade }}" {{ request('grade_level') == $grade ? 'selected' : '' }}>
                                        Grade {{ $grade }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                    </div>
                </form>
            </div>

            <x-ui.table>
                <thead class="table-light">
                    <tr>
                        <th>Grade</th>
                        <th>Section</th>
                        <th>Adviser</th>
                        <th>Students</th>
                        <th>Status</th>
                        <th width="180">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sections as $section)
                        <tr>
                            <td>Grade  {{ $section->grade_level }}</td>
                            <td>{{ $section->name }}</td>
                            <td>{{ $section->advisor?->full_name ?? '-' }}</td>
                            <td>{{ $section->students_count }}</td>
                            <td>
                                @if($section->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                               @if($section->students_count > 0)
                                    <a href="{{ route('sections.qr.download', $section) }}"
                                    class="btn btn-success btn-sm">
                                        <i class="fa-solid fa-download me-1"></i>
                                        Download QR
                                    </a>
                                @else
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        No Students
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">No sections available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>

            <div class="mx-3 mt-3 mb-3">
                {{ $sections->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="{{ Vite::asset('resources/js/qr-generation.js') }}"></script>
    @endpush

</x-layouts.admin>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-semibold text-dark m-0 fs-5">Section List</h3>

                  
                </div>

                <form method="GET">
                    <div class="row g-3 align-items-center">

                        {{-- Search --}}
                        <div class="col-lg-8">
                            <div class="input-group">
                                <input type="search" name="search" class="form-control"
                                    placeholder="Search by section name or grade level..."
                                    value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>

                        {{-- Grade Level --}}
                        <div class="col-lg-4">
                            <select name="grade_level" class="form-select" onchange="this.form.submit()">
                                <option value="">All Grade Levels</option>
                                @for ($grade = 1; $grade <= 12; $grade++)
                                    <option value="{{ $grade }}" {{ request('grade_level') == $grade ? 'selected' : '' }}>
                                        Grade {{ $grade }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                    </div>
                </form>
            </div>
        </div>



        <x-ui.table>
            <thead class="table-light">
                <tr>
                    <th>Grade</th>
                    <th>Section</th>
                    <th>Adviser</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th width="180">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sections as $section)
                    <tr>
                        <td>Grade  {{ $section->grade_level }}</td>
                        <td>{{ $section->name }}</td>
                        <td>{{ $section->advisor?->full_name ?? '-' }}</td>
                        <td>{{ $section->students_count }}</td>
                        <td>
                            @if($section->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                           @if($section->students_count > 0)
                                <a href="{{ route('sections.qr.download', $section) }}"
                                class="btn btn-success btn-sm">
                                    <i class="fa-solid fa-download me-1"></i>
                                    Download QR
                                </a>
                            @else
                                <button class="btn btn-secondary btn-sm" disabled>
                                    No Students
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">No sections available.</td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>


            <div class=" mx-3 mt-3 mb-3">
        {{ $sections->links() }}
    </div>
        
        


</div>
        

</x-layouts.admin>