<div class="modal fade" id="{{ $id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold text" 
                id="modalTitle">
                 {{ $modalTitle }}
                </h5>
                {{-- <button class="btn-close" data-bs-dismiss="modal"></button> --}}
            </div>
            <div class="modal-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>