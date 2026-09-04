<div class="modal fade" id="{{ $id }}" tabindex="-1">

    <div class="modal-dialog {{ $size ?? 'modal-lg' }}">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title fw-semibold">
                    {{ $modalTitle }}
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>

            </div>

            <div class="modal-body">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endisset

        </div>

    </div>

</div>