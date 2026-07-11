@php
    $logoOrientation = str_replace('_', '-', strtolower((string) config('website.theme.logo_orientation', 'normal')));
    $logoDynamic = $logoOrientation === 'dynamic';
    $logoDecreasing = $logoOrientation === 'decreasing';
    $logoRotatedLeft = $logoOrientation === 'rotated-left' || $logoDynamic;
    $logoAnimated = $logoDynamic || $logoDecreasing;
    $sidebarSwitcher = $sidebarContext['switcher'];
    $sidebarButton = $sidebarContext['button'];
    $sidebarSwitcherHasOptions = count($sidebarSwitcher['options']) > 0;
    $sidebarButtonOpensAnalysis = ($sidebarButton['route'] ?? null) === 'analiza';
    $showSidebarButton = ! request()->routeIs('analiza');
    $logoHref = $sidebarContext['variant'] === 'family-law' ? route('rozwod') : route('homepage');
@endphp

<div data-site-header>
    <header
        class="site-header-frame fixed inset-x-0 top-0 z-40 border-b border-primary-200/80 bg-white/95 px-4 py-3 shadow-sm backdrop-blur min-[1600px]:hidden"
    >
        <nav class="flex items-center justify-between gap-4" aria-label="Menu mobilne">
            <a href="{{ $logoHref }}" class="min-w-0">
                <span class="sr-only">PRĘDA Kancelaria Adwokacka</span>
                <x-preda.logo class="fill-primary-700" color="primary-700" height="h-10" />
            </a>

            <button
                type="button"
                class="group inline-flex size-12 shrink-0 items-center justify-center rounded-full border border-primary-200 bg-white text-primary-800 transition-colors duration-200 hover:border-accent-200 hover:text-accent-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                @click="openMobileMenu()"
            >
                <span class="sr-only">Otwórz menu</span>
                <x-icon.menu class="size-7" />
            </button>
        </nav>
    </header>

    <a
        href="{{ $logoHref }}"
        @class([
            'duo-main-logo hidden focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-accent-600 min-[1600px]:fixed min-[1600px]:z-40 min-[1600px]:inline-flex min-[1600px]:origin-top-left min-[1600px]:py-0 min-[1600px]:px-[var(--duo-logo-padding-inline)] min-[1600px]:[left:var(--duo-logo-left)] min-[1600px]:[top:var(--duo-logo-top)] min-[1600px]:w-[var(--duo-logo-width)] min-[1600px]:max-w-[9vw] min-[1600px]:transition-[left,top,width,max-width,padding,transform] min-[1600px]:duration-[520ms] min-[1600px]:ease-[cubic-bezier(0.22,1,0.36,1)] min-[1600px]:will-change-[left,top,width,transform]',
            'duo-main-logo--rotated-left' => $logoRotatedLeft,
            'duo-main-logo--dynamic' => $logoDynamic,
            'duo-main-logo--decreasing' => $logoDecreasing,
            'duo-main-logo--large' => $logoDecreasing,
        ])
        @if ($logoAnimated)
            data-duo-dynamic-logo
            data-duo-logo-mode="{{ $logoDecreasing ? 'decreasing' : 'dynamic' }}"
            data-duo-scroll-threshold="30"
        @endif
    >
        <span class="sr-only">PRĘDA Kancelaria Adwokacka</span>
        <x-preda.logo
            class="block w-full fill-primary-700 !h-auto !mt-0 min-[1600px]:translate-x-[var(--duo-logo-optical-shift)] min-[1600px]:transition-[margin-top,transform] min-[1600px]:duration-[520ms] min-[1600px]:ease-[cubic-bezier(0.22,1,0.36,1)]"
            color="primary-700"
            :height="$logoRotatedLeft || $logoDecreasing ? '' : 'h-12'"
        />
    </a>

    <aside
        class="site-header-frame fixed inset-y-0 right-0 z-50 hidden w-[var(--duo-sidebar-width)] bg-white text-primary-950 min-[1600px]:flex"
        aria-label="Menu główne"
        x-data="duoSidebar()"
        @keydown.escape.window="sidebarView === 'analysis' && closeAnalysisSidebar()"
    >
        <div class="relative h-full w-full overflow-hidden">
            <div
                x-show="sidebarView === 'menu'"
                x-transition:enter="transform transition ease-out duration-300 motion-reduce:transition-none"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-300 motion-reduce:transition-none"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="absolute inset-0 flex h-full w-full flex-col px-7 py-8 2xl:px-8"
                :aria-hidden="sidebarView !== 'menu'"
                x-bind:inert="sidebarView !== 'menu'"
            >
                <div class="mb-16 flex flex-wrap items-center gap-x-4 gap-y-3 text-sm text-secondary-600">
                    <a
                        href="mailto:kancelaria@preda.info"
                        class="inline-flex items-center gap-2 whitespace-nowrap transition-colors duration-200 hover:text-accent-600"
                    >
                        <x-icon.envelope class="size-5 text-secondary-400" />
                        kancelaria@preda.info
                    </a>

                    <a
                        href="tel:+48666580580"
                        class="inline-flex items-center gap-2 whitespace-nowrap transition-colors duration-200 hover:text-accent-600"
                    >
                        <x-icon.phone class="size-5 text-secondary-400" />
                        +48 666 580 580
                    </a>
                </div>

                @if($sidebarSwitcherHasOptions)
                    <div class="mt-[50px] flex flex-col">
                        <div class="mt-[50px] mb-[25px] flex flex-col items-start justify-start gap-[0.55rem]">
                            <p class="text-[0.68rem] font-normal leading-none tracking-[0.08em] text-secondary-400 uppercase">Specjalizacje</p>

                            <div
                                x-data="{ open: false }"
                                @click.outside="open = false"
                                @keydown.escape.window="open = false"
                                class="relative inline-block w-max min-w-[min(300px,100%)] max-w-[min(max(50%,300px),100%)] text-primary-950 after:absolute after:left-0 after:top-full after:z-[9] after:h-[3px] after:w-full after:content-['']"
                                aria-label="Wybór obszaru sprawy"
                            >
                                <a
                                    href="{{ $sidebarSwitcher['current']['href'] }}"
                                    class="flex box-border w-full max-w-full items-center justify-between gap-[0.45rem] whitespace-nowrap rounded-xl border border-primary-200 bg-primary-50 px-[15px] pt-4 pb-3.5 text-base font-semibold leading-normal tracking-normal text-primary-900 normal-case"
                                    aria-current="true"
                                    :aria-expanded="open ? 'true' : 'false'"
                                    @click.prevent="open = !open"
                                >
                                    <span>{{ $sidebarSwitcher['current']['label'] }}</span>
                                    <x-icon.chevron-down class="size-[0.7rem] flex-none stroke-[2.5] transition-transform duration-[160ms]" x-bind:class="{ 'rotate-180': open }" />
                                </a>

                                <div
                                    class="absolute left-0 top-[calc(100%+3px)] z-10 w-max min-w-full rounded-xl border border-primary-200 bg-primary-50 p-0 text-primary-900 transition-[opacity,transform,visibility] duration-[160ms]"
                                    x-bind:class="open ? 'visible translate-y-0 opacity-100' : 'invisible -translate-y-1 opacity-0'"
                                    :aria-hidden="open ? 'false' : 'true'"
                                >
                                    @foreach($sidebarSwitcher['options'] as $switcherOption)
                                        <a href="{{ $switcherOption['href'] }}" class="block whitespace-nowrap px-[15px] pt-4 pb-3.5 text-base font-semibold leading-normal tracking-normal text-primary-900 normal-case transition-colors duration-[160ms] hover:bg-primary-100 hover:text-primary-950 focus-visible:bg-primary-100 focus-visible:text-primary-950" @click="open = false">
                                            {{ $switcherOption['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-[50px] mb-[25px] flex min-h-[77px] flex-col items-start justify-start gap-[0.55rem]" aria-hidden="true"></div>
                @endif

                <div class="min-h-0 flex-1 overflow-y-auto pr-1">
                    <x-theme::navigation :variant="$sidebarContext['variant']" />
                </div>

                @if($showSidebarButton)
                    <div class="mt-8 border-t border-primary-200 pt-6">
                        <p class="text-xs font-semibold uppercase text-secondary-500">{{ $sidebarButton['eyebrow'] }}</p>

                        @if($sidebarButtonOpensAnalysis)
                            <x-button.primary-link
                                href="{{ route($sidebarButton['route']) }}"
                                class="mt-3 w-full justify-between"
                                title="{{ $sidebarButton['text'] }}"
                                @click.prevent="openAnalysisSidebar()"
                            >
                                <span>{{ $sidebarButton['text'] }}</span>
                            </x-button.primary-link>
                        @else
                            <x-button.primary-link
                                href="{{ route($sidebarButton['route']) }}"
                                class="mt-3 w-full justify-between"
                                title="{{ $sidebarButton['text'] }}"
                            >
                                <span>{{ $sidebarButton['text'] }}</span>
                            </x-button.primary-link>
                        @endif
                    </div>
                @endif
            </div>

            @if($showSidebarButton && $sidebarButtonOpensAnalysis)
                <div
                    x-ref="analysisSidebarPanel"
                    data-duo-analysis-sidebar-panel
                    x-show="sidebarView === 'analysis'"
                    x-transition:enter="duo-analysis-sidebar-panel--enter"
                    x-transition:enter-start="duo-analysis-sidebar-panel--offscreen"
                    x-transition:enter-end="duo-analysis-sidebar-panel--onscreen"
                    x-transition:leave="duo-analysis-sidebar-panel--leave"
                    x-transition:leave-start="duo-analysis-sidebar-panel--onscreen"
                    x-transition:leave-end="duo-analysis-sidebar-panel--offscreen"
                    class="duo-analysis-sidebar-panel fixed top-0 right-0 z-10 min-h-svh bg-[#f4f7fa] px-7 py-8 will-change-transform 2xl:px-8"
                    style="display: none;"
                    x-bind:style="analysisPanelStyle()"
                    :aria-hidden="sidebarView !== 'analysis'"
                    x-bind:inert="sidebarView !== 'analysis'"
                >
                    <div class="flex items-center justify-between gap-4 border-b border-primary-200 pb-5" data-analysis-sidebar-heading>
                        <div data-analysis-sidebar-heading-copy>
                            <h2 class="text-2xl font-bold leading-tight tracking-tight text-primary-700">
                                Sprawdź swój kredyt
                            </h2>
                            <p class="mt-1 text-[0.8125rem] leading-none text-secondary-500">
                                Wypełnij formularz, by dowiedzieć się, czy można unieważnić Twoją umowę.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="inline-flex size-11 flex-none items-center justify-center rounded-full border border-primary-200 bg-white text-primary-800 transition-colors duration-200 hover:border-accent-200 hover:text-accent-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600"
                            @click="closeAnalysisSidebar()"
                        >
                            <span class="sr-only">Zamknij formularz</span>
                            <x-icon.close class="size-6" />
                        </button>
                    </div>

                    <div class="py-6">
                        <livewire:website.analysis-form context="sidebar" wire:key="sidebar-analysis-form" />
                    </div>
                </div>
            @endif
        </div>
    </aside>

    <x-theme::navigation :mobile="true" :variant="$sidebarContext['variant']" :switcher="$sidebarSwitcher" />
</div>
