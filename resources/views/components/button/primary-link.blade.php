@props([
    'outline' => false,
    'dark' => false,
    'muted' => false,
    'size' => 'md',
    'as' => 'a',
    'type' => 'button',
])

@php
    $size = in_array($size, ['sm', 'md'], true) ? $size : 'md';

    $baseClasses = 'group justify-center inline-flex items-center gap-x-1.5
    rounded-full shadow-xs focus-visible:outline-2 focus-visible:outline-offset-2';

    $sizeClasses = [
        'sm' => 'h-10 pl-4 pr-1 text-xs',
        'md' => 'h-14 pl-6 pr-1 text-sm sm:text-base lg:py-3',
    ];

    $iconWrapClasses = [
        'sm' => 'ml-1.5 rounded-full p-1.5',
        'md' => 'ml-2 rounded-full p-2',
    ];

    $iconClasses = [
        'sm' => '!size-4',
        'md' => '',
    ];

    $iconMotionClasses = $outline
        ? 'rotate-45 group-hover:rotate-90'
        : '-rotate-45 group-hover:rotate-0';
@endphp

@php
    $classes = [
        $baseClasses,
        $sizeClasses[$size],
        'border border-secondary-200 bg-white text-secondary-800 hover:bg-secondary-50 focus-visible:outline-secondary-400 !shadow-none font-semibold' => $muted,
        // Solid variant
        'border-2 border-transparent bg-accent-600 text-white hover:bg-accent-500 focus-visible:outline-accent-600 font-semibold' => !$outline && !$muted,
        // Outline variant
        'border-[3px] border-primary-700 text-primary-700 bg-white hover:bg-primary-50 focus-visible:outline-primary-700' => $outline && !$dark && !$muted,
        'border-[3px] border-white text-primary-300 bg-transparent hover:bg-white/10 focus-visible:outline-primary-500' => $outline && $dark && !$muted,
    ];
@endphp

@if($as === 'button')
    <button
        type="{{ $type }}"
        {{ $attributes->class($classes) }}
    >
        <div class="relative overflow-hidden py-0.5">
            <div class="flex items-center transition-transform duration-500 ease-out group-hover:-translate-y-full">
                {{ $slot }}
            </div>

            <div aria-hidden="true" class="pointer-events-none absolute inset-0 flex items-center translate-y-full transition-transform duration-500 ease-out group-hover:translate-y-0">
                {{ $slot }}
            </div>
        </div>

        <div class="{{ $iconWrapClasses[$size] }}">
            <x-icon.arrow-right class="{{ $iconClasses[$size] }} {{ $iconMotionClasses }} transition-transform duration-300" />
        </div>
    </button>
@else
    <a
        {{ $attributes->class($classes) }}
    >
        <div class="relative overflow-hidden py-0.5">
            <div class="flex items-center transition-transform duration-500 ease-out group-hover:-translate-y-full">
                {{ $slot }}
            </div>

            <div aria-hidden="true" class="pointer-events-none absolute inset-0 flex items-center translate-y-full transition-transform duration-500 ease-out group-hover:translate-y-0">
                {{ $slot }}
            </div>
        </div>

        <div class="{{ $iconWrapClasses[$size] }}">
            <x-icon.arrow-right class="{{ $iconClasses[$size] }} {{ $iconMotionClasses }} transition-transform duration-300" />
        </div>
    </a>
@endif
