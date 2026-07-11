@props([
    'outline' => false,
    'dark' => false
])

@php
    $baseClasses = 'justify-center inline-flex items-center gap-x-1.5
    rounded-md px-3.5 py-4 text-sm font-semibold uppercase shadow-xs focus-visible:outline-2 focus-visible:outline-offset-2';
@endphp

<a
    {{ $attributes->class([
        $baseClasses,
        // Solid variant
        'border-2 border-transparent bg-accent-600 text-white hover:bg-accent-500 focus-visible:outline-accent-600' => !$outline,
        // Outline variant
        'border-2 text-primary-900 bg-transparent hover:bg-primary-50 focus-visible:outline-primary-800' => $outline && !$dark,
        'border-2 border-white text-primary-600 bg-transparent hover:bg-white/10 focus-visible:outline-primary-500' => $outline && $dark,
    ]) }}
>
    {{ $slot }}
</a>
