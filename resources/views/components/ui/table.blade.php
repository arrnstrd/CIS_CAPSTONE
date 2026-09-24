@props([
    'tableClass' => 'table align-middle mb-0',
    'wrapperClass' => 'table-responsive',
])

<div {{ $attributes->merge(['class' => $wrapperClass]) }}>
    <table class="{{ $tableClass }}">
        {{ $slot }}
    </table>
</div>
