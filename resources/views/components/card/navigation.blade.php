@props([
    'dropdown' => false,
    'icon' => null,
    'heading' => null,
    'href' => null,
    'route' => null,
])
<div @class([
    'group relative flex p-4',
    'gap-x-6 rounded-lg hover:bg-secondary-100' => !$dropdown,
    'gap-x-5 rounded-[1.25rem] transition-all duration-300' => $dropdown,
])>
    @if($icon)
        <div @class([
            'mt-1 flex size-11 flex-none items-center justify-center bg-secondary-100',
            'rounded-lg group-hover:bg-white' => !$dropdown,
            'rounded-xl transition-all duration-300 group-hover:bg-white' => $dropdown,
        ])>
            <x-icon name="heroicon-o-{{ $icon }}" @class([
                'size-6 text-secondary-400',
                'group-hover:text-accent-600' => !$dropdown,
                'transition-colors duration-300 group-hover:text-accent-600' => $dropdown,
            ]) />
        </div>
    @endif
    <div>
    <a href="{{ $href ?? ($route ? route($route) : '#') }}" @class([
        'text-base leading-6 font-semibold text-secondary-900',
        'transition-colors duration-300 group-hover:text-accent-700' => $dropdown,
    ])>
        {{ $heading }}
        <span class="absolute inset-0"></span>
    </a>
    <p @class([
        'mt-1 text-sm text-secondary-600',
        'leading-6 transition-colors duration-300 group-hover:text-secondary-700' => $dropdown,
    ])>

        {{ $slot }}

    </p>
    </div>
</div>
