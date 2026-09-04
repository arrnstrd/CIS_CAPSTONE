{{-- Delete Remark Modal --}}
<div class="modal fade" id="deleteRemarkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form method="POST" id="deleteRemarkForm" action="">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" style="font-size: 0.95rem;">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Delete Remark
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-0">Are you sure you want to delete this risk remark? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </div>
            </div>
        </form>
    </div>
</div>
