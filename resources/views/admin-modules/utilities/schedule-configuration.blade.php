<x-layouts.admin>
    <x-slot name="pageName">
        Schedule Configuration
    </x-slot>

    <x-slot name="pageTitle">

    </x-slot>

    <div class="main-content mx-3">

        {{-- Top Controls --}}
        <div class="d-flex justify-content-end align-items-center mx-3 gap-2 mb-3">
            <button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                Add Schedule
            </button>
        </div>

        {{-- Quick Guide --}}
        <div class="border-start border-primary border-4 bg-white rounded-3 p-3 mb-4 mx-3 shadow-sm">
            <div class="text-primary fw-bold mb-3">How to Use</div>

            <div class="row g-3 small">
                <div class="col-md d-flex align-items-start gap-2">
                    <span
                        class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold"
                        style="width: 24px; height: 24px; min-width: 24px; font-size: 12px;">1</span>
                    <div>
                        <div class="fw-semibold text-dark">Department</div>
                        <div class="text-muted">Elementary, HS, SHS</div>
                    </div>
                </div>

                <div class="col-md d-flex align-items-start gap-2">
                    <span
                        class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold"
                        style="width: 24px; height: 24px; min-width: 24px; font-size: 12px;">2</span>
                    <div>
                        <div class="fw-semibold text-dark">Session</div>
                        <div class="text-muted">Morning or Afternoon</div>
                    </div>
                </div>

                <div class="col-md d-flex align-items-start gap-2">
                    <span
                        class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold"
                        style="width: 24px; height: 24px; min-width: 24px; font-size: 12px;">3</span>
                    <div>
                        <div class="fw-semibold text-dark">IN Window</div>
                        <div class="text-muted">Allowed entry scan time</div>
                    </div>
                </div>

                <div class="col-md d-flex align-items-start gap-2">
                    <span
                        class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold"
                        style="width: 24px; height: 24px; min-width: 24px; font-size: 12px;">4</span>
                    <div>
                        <div class="fw-semibold text-dark">Late Threshold</div>
                        <div class="text-muted">Basis for late detection</div>
                    </div>
                </div>

                <div class="col-md d-flex align-items-start gap-2">
                    <span
                        class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold"
                        style="width: 24px; height: 24px; min-width: 24px; font-size: 12px;">5</span>
                    <div>
                        <div class="fw-semibold text-dark">OUT Window</div>
                        <div class="text-muted">Allowed exit scan time</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Schedule Table --}}
        <div class="col">
            <x-ui.table>
                <thead class="text-uppercase text-center">
                    <tr>
                        <th>Level</th>
                        <th>Session Type</th>
                        <th>IN Start</th>
                        <th>IN End</th>
                        <th>Late Threshold</th>
                        <th>OUT Start</th>
                        <th>OUT End</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody class="text-center">
                    @forelse ($scheduleConfigs as $scheduleConfig)
                        <tr>
                            <td>{{ $scheduleConfig->level }}</td>
                            <td>{{ $scheduleConfig->session_type }}</td>
                            <td>{{ $scheduleConfig->in_start ? \Carbon\Carbon::parse($scheduleConfig->in_start)->format('g:i A') : '—' }}
                            </td>
                            <td>{{ $scheduleConfig->in_end ? \Carbon\Carbon::parse($scheduleConfig->in_end)->format('g:i A') : '—' }}
                            </td>
                            <td>{{ $scheduleConfig->late_threshold ? \Carbon\Carbon::parse($scheduleConfig->late_threshold)->format('g:i A') : '—' }}
                            </td>
                            <td>{{ $scheduleConfig->out_start ? \Carbon\Carbon::parse($scheduleConfig->out_start)->format('g:i A') : '—' }}
                            </td>
                            <td>{{ $scheduleConfig->out_end ? \Carbon\Carbon::parse($scheduleConfig->out_end)->format('g:i A') : '—' }}
                            </td>
                            <td>
                                <div class="dropdown position-static">
                                    <button class="btn btn-sm btn-outline-secondary" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button type="button" class="dropdown-item js-edit-schedule"
                                                data-bs-toggle="modal" data-bs-target="#editScheduleModal"
                                                data-id="{{ $scheduleConfig->id }}"
                                                data-level="{{ $scheduleConfig->level }}"
                                                data-session-type="{{ $scheduleConfig->session_type }}"
                                                data-in-start="{{ $scheduleConfig->in_start ? \Carbon\Carbon::parse($scheduleConfig->in_start)->format('H:i') : '' }}"
                                                data-in-end="{{ $scheduleConfig->in_end ? \Carbon\Carbon::parse($scheduleConfig->in_end)->format('H:i') : '' }}"
                                                data-late-threshold="{{ $scheduleConfig->late_threshold ? \Carbon\Carbon::parse($scheduleConfig->late_threshold)->format('H:i') : '' }}"
                                                data-out-start="{{ $scheduleConfig->out_start ? \Carbon\Carbon::parse($scheduleConfig->out_start)->format('H:i') : '' }}"
                                                data-out-end="{{ $scheduleConfig->out_end ? \Carbon\Carbon::parse($scheduleConfig->out_end)->format('H:i') : '' }}">
                                                Edit
                                            </button>
                                        </li>

                                        <li>
                                            <button type="button" class="dropdown-item text-danger js-delete-schedule"
                                                data-bs-toggle="modal" data-bs-target="#deleteScheduleModal"
                                                data-id="{{ $scheduleConfig->id }}"
                                                data-level="{{ $scheduleConfig->level }}"
                                                data-session-type="{{ $scheduleConfig->session_type }}">
                                                Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No records yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

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
                    <select class="form-select" name="level" required>
                        <option value="" disabled selected>Select education level</option>
                        <option value="elementary">Elementary</option>
                        <option value="hs">High School</option>
                        <option value="shs">Senior High School</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Session Type</label>
                    <select class="form-select" name="session_type" required>
                        <option value="" disabled selected>Select session type</option>
                        <option value="morning">Morning Session</option>
                        <option value="afternoon">Afternoon Session</option>
                        <option value="whole_day">Wholeday Session</option>
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

            <button type="submit" class="btn btn-dark w-100">Save Schedule Configuration</button>
        </form>
    </x-modal>

    {{-- Edit Schedule Modal --}}
    <x-modal>
        <x-slot name="id">editScheduleModal</x-slot>
        <x-slot name="modalTitle">Edit Schedule Configuration</x-slot>

        <form id="editScheduleForm" method="POST" data-update-url="{{ route('schedconfig.update', '__ID__') }}">
            @csrf
            @method('PUT')

            <div data-ajax-errors></div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Education Level</label>
                    <select class="form-select" name="level" id="edit_schedule_level" required>
                        <option value="" disabled>Select education level</option>
                        <option value="elementary">Elementary</option>
                        <option value="hs">High School</option>
                        <option value="shs">Senior High School</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Session Type</label>
                    <select class="form-select" name="session_type" id="edit_schedule_session_type" required>
                        <option value="" disabled>Select session type</option>
                        <option value="morning">Morning Session</option>
                        <option value="afternoon">Afternoon Session</option>
                        <option value="whole_day">Wholeday Session</option>
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
                        <input type="time" class="form-control" name="in_start" id="edit_schedule_in_start" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Entry End Time</label>
                        <input type="time" class="form-control" name="in_end" id="edit_schedule_in_end" required>
                    </div>
                </div>

                <div>
                    <label class="form-label">Late Threshold</label>
                    <input type="time" class="form-control" name="late_threshold" id="edit_schedule_late_threshold"
                        required>
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
                        <input type="time" class="form-control" name="out_start" id="edit_schedule_out_start" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Exit End Time</label>
                        <input type="time" class="form-control" name="out_end" id="edit_schedule_out_end" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer px-0 pb-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark" data-loading-text="Saving...">Save Changes</button>
            </div>
        </form>
    </x-modal>

    {{-- Delete Schedule Modal --}}
    <x-modal size="modal-md">
        <x-slot name="id">deleteScheduleModal</x-slot>
        <x-slot name="modalTitle">Delete Schedule Configuration</x-slot>

        <form id="deleteScheduleForm" method="POST" data-delete-url="{{ route('schedconfig.destroy', '__ID__') }}">
            @csrf
            @method('DELETE')

            <div data-ajax-errors></div>

            <p class="mb-2">This will permanently delete the selected schedule configuration.</p>
            <p class="mb-0 text-muted small">
                <strong>Level:</strong> <span id="delete_schedule_level">-</span><br>
                <strong>Session:</strong> <span id="delete_schedule_session_type">-</span>
            </p>

            <div class="modal-footer px-0 pb-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger" data-loading-text="Deleting...">Delete</button>
            </div>
        </form>
    </x-modal>

</x-layouts.admin>