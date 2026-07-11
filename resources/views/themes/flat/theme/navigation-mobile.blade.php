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
        class="fixed inset-0 z-50 overflow-hidden overscroll-contain"
    >
        <div
            class="absolute inset-0"
            @click="closeMobileMenu()"
        ></div>

        <div
            class="absolute inset-3 sm:inset-4"
            x-show="showMenu"
            x-transition:enter="transform transition ease-out duration-400"
            x-transition:enter-start="translate-x-8 opacity-0 scale-[0.98]"
            x-transition:enter-end="translate-x-0 opacity-100 scale-100"
            x-transition:leave="transform transition ease-in-out duration-300"
            x-transition:leave-start="translate-x-0 opacity-100 scale-100"
            x-transition:leave-end="translate-x-8 opacity-0 scale-[0.98]"
        >
            <div class="ml-auto flex h-full w-full max-w-sm flex-col overflow-hidden rounded-[2rem] border border-white/10 bg-secondary-950/92 shadow-2xl ring-1 ring-black/10">
                <div class="flex items-center justify-between px-5 pt-5 pb-3">

                    <a href="{{ route('homepage') }}">
                        <span class="sr-only">PRĘDA Kancelaria Adwokacka</span>
                        <x-preda.logo color="secondary-700" class="fill-secondary-700" />
                    </a>

                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-full border border-white/10 bg-white/5 p-2 text-white/70 transition-colors duration-200 hover:bg-white/10 hover:text-white"
                        @click="closeMobileMenu()"
                    >
                        <span class="sr-only">Zamknij menu</span>
                        <x-icon name="heroicon-s-x-mark" class="w-7" />
                    </button>

                </div>

                <div class="flex-1 overflow-y-auto overscroll-contain px-5 pb-6">
                    <div class="mt-4">
                    <div class="overflow-hidden divide-y divide-white/10 rounded-[1.5rem] border border-white/10 bg-white/4">
                        @foreach($navigation as $navi)

                        <div
                            class=""
                            x-data="{ more_mob_{{ $loop->index }}: false }"
                        >

                            <{{ isset($navi['more']) ? 'button' : 'a' }}

                                @if(isset($navi['more']))
                                    @click="more_mob_{{ $loop->index }} = !more_mob_{{  $loop->index }}"
                                    @keyup.window.escape="more_mob_{{ $loop->index }} = false"
                                    @click.outside="more_mob_{{ $loop->index }} = false"
                                @else
                                    href="{{ route($navi['route']) }}"
                                @endif
                                    class="items-center block w-full px-4 py-3 text-left text-base font-semibold leading-7 text-white transition-colors duration-200 hover:bg-white/8"
                            >
                            <span class="flex justify-between items-center">

                                {{ $navi['text'] }}

                                @if(isset($navi['more']))
                                        <x-icon
                                            name="heroicon-s-chevron-down"
                                            class="w-6 transition-transform duration-200"
                                            x-bind:class="{ 'rotate-180': more_mob_{{ $loop->index }} }"
                                        />
                                @endif
                            </span>

                            </{{ isset($navi['more']) ? 'button' : 'a' }}>

                            @if(isset($navi['more']))
                            <div
                                class="relative mx-3 mb-3 mt-1 grid gap-1"
                                x-show="more_mob_{{ $loop->index }}"
                                x-transition:enter="transition ease-in duration-500"
                                x-transition:enter-start="opacity-0 -translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-out duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-2"
                            >

                                @foreach($navi['more'] as $subnavi)

                                    <div class="flex items-center">

                                        <a
                                            href="{{ route($subnavi['route']) }}"
                                            class="flex flex-1 items-center rounded-xl px-3 py-2.5 text-sm leading-6 text-white/75 transition-[background-color,color] duration-200 hover:bg-white/8 hover:text-white focus-visible:bg-white/8 focus-visible:text-white"
                                        >
                                            {{ $subnavi['text'] }}
                                        </a>
                                    </div>

                                @endforeach

                            </div>
                            @endif

                        </div>

                        @endforeach

                    </div>
                    <div class="py-6">

                        <x-button.primary-link
                            href="{{ route('analiza') }}"
                            class="w-full transition-transform duration-200 hover:scale-[1.02] flex justify-start"
                            title="Sprawdź swój kredyt"
                        >
                            <x-icon.doc class="mr-3" />
                            <span class="sr-only">Sprawdź swój kredyt</span>
                            <span>Sprawdź swój kredyt</span>
                        </x-button.primary-link>

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
