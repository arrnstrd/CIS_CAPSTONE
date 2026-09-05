<div class="modal fade" id="exportFormatModal" tabindex="-1" aria-labelledby="exportFormatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success-subtle text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-file-excel fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="exportFormatModalLabel">Export Student List</h5>
                        <p class="text-muted small mb-0">Choose your preferred Excel export format</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ $exportRoute }}" method="GET">
                @if(request('school_year_id')) <input type="hidden" name="school_year_id" value="{{ request('school_year_id') }}"> @endif
                @if(request('section_id')) <input type="hidden" name="section_id" value="{{ request('section_id') }}"> @endif
                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                @if(request('query')) <input type="hidden" name="query" value="{{ request('query') }}"> @endif
                @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif

                <div class="modal-body py-4">
                    <div class="d-flex flex-column gap-3">

                        <!-- Option 1: SF1 Template -->
                        <label class="export-option-card border rounded-3 p-3 d-flex align-items-start gap-3 position-relative cursor-pointer">
                            <input type="radio" name="format" value="sf1" class="form-check-input mt-1" checked>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill small fw-semibold">DepEd Form</span>
                                    <h6 class="fw-bold text-dark mb-0">Official SF1 Template</h6>
                                </div>
                                <p class="text-muted small mb-0">
                                    Official DepEd School Register (SF1) layout with form headers, section branding, and formatted columns.
                                </p>
                            </div>
                        </label>

                        <!-- Option 2: Raw / Unmerged -->
                        <label class="export-option-card border rounded-3 p-3 d-flex align-items-start gap-3 position-relative cursor-pointer">
                            <input type="radio" name="format" value="raw" class="form-check-input mt-1">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 rounded-pill small fw-semibold">Tabular</span>
                                    <h6 class="fw-bold text-dark mb-0">Raw Data (Unmerged Columns)</h6>
                                </div>
                                <p class="text-muted small mb-0">
                                    Clean tabular spreadsheet with styled headers (dark background), zero merged cells, and separate columns for easy copying and pasting into other files.
                                </p>
                            </div>
                        </label>

                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light border px-3 rounded-3 fw-medium" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4 rounded-3 fw-semibold d-inline-flex align-items-center gap-2"
                        onclick="setTimeout(() => { const m = bootstrap.Modal.getInstance(document.getElementById('exportFormatModal')); if (m) m.hide(); }, 500);">
                        <i class="fas fa-download fa-sm"></i>
                        <span>Download Excel</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .export-option-card {
        cursor: pointer;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }
    .export-option-card:hover {
        background-color: rgba(13, 110, 253, 0.02);
        border-color: #0d6efd !important;
    }
</style>
