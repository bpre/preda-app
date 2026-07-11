@props([
    'more' => false,
    'button' => false
])
<{{ $button ? 'button' : 'a' }}
    {{ $button ? 'type="button"' : '' }}
    aria-expanded="false"
    class="inline-flex h-10 items-center gap-x-1.5 whitespace-nowrap rounded-full px-3.5 text-base font-semibold text-primary-800 transition-all duration-300 hover:text-accent-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600 min-[1800px]:text-lg"
    {{ $attributes }}
>
    {{ $slot }}
    @if($more)
    <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true" class="size-4 text-secondary-400 transition-transform duration-300 ease-out">
        <path d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" fill-rule="evenodd" />
    </svg>
    @endif
</{{ $button ? 'button' : 'a' }}>
