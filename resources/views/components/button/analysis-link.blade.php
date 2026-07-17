@props([
    'outline' => false,
    'dark' => false,
    'muted' => false,
    'size' => 'md',
])

@php
    $analysisClickHandler = "if (window.matchMedia('(min-width: 1600px)').matches && document.querySelector('[data-duo-analysis-sidebar-panel]')) { \$event.preventDefault(); window.dispatchEvent(new CustomEvent('site:open-analysis-sidebar')); }";
@endphp

<x-button.primary-link
    :outline="$outline"
    :dark="$dark"
    :muted="$muted"
    :size="$size"
    href="{{ route('analiza') }}"
    x-on:click="{{ $analysisClickHandler }}"
    {{ $attributes }}
>
    {{ $slot }}
</x-button.primary-link>
