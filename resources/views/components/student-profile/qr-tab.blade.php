<div class="tab-pane fade" id="qr" role="tabpanel" aria-labelledby="qr-tab">

    @if($student->qrCode && $student->qrCode->image_path)
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <img src="{{ asset('storage/' . $student->qrCode->image_path) }}"
                            class="img-fluid rounded border shadow-sm"
                            style="width:220px; cursor:pointer; transition:.2s;"
                            data-bs-toggle="modal"
                            data-bs-target="#studentQrModal"
                            id="studentQrPreview">

                        <h5 class="fw-bold mt-4 mb-1">{{ $student->first_name }} {{ $student->last_name }}</h5>
                        <p class="text-muted mb-2">{{ $student->student_number }}</p>
                        <small class="text-muted">Click the QR code to enlarge.</small>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning">QR Code has not been generated yet.</div>
    @endif

</div>

@if($student->qrCode && $student->qrCode->image_path)
    <div class="modal fade" id="studentQrModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Student QR Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <img src="{{ asset('storage/' . $student->qrCode->image_path) }}" class="img-fluid border rounded" style="max-width:320px;">

                    <h5 class="fw-bold mt-4 mb-1">
                        {{ strtoupper($student->last_name) }}, {{ strtoupper($student->first_name) }}
                    </h5>
                    <p class="text-muted mb-1">{{ $student->student_number }}</p>
                    <p class="text-muted">
                        {{ $currentEnrollment?->section?->grade_level ?? '-' }} - {{ $currentEnrollment?->section?->name ?? '-' }}
                    </p>
                </div>

                <div class="modal-footer justify-content-between">
                    <a href="{{ route('students.qr.download', $student) }}" class="btn btn-primary">
                        <i class="fa-solid fa-download me-2"></i>Download PDF
                    </a>
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>
@endif

<style>
    #studentQrPreview:hover {
        transform: scale(1.05);
    }
</style>