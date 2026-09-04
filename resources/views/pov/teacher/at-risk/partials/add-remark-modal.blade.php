{{-- Add Remark Modal --}}
<div class="modal fade" id="addRemarkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('teacher.grading-system.at-risk.remarks.store', $enrollment->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" style="font-size: 0.95rem;">Add Teacher Risk Remark</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="remarkText" class="form-label small text-muted">Observation / Remark</label>
                    <textarea name="remark" id="remarkText" class="form-control" rows="4" maxlength="2000" required placeholder="e.g. Discussed attendance concern with student, will monitor next two weeks."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Remark</button>
                </div>
            </div>
        </form>
    </div>
</div>
