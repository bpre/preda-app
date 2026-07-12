<div class="flex items-center gap-x-1">

    <x-button.navigation href="{{ route('kredyty-frankowe') }}">
        Kredyty frankowe
    </x-button.navigation>

    <x-button.navigation href="{{ route('kredyty-euro') }}">
        Kredyty EUR
    </x-button.navigation>

    <x-button.navigation href="{{ route('wyroki') }}">
        Nasze wyroki
    </x-button.navigation>

    <x-button.navigation href="{{ route('analiza') }}">
        Analiza umowy
    </x-button.navigation>

    <x-button.navigation href="{{ route('kancelaria') }}">
        O nas
    </x-button.navigation>

    <x-button.navigation href="{{ route('kontakt') }}">
        Kontakt
    </x-button.navigation>

    <div
        x-data="{
            open: false,
            closeTimer: null,
            showMenu() {
                clearTimeout(this.closeTimer);
                this.open = true;
            },
            hideMenu() {
                clearTimeout(this.closeTimer);
                this.closeTimer = setTimeout(() => {
                    this.open = false;
                }, 180);
            },
            cancelHide() {
                clearTimeout(this.closeTimer);
            }
        }"
        class="group/menu relative"
        @mouseenter="showMenu()"
        @mouseleave="hideMenu()"
        @focusin="showMenu()"
        @focusout="if (!$el.contains($event.relatedTarget)) hideMenu()"
    >
        <button
            type="button"
            class="group inline-flex h-10 items-center gap-x-1.5 whitespace-nowrap rounded-full px-3.5 text-base font-semibold text-primary-800 transition-all duration-300 hover:text-accent-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600 min-[1800px]:text-lg"
            :class="open ? 'text-accent-600' : ''"
            :aria-expanded="open ? 'true' : 'false'"
        >
            Więcej

            <svg
                viewBox="0 0 20 20"
                fill="currentColor"
                aria-hidden="true"
                class="size-5 text-secondary-400 transition-all duration-300 ease-out group-hover:text-accent-500"
                :class="open ? 'rotate-180 text-accent-500' : ''"
            >
                <path d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" fill-rule="evenodd" />
            </svg>
        </button>

        <div
            x-cloak
            x-show="open"
            class="absolute left-1/2 top-full z-20 w-[28rem] -translate-x-1/2 px-2 pt-3"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 translate-y-3 scale-[0.98]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-180"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-[0.99]"
            @mouseenter="cancelHide()"
            @mouseleave="hideMenu()"
        >
            <div class="overflow-hidden rounded-[1.75rem] border border-primary-200/80 bg-white p-4 shadow-[0_24px_70px_-32px_rgba(15,23,42,0.38)] ring-1 ring-primary-100/70">

                <x-card.navigation
                    dropdown
                    heading="Blog"
                    icon="document-text"
                    route="blog"
                >
                    Bądź na bieżąco w sprawa kredytów powiązanych z walutami obcymi.<br>
                    Zagadnienia prawne, argumentacja, analizy.
                </x-card.navigation>

                <x-card.navigation
                    dropdown
                    heading="Orzecznictwo"
                    icon="document-text"
                    route="orzecznictwo"
                >
                    Najważniejsze orzeczenia TSUE oraz Sądu Najwyższego w sprawach kredytów powiązanych z walutami obcymi.
                </x-card.navigation>

                <x-card.navigation
                    dropdown
                    heading="Częste pytania"
                    icon="document-text"
                    route="faq"
                >
                    Odpowiedzi na najczęściej pojawiające się pytania dotyczące kredytów powiązanych z walutami obcymi.
                </x-card.navigation>

                <x-card.navigation
                    dropdown
                    heading="Klauzule niedozwolone"
                    icon="document-text"
                    route="klauzule-niedozwolone"
                >
                    Sprawdź czy w Twojej umowie kredytu frankowego lub kredytu w euro występują niedozwolone postanowienia.
                </x-card.navigation>

                <x-card.navigation
                    dropdown
                    heading="Spłacony kredyt frankowy"
                    icon="document-text"
                    route="splacony-kredyt"
                >
                    Spłaciłeś kredyt frankowy?<br>Prawdopodobnie bank wciąż jest Ci winien pieniądze!
                </x-card.navigation>

            </div>
        </div>
    </div>

</div>
