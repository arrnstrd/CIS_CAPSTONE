@props(['tabs' => [], 'id' => null])

<div class="d-flex align-items-center justify-content-between mx-3 mb-3 pb-2 border-bottom">
    <ul class="sa-nav-tabs nav nav-pills gap-1 p-1 bg-light rounded-3 border" @if($id) id="{{ $id }}" @endif
        role="tablist">
        @foreach ($tabs as $tab)
            @php
                $isActive = $tab['active'] ?? false;
                $tabClasses = 'nav-link px-3 py-1.5 fw-semibold' . ($isActive ? ' active' : '');
            @endphp
            <li class="nav-item">
                @if (!empty($tab['href']))
                    <a class="{{ $tabClasses }}" @if($isActive) aria-current="page" @endif href="{{ $tab['href'] }}">
                        @if (!empty($tab['icon']))
                            <i class="{{ $tab['icon'] }} me-1.5" aria-hidden="true"></i>
                        @endif
                        {{ $tab['label'] }}
                    </a>
                @else
                    <button type="button" class="{{ $tabClasses }}" data-bs-toggle="tab"
                        data-bs-target="{{ $tab['target'] ?? '' }}" role="tab" @if($isActive) aria-selected="true" @else
                        aria-selected="false" @endif>
                        @if (!empty($tab['icon']))
                            <i class="{{ $tab['icon'] }} me-1.5" aria-hidden="true"></i>
                        @endif
                        {{ $tab['label'] }}
                    </button>
                @endif
            </li>
        @endforeach
    </ul>
</div>