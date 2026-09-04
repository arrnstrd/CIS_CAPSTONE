<div class="gs-breadcrumb">
    <i class="fa-solid fa-diagram-project gs-breadcrumb-icon"></i>
    @foreach ($crumbs as $i => $crumb)
        @if (!$loop->last)
            <a href="{{ $crumb['url'] }}" class="gs-breadcrumb-link">{{ $crumb['label'] }}</a>
            <i class="fa-solid fa-chevron-right gs-breadcrumb-sep"></i>
        @else
            <span class="gs-breadcrumb-current">{{ $crumb['label'] }}</span>
        @endif
    @endforeach
</div>
