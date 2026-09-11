{{-- Risk Summary --}}
<div class="row g-3 mb-3">
    {{-- Low Risk --}}
    <div class="col-12 col-md-4">
        <div class="gs-stat-card gs-stat-card-success d-flex align-items-center gap-3 h-100">
            <span class="gs-stat-icon gs-stat-icon-success">
                <i class="fa-solid fa-shield-halved"></i>
            </span>
            <div>
                <p class="gs-stat-label gs-stat-label-success mb-1">Low Risk</p>
                <p class="gs-stat-value gs-stat-success mb-0">{{ $stats['low'] }}</p>
            </div>
        </div>
    </div>

    {{-- Moderate Risk --}}
    <div class="col-12 col-md-4">
        <div class="gs-stat-card d-flex align-items-center gap-3 h-100" style="background-color: #FAEEDA; border-color: #f0dfb8;">
            <span class="gs-stat-icon" style="background-color: #f0dfb8; color: #854F0B;">
                <i class="fa-solid fa-circle-exclamation"></i>
            </span>
            <div>
                <p class="gs-stat-label mb-1" style="color: #854F0B;">Moderate Risk</p>
                <p class="gs-stat-value mb-0" style="color: #854F0B;">{{ $stats['moderate'] }}</p>
            </div>
        </div>
    </div>

    {{-- High Risk --}}
    <div class="col-12 col-md-4">
        <div class="gs-stat-card gs-stat-card-danger d-flex align-items-center gap-3 h-100">
            <span class="gs-stat-icon gs-stat-icon-danger">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </span>
            <div>
                <p class="gs-stat-label gs-stat-label-danger mb-1">High Risk</p>
                <p class="gs-stat-value gs-stat-danger mb-0">{{ $stats['high'] }}</p>
            </div>
        </div>
    </div>
</div>
