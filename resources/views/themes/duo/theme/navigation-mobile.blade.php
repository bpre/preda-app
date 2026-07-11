@php
    $mobileSwitcher = $switcher ?? null;
    $mobileSwitcherHasOptions = is_array($mobileSwitcher) && count($mobileSwitcher['options'] ?? []) > 0;
@endphp

<template x-teleport="body">
    <div
        x-cloak
        x-show="showMenu"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-out duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.window.escape="closeMobileMenu()"
        role="dialog"
        aria-modal="true"
        class="fixed inset-0 z-50 overflow-hidden overscroll-contain min-[1600px]:hidden"
    >
        <div
            class="absolute inset-0"
            @click="closeMobileMenu()"
        ></div>

        <div
            class="absolute inset-y-0 right-0 w-full max-w-sm"
            x-show="showMenu"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-240"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            <div class="flex h-full w-full flex-col overflow-hidden border-l border-primary-200 bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-primary-200 px-5 py-4">
                    <a href="{{ route('homepage') }}">
                        <span class="sr-only">PRĘDA Kancelaria Adwokacka</span>
                        <x-preda.logo color="primary-700" class="fill-primary-700" height="h-10" />
                    </a>

                    <button
                        type="button"
                        class="inline-flex size-11 items-center justify-center rounded-full border border-primary-200 bg-white text-primary-800 transition-colors duration-200 hover:border-accent-200 hover:text-accent-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                        @click="closeMobileMenu()"
                    >
                        <span class="sr-only">Zamknij menu</span>
                        <x-icon.close class="size-6" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-6">
                    @if($mobileSwitcherHasOptions)
                        <div class="mb-6 border-b border-primary-200 pb-6">
                            <p class="text-[0.68rem] font-normal leading-none tracking-[0.08em] text-secondary-400 uppercase">Specjalizacje</p>

                            <div
                                x-data="{ open: false }"
                                @click.outside="open = false"
                                @keydown.escape.window="open = false"
                                class="relative mt-3 text-primary-950"
                                aria-label="Wybór obszaru sprawy"
                            >
                                <a
                                    href="{{ $mobileSwitcher['current']['href'] }}"
                                    class="flex w-full items-center justify-between gap-3 rounded-xl border border-primary-200 bg-primary-50 px-4 py-3 text-base font-semibold text-primary-900 transition-colors duration-200 hover:bg-primary-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                                    aria-current="true"
                                    :aria-expanded="open ? 'true' : 'false'"
                                    @click.prevent="open = !open"
                                >
                                    <span>{{ $mobileSwitcher['current']['label'] }}</span>
                                    <x-icon.chevron-down class="size-4 flex-none stroke-[2.5] transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                                </a>

                                <div
                                    x-cloak
                                    x-show="open"
                                    x-collapse
                                    class="mt-2 grid overflow-hidden rounded-xl border border-primary-200 bg-primary-50 text-primary-900"
                                    :aria-hidden="open ? 'false' : 'true'"
                                >
                                    @foreach($mobileSwitcher['options'] as $switcherOption)
                                        <a
                                            href="{{ $switcherOption['href'] }}"
                                            class="block px-4 py-3 text-base font-semibold text-primary-900 transition-colors duration-200 hover:bg-primary-100 focus-visible:bg-primary-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                                            @click="closeMobileMenu(); open = false"
                                        >
                                            {{ $switcherOption['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <nav aria-label="Menu mobilne">
                        <ul class="grid gap-1.5">
                            @foreach($navigation as $navi)
                                @if(($navi['type'] ?? null) === 'spacer')
                                    <li class="h-5" aria-hidden="true"></li>
                                    @continue
                                @endif

                                <li x-data="{ open: @js((bool) ($navi['active'] ?? false)) }">
                                    @if(isset($navi['more']))
                                        <button
                                            type="button"
                                            @class([
                                                'flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 text-left text-base font-semibold text-primary-800 transition-colors duration-200 hover:bg-primary-50 focus-visible:bg-primary-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600',
                                                '!text-accent-600' => $navi['active'] ?? false,
                                            ])
                                            @click="open = !open"
                                            @keyup.window.escape="open = false"
                                            :aria-expanded="open ? 'true' : 'false'"
                                        >
                                            <span>{{ $navi['text'] }}</span>
                                            <x-icon.chevron-down class="size-5 shrink-0 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                                        </button>

                                        <div
                                            x-cloak
                                            x-show="open"
                                            x-collapse
                                            class="my-1 grid gap-1 pl-3"
                                        >
                                            @foreach($navi['more'] as $subnavi)
                                                <a
                                                    href="{{ route($subnavi['route']) }}"
                                                    @class([
                                                        'block rounded-lg px-4 py-2.5 text-sm leading-6 text-secondary-600 transition-colors duration-200 hover:bg-secondary-50 focus-visible:bg-secondary-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600',
                                                        '!text-accent-600' => $subnavi['active'] ?? false,
                                                    ])
                                                    @if($subnavi['active'] ?? false) aria-current="page" @endif
                                                    @click="closeMobileMenu()"
                                                >
                                                    {{ $subnavi['text'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <a
                                            href="{{ route($navi['route']) }}"
                                            @class([
                                                'flex w-full items-center justify-between gap-3 rounded-xl px-4 py-3 text-left text-base font-semibold text-primary-800 transition-colors duration-200 hover:bg-primary-50 focus-visible:bg-primary-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600',
                                                '!text-accent-600' => $navi['active'] ?? false,
                                            ])
                                            @if($navi['active'] ?? false) aria-current="page" @endif
                                            @click="closeMobileMenu()"
                                        >
                                            <span>{{ $navi['text'] }}</span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </nav>

                    <x-button.primary-link
                        href="{{ route($button['route']) }}"
                        class="mt-8 w-full justify-between"
                        title="{{ $button['text'] }}"
                        @click="closeMobileMenu()"
                    >
                        <span>{{ $button['text'] }}</span>
                    </x-button.primary-link>
                </div>
            </div>
        </div>
    </div>
</template>
