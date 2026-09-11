{{-- Add Follow-Up Modal --}}
<div class="modal fade" id="addFollowUpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('teacher.grading-system.at-risk.follow-ups.store', $enrollment->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size: 0.95rem;">
                        <i class="fa-solid fa-clipboard-check text-primary me-2"></i>Add Monitoring Follow-Up
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="followUpDate" class="form-label small text-muted">Follow-Up Date <span class="text-danger">*</span></label>
                        <input type="date" name="follow_up_date" id="followUpDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="interventionText" class="form-label small text-muted">Intervention / Action Taken <span class="text-danger">*</span></label>
                        <input type="text" name="intervention" id="interventionText" class="form-control form-control-sm" required maxlength="255" placeholder="e.g. Parent Consultation, Remedial Tutoring, Student Conference">
                    </div>

                    <div class="mb-3">
                        <label for="statusSelect" class="form-label small text-muted">Status <span class="text-danger">*</span></label>
                        <select name="status" id="statusSelect" class="form-select form-select-sm" required>
                            <option value="Completed" selected>Completed</option>
                            <option value="Ongoing">Ongoing</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label for="followUpNotes" class="form-label small text-muted">Follow-Up Notes / Outcome</label>
                        <textarea name="notes" id="followUpNotes" class="form-control" rows="3" maxlength="2000" placeholder="e.g. Met with guardian; agreed on weekly study plan and attendance monitoring."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Follow-Up</button>
                </div>
            </div>
        </form>
    </div>
</div>

