@php
    $headerShadowsEnabled = (bool) config('website.theme.shadows', true);
    $headerBorderBottomEnabled = (bool) config('website.theme.header_border_bottom', false) && ! $headerShadowsEnabled;
@endphp

<div
    data-site-header
    x-data="{
        open: null,
        navElevated: false,
        headerOffset: 0,
        headerMaxOffset: 0,
        headerAutoHideEnabled: false,
        headerAutoHideQuery: null,
        lastScrollY: 0,
        scrollFactor: 1.5,
        scrollThreshold: 200,
        headerBorderThreshold: 100,
        headerBorderBottomEnabled: @js($headerBorderBottomEnabled),
        headerBorderVisible: false,
        ticking: false,
        updateHeaderChrome(currentScrollY) {
            this.navElevated = currentScrollY > this.scrollThreshold;
            this.headerBorderVisible = this.headerBorderBottomEnabled && currentScrollY >= this.headerBorderThreshold;
        },
        measureHeader() {
            const currentScrollY = window.scrollY;
            this.headerMaxOffset = this.$refs.header?.offsetHeight ?? 0;
            this.lastScrollY = currentScrollY;
            this.updateHeaderChrome(currentScrollY);

            if (! this.headerAutoHideEnabled) {
                this.headerOffset = 0;
                return;
            }

            this.headerOffset = Math.min(currentScrollY * this.scrollFactor, this.headerMaxOffset);
        },
        handleScroll() {
            const currentScrollY = window.scrollY;
            const scrollDelta = currentScrollY - this.lastScrollY;

            this.updateHeaderChrome(currentScrollY);

            if (! this.headerAutoHideEnabled) {
                this.headerOffset = 0;
                this.lastScrollY = currentScrollY;
                return;
            }

            this.headerOffset = Math.min(
                Math.max(this.headerOffset + (scrollDelta * this.scrollFactor), 0),
                this.headerMaxOffset,
            );

            if (this.headerOffset >= this.headerMaxOffset) {
                this.open = null;
            }

            this.lastScrollY = currentScrollY;
        },
        queueScroll() {
            if (this.ticking) {
                return;
            }

            this.ticking = true;

            requestAnimationFrame(() => {
                this.handleScroll();
                this.ticking = false;
            });
        },
        syncHeaderAutoHideMode() {
            this.headerAutoHideEnabled = this.headerAutoHideQuery?.matches ?? false;
            this.measureHeader();
        },
        setupHeaderAutoHide() {
            this.headerAutoHideQuery = window.matchMedia('(min-width: 1024px)');
            this.syncHeaderAutoHideMode();

            if (typeof this.headerAutoHideQuery.addEventListener === 'function') {
                this.headerAutoHideQuery.addEventListener('change', () => this.syncHeaderAutoHideMode());
                return;
            }

            this.headerAutoHideQuery.addListener(() => this.syncHeaderAutoHideMode());
        }
    }"
    x-init="
        $nextTick(() => setupHeaderAutoHide());
    "
    @scroll.window="queueScroll()"
    @resize.window.debounce.100ms="measureHeader()"
>

    @if(false)
    <div class="h-8 fixed top-0 w-full content-center z-40">
        <div class="mx-auto text-primary-500 text-xs flex">
            <div class="flex-1 xl:flex items-center">
                <div class="flex">
            <strong class="font-semibold mr-2 content-center">Oddziały:</strong>

            <ul class="flex">
                @foreach($offices as $office)
                <li
                    class="relative"
                    @mouseenter="open = '{{ $office->slug }}'"
                    @mouseleave="open = null"
                >
                    <a
                    href="{{ route($office->slug) }}"
                    class="items-center hover:bg-primary-200 p-2 inline-flex"
                    >
                    {{ $office->city }}
                    </a>

                    <ul
                    x-cloak
                    x-show="open === '{{ $office->slug }}'"
                    x-transition:enter="transition zoom-in duration-500"
                    x-transition:enter-start="opacity-0 translate-y-5"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    @click.outside="open = null"
                    class="absolute left-0 top-full z-50 px-4 w-[226px] -ml-4"
                    >
                    <li><a href="{{ route('city-chf' , $office->slug) }}" class="block bg-primary-100 p-2 hover:bg-primary-200">Kredyty frankowe {{ $office->city }}</a></li>
                    <li><a href="{{ route('city-euro' , $office->slug) }}" class="block bg-primary-100 p-2 hover:bg-primary-200">Kredyty w euro {{ $office->city }}</a></li>
                    </ul>
                </li>
                @endforeach
            </ul>

                </div>
            </div>

            <div class="flex">
                <a
                    href="mailto:kancelaria@preda.info"
                    class="flex mr-8 items-center hover:bg-primary-200 p-2"
                >
                    <x-icon.envelope class="mr-2"/>
                    kancelaria@preda.info
                </a>

                <a
                    href="tel:+48666580580"
                    class="flex items-center hover:bg-primary-200 p-2"
                >
                    <x-icon.phone class="mr-1"/>
                    +48 666 580 580
                </a>

            </div>
        </div>
    </div>
    @endif

    <header
        x-ref="header"
        @class([
            'site-header-frame fixed inset-x-0 top-0 z-40 h-20 border-t bg-white px-[var(--site-page-gutter)] text-white transform-gpu transition-shadow duration-300 will-change-transform motion-reduce:transition-none sm:h-24',
            'border-b' => $headerBorderBottomEnabled,
        ])
        @if ($headerBorderBottomEnabled)
            :class="[
                navElevated ? 'shadow-sm' : 'shadow-none',
                headerBorderVisible ? 'border-secondary-200/80' : 'border-transparent',
            ]"
        @else
            :class="navElevated ? 'shadow-sm' : 'shadow-none'"
        @endif
        :style="{ transform: `translate3d(0, -${headerOffset}px, 0)` }"
    >
        <nav class="mx-auto flex h-full w-full items-center justify-between" aria-label="Global">

                <div class="ml-3 flex">
                    <a href="{{ route('homepage') }}">
                        <span class="sr-only">PRĘDA Kancelaria Adwokacka</span>
                        <x-preda.logo class="fill-primary-700" color="primary-700" height="h-12 md:h-14" />

                    </a>
                </div>

                <div class="flex flex-1 items-center justify-end">
                    <div class="hidden flex-1 items-center justify-center 2xl:flex">
                        <x-theme::navigation />
                    </div>

                    <div class="flex items-center justify-end gap-2 sm:gap-3 2xl:ml-8">

                        <div class="hidden sm:flex">
                            <x-button.primary-link
                                href="{{  route('analiza') }}"
                                title="Sprawdź swój kredyt"
                            >
                                <span class="sr-only">Sprawdź swój kredyt</span>
                                <span>Sprawdź swój kredyt</span>
                            </x-button.primary-link>
                        </div>

                        <button type="button" class="group mr-0 inline-flex items-center justify-center rounded-md p-2.5 sm:-mr-2 2xl:hidden" @click="openMobileMenu()">
                            <span class="sr-only">Otwórz menu</span>
                            <x-icon.menu class="w-9 h-9 text-secondary-700" />
                        </button>

                    </div>
                </div>

                <div class="absolute">
                    <!-- Mobile menu -->
                    <x-theme::navigation :mobile="true" />
                </div>

        </nav>

    </header>
</div>
