<div class="col-md-3 col-sm-6">
            <div class="stat-card shadow-sm card h-100 border">
                <div class="d-flex align-items-center p-3 gap-3">
                    <div class="stat-icon-circle {{ $bgColor }} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 40px; height: 40px">
                        <i class="{{ $icon }} fs-5 text-white {{ $textColor }}"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small fw-bold text-uppercase mb-1">
                          {{$title}}
                        </h6>
                        <h4 class="fw-bold mb-0 {{$textColor}}">{{ $value }}</h4>
                    </div>
                </div>
         </div>
</div>