<x-layouts.admin>
    <x-slot name="pageName">
        Schedule Configuration
    </x-slot>

    <x-slot name="pageTitle">

    </x-slot>

    <div class="main-content mx-3">

        {{-- Top Controls --}}
        <div class="d-flex justify-content-end align-items-center mx-3 gap-2 mb-3">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#scheduleHelpModal">
                <i class="fa-solid fa-circle-question me-1"></i> Help
            </button>
            <button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                Add Schedule
            </button>
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
            <!-- Pagination -->
            <div class="px-3 py-3">
                {{ $scheduleConfigs->links() }}
            </div>
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

    {{-- Help Modal --}}
    <x-modal size="modal-lg">
        <x-slot name="id">scheduleHelpModal</x-slot>
        <x-slot name="modalTitle">Schedule Configuration - Help & FAQ</x-slot>

        <div class="modal-body px-0 pb-3">
            {{-- Tabs --}}
            <ul class="nav nav-tabs nav-fill mb-0" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="help-tab" data-bs-toggle="tab"
                        data-bs-target="#help-tab-pane" type="button" role="tab" aria-controls="help-tab-pane"
                        aria-selected="true">
                        <i class="fa-solid fa-book-open me-1"></i> Help
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="faq-tab" data-bs-toggle="tab" data-bs-target="#faq-tab-pane"
                        type="button" role="tab" aria-controls="faq-tab-pane" aria-selected="false">
                        <i class="fa-solid fa-circle-question me-1"></i> FAQ
                    </button>
                </li>
            </ul>

            <div class="tab-content pt-3">
                {{-- Help Tab --}}
                <div class="tab-pane fade show active" id="help-tab-pane" role="tabpanel"
                    aria-labelledby="help-tab" tabindex="0">
                    <div class="px-2 small text-secondary">
                        <p class="fw-semibold text-dark mb-2">How schedules are organized</p>
                        <p class="mb-1">A schedule configuration defines when students may <strong>enter</strong> and
                            <strong> exit</strong> campus. Each configuration is tied to an
                            <strong> Education Level</strong> and a <strong> Session Type</strong>.
                        </p>

                        <div class="row g-2 mt-1">
                            <div class="col-sm-6">
                                <div class="border rounded-2 p-2 h-100">
                                    <div class="fw-semibold text-dark mb-1">1. Department</div>
                                    <div class="text-muted">Elementary, High School (HS), or Senior High School
                                        (SHS).
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded-2 p-2 h-100">
                                    <div class="fw-semibold text-dark mb-1">2. Session</div>
                                    <div class="text-muted">Morning, Afternoon, or Whole-day session.</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded-2 p-2 h-100">
                                    <div class="fw-semibold text-dark mb-1">3. IN Window</div>
                                    <div class="text-muted">The allowed time range for entry scanning.</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded-2 p-2 h-100">
                                    <div class="fw-semibold text-dark mb-1">4. Late Threshold</div>
                                    <div class="text-muted">Scans after this time are marked as Late.</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded-2 p-2 h-100">
                                    <div class="fw-semibold text-dark mb-1">5. OUT Window</div>
                                    <div class="text-muted">The allowed time range for exit scanning.</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded-2 p-2 h-100">
                                    <div class="fw-semibold text-dark mb-1">6. Actions</div>
                                    <div class="text-muted">Use the ellipsis to Edit or Delete a schedule.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FAQ Tab --}}
                <div class="tab-pane fade" id="faq-tab-pane" role="tabpanel" aria-labelledby="faq-tab" tabindex="0">
                    <div class="px-2 small text-muted">
                        <div class="accordion accordion-flush" id="scheduleFaqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq-heading-1">
                                    <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq-collapse-1"
                                        aria-expanded="false" aria-controls="faq-collapse-1">
                                        What is a schedule configuration?
                                    </button>
                                </h2>
                                <div id="faq-collapse-1" class="accordion-collapse collapse"
                                    aria-labelledby="faq-heading-1" data-bs-parent="#scheduleFaqAccordion">
                                    <div class="accordion-body">
                                        It defines the allowed entry (IN) and exit (OUT) scanning
                                        windows for a specific Education Level and Session Type, plus
                                        the Late Threshold used to flag late arrivals.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq-heading-2">
                                    <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq-collapse-2"
                                        aria-expanded="false" aria-controls="faq-collapse-2">
                                        What should I do if a student scans outside the IN Window?
                                    </button>
                                </h2>
                                <div id="faq-collapse-2" class="accordion-collapse collapse"
                                    aria-labelledby="faq-heading-2" data-bs-parent="#scheduleFaqAccordion">
                                    <div class="accordion-body">
                                        A scan before the Entry Start Time or after the Entry End Time
                                        is considered outside the allowed window. Adjust the schedule
                                        if the window needs to be widened.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq-heading-3">
                                    <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq-collapse-3"
                                        aria-expanded="false" aria-controls="faq-collapse-3">
                                        How does the Late Threshold work?
                                    </button>
                                </h2>
                                <div id="faq-collapse-3" class="accordion-collapse collapse"
                                    aria-labelledby="faq-heading-3" data-bs-parent="#scheduleFaqAccordion">
                                    <div class="accordion-body">
                                        Any entry scan occurring after the Late Threshold time is
                                        marked as <em>Late</em>. It should sit between Entry Start and
                                        Entry End.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq-heading-4">
                                    <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq-collapse-4"
                                        aria-expanded="false" aria-controls="faq-collapse-4">
                                        Can I have different sessions for the same level?
                                    </button>
                                </h2>
                                <div id="faq-collapse-4" class="accordion-collapse collapse"
                                    aria-labelledby="faq-heading-4" data-bs-parent="#scheduleFaqAccordion">
                                    <div class="accordion-body">
                                        Yes. For example, Elementary can have both a Morning and an
                                        Afternoon session. Add one schedule per level-session pair.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="faq-heading-5">
                                    <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#faq-collapse-5"
                                        aria-expanded="false" aria-controls="faq-collapse-5">
                                        Why can't I delete a schedule?
                                    </button>
                                </h2>
                                <div id="faq-collapse-5" class="accordion-collapse collapse"
                                    aria-labelledby="faq-heading-5" data-bs-parent="#scheduleFaqAccordion">
                                    <div class="accordion-body">
                                        Deleting a schedule is permanent. If there are active
                                        attendance records tied to it, coordinate with your administrator
                                        before deleting.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                Close
            </button>
        </div>
    </x-modal>

</x-layouts.admin>