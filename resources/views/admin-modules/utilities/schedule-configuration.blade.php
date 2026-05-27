<x-layouts.admin>
    <x-slot name="pageName">
        Schedule Configuration
    </x-slot>

    <x-slot name="pageTitle">

    </x-slot>

    <div class="main-content mx-3">

        {{-- Top Controls --}}
        <div class="d-flex justify-content-end align-items-center mx-3 gap-2 mb-3">

            {{-- Help Button --}}
            <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                data-bs-toggle="modal" data-bs-target="#scheduleGuideModal">

                <i class="fa-solid fa-circle-question"></i>
                <span>Help</span>
            </button>

            {{-- Add Button --}}
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

        <div class="col">
            <x-ui.table>
                <thead class="text-uppercase text-center">
                    <tr>
                        <th>Level</th>
                        <th>Session Type</th>
                        <th>IN Start </th>
                        <th>IN End</th>
                        <th>Late Threshold</th>
                        <th>OUT Start</th>
                        <th>OUT End</th>
                        <th>actions</th>
                    </tr>
                </thead>

                <tbody class="text-center">
                    @forelse($scheduleConfigs as $scheduleConfig)
                        <tr>
                            <td>{{ $scheduleConfig->level }} </td>
                            <td> {{ $scheduleConfig->session_type }}</td>
                            <td>{{ $scheduleConfig->in_start }} </td>
                            <td>{{ $scheduleConfig->in_end }} </td>
                            <td>{{ $scheduleConfig->late_threshold }}</td>
                            <td> {{ $scheduleConfig->out_start }}</td>
                            <td>{{ $scheduleConfig->out_end }} </td>
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

        </div>

    </div>



    {{-- modal --}}
    <x-modal>
        <x-slot name="id">addScheduleModal</x-slot>
        <x-slot name="modalTitle">Add Schedule Configuration</x-slot>

        <form action="{{ route('schedconfig.store') }}" method="POST">
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

    {{-- Manual Guide Modal --}}
    <div class="modal fade" id="scheduleGuideModal" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                {{-- Header --}}
                <div class="modal-header border-0 pb-0">

                    <div>
                        <h5 class="modal-title fw-bold mb-1">
                            Schedule Configuration Guide for Gate Scan
                        </h5>

                        <p class="text-muted small mb-0">
                            Learn how schedule configuration works in the entry and exit monitoring system.
                        </p>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>

                </div>

                {{-- Body --}}
                <div class="modal-body pt-3">

                    {{-- Overview --}}
                    <div class="mb-4">

                        <h6 class="fw-bold mb-2">
                            What is Schedule Configuration?
                        </h6>

                        <p class="text-muted small mb-0">
                            Schedule Configuration controls the allowed attendance
                            scanning time of students based on their assigned
                            level and session type. The scanner uses these schedules
                            to validate entry scans, exit scans, and late arrivals.
                        </p>

                    </div>

                    {{-- How It Works --}}
                    <div class="mb-4">

                        <h6 class="fw-bold mb-2">
                            How the Scanner Uses the Schedule
                        </h6>

                        <div class="bg-light rounded p-3 small text-muted">

                            <p class="mb-2">
                                The system checks:
                            </p>

                            <ul class="mb-3">
                                <li>Student level</li>
                                <li>Student session type</li>
                                <li>Current time</li>
                            </ul>

                            <p class="mb-0">
                                Once matched, the scanner follows the configured
                                time windows for entry and exit validation.
                            </p>

                        </div>

                    </div>

                    {{-- Field Guide --}}
                    <div class="mb-4">

                        <h6 class="fw-bold mb-3">
                            Schedule Field Descriptions
                        </h6>

                        <div class="table-responsive">

                            <table class="table table-bordered align-middle small">

                                <thead class="table-light">
                                    <tr>
                                        <th width="25%">Field</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <tr>
                                        <td class="fw-semibold">Department</td>
                                        <td>
                                            Defines whether the schedule belongs to
                                            Elementary, HS, or SHS students.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="fw-semibold">Session Type</td>
                                        <td>
                                            Determines if the schedule is for
                                            Morning or Afternoon classes.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">IN Start</td>
                                        <td>
                                            Earliest allowed time for entry scanning.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="fw-semibold">IN End</td>
                                        <td>
                                            Latest allowed time for entry scanning.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="fw-semibold">Late Threshold</td>
                                        <td>
                                            Students scanning after this time
                                            are marked as late.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="fw-semibold">OUT Start</td>
                                        <td>
                                            Earliest allowed time for exit scanning.
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="fw-semibold">OUT End</td>
                                        <td>
                                            Latest allowed time for exit scanning.
                                        </td>
                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                    {{-- Example --}}
                    <div class="mb-4">

                        <h6 class="fw-bold mb-3">
                            Example Scenario
                        </h6>

                        <div class="bg-light rounded p-3 small text-muted">

                            <p class="mb-2">
                                Example: HS Afternoon Schedule
                            </p>

                            <ul class="mb-3">
                                <li>IN Start: 12:00 PM</li>
                                <li>IN End: 12:30 PM</li>
                                <li>Late Threshold: 12:15 PM</li>
                                <li>OUT Start: 5:00 PM</li>
                                <li>OUT End: 5:30 PM</li>
                            </ul>

                            <p class="mb-0">
                                Students may scan for entry between
                                12:00 PM and 12:30 PM. Students scanning
                                after 12:15 PM are marked late.
                                Exit scans are only accepted from
                                5:00 PM to 5:30 PM.
                            </p>

                        </div>

                    </div>

                    {{-- FAQ --}}
                    <div>

                        <h6 class="fw-bold mb-3">
                            Frequently Asked Questions
                        </h6>

                        <div class="accordion accordion-flush small" id="scheduleFaq">

                            {{-- FAQ 1 --}}
                            <div class="accordion-item">

                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faqOne">

                                        Why does the scanner say
                                        “No active schedule for current time”?
                                    </button>
                                </h2>

                                <div id="faqOne" class="accordion-collapse collapse" data-bs-parent="#scheduleFaq">

                                    <div class="accordion-body text-muted">
                                        The current time does not match any active
                                        schedule configured for the student's level
                                        and session type.
                                    </div>

                                </div>

                            </div>

                            {{-- FAQ 2 --}}
                            <div class="accordion-item">

                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faqTwo">

                                        Why was the student marked late?
                                    </button>
                                </h2>

                                <div id="faqTwo" class="accordion-collapse collapse" data-bs-parent="#scheduleFaq">

                                    <div class="accordion-body text-muted">
                                        The student scanned after the configured
                                        Late Threshold time.
                                    </div>

                                </div>

                            </div>

                            {{-- FAQ 3 --}}
                            <div class="accordion-item">

                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#faqThree">

                                        Why was the exit scan rejected?
                                    </button>
                                </h2>

                                <div id="faqThree" class="accordion-collapse collapse" data-bs-parent="#scheduleFaq">

                                    <div class="accordion-body text-muted">
                                        The scan happened outside the allowed
                                        OUT window configured for the schedule.
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </div>

    </div>





</x-layouts.admin>