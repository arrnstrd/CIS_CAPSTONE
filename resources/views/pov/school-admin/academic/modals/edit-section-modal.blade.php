<x-modal id="editSectionModal" modalTitle="Edit Section" size="modal-md">
    <form id="editSectionForm" method="POST" data-update-url="{{ route('sections.update', ':id') }}"
        data-ajax-scope="#section-table-pane">
        @csrf
        @method('PUT')
        <div data-ajax-errors></div>

        {{-- 1. Grade Level Selection First --}}
        <div class="p-3 bg-light rounded-3 border mb-3">
            <label class="form-label fw-bold text-dark d-flex align-items-center gap-1.5 mb-1">
                <i class="fa-solid fa-layer-group text-primary"></i>
                <span>Grade Level <span class="text-danger">*</span></span>
            </label>
            <select name="grade_level" id="edit_section_grade_level" class="form-select border-primary" required>
                <option value="" disabled>-- Select Grade Level --</option>
                @for ($grade = 1; $grade <= 12; $grade++)
                    <option value="{{ $grade }}">Grade {{ $grade }}</option>
                @endfor
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Section Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="edit_section_name" class="form-control js-edit-section-field" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Department Level</label>
                <select name="level" id="edit_section_level" class="form-select bg-light js-edit-section-field" required readonly tabindex="-1">
                    <option value="elementary">Elementary (Grades 1-6)</option>
                    <option value="highschool">High School (Grades 7-10)</option>
                    <option value="senior_high_school">Senior High School (Grades 11-12)</option>
                </select>
                <small class="text-muted" style="font-size: 0.72rem;">Auto-assigned based on Grade Level</small>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Session Type <span class="text-danger">*</span></label>
                <select name="session_type" id="edit_session_type" class="form-select js-edit-section-field" required>
                    <option value="morning">Morning</option>
                    <option value="afternoon">Afternoon</option>
                    <option value="whole_day">Whole Day</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Adviser</label>
            <select name="advisor_id" id="edit_section_advisor_id" class="form-select js-edit-section-field">
                <option value="">Not Assigned</option>
                @foreach ($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Capacity <span class="text-danger">*</span></label>
                <input type="number" name="capacity" id="edit_section_capacity" class="form-control js-edit-section-field" min="1" max="100" required>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                <select name="status" id="edit_section_status" class="form-select js-edit-section-field" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="modal-footer px-0 pb-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-dark js-edit-section-submit" data-loading-text="Saving...">Save Changes</button>
        </div>
    </form>
</x-modal>
