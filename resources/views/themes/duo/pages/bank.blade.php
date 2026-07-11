<x-theme::app>

    <x-theme::header />

    @php
        $accordionCardClasses = 'overflow-hidden rounded-[1.25rem] border border-primary-200 bg-white';
        $accordionButtonClasses = 'flex w-full items-center justify-between bg-primary-50 px-6 py-4 text-left transition-colors duration-200 hover:bg-primary-100 focus-visible:bg-primary-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-600';
        $accordionTitleClasses = 'text-lg font-semibold text-primary-900';
        $accordionIconClasses = 'h-5 w-5 text-secondary-500 transition-transform duration-200';
        $accordionPanelClasses = 'overflow-hidden bg-white';
        $accordionBodyClasses = 'border-t border-primary-200 p-6 prose prose-slate';
    @endphp

    <x-section::frame
        :heading="$h1"
        :subheading="$h2"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
        :useH1="true"
    >
        <div class="max-w-3xl prose prose-slate text-xl mt-12">
        {{ $content }}
        </div>

    </x-section::frame>




    <!-- Klauzule - CHF -->
    <x-section::frame
        heading="Kredyty frankowe {{ $bank->form_a }}"
        subheading="Klauzule niedozwolone w kredytach frankowych {{ $bank->form_a }}"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :alternate="true"
        :full="true"
    >

        <div class="md:flex gap-12">

            <div class="flex-1 mb-12 prose">
                {!! $bank->desc_chf !!}
            </div>

            @php($firstPublishedChfCreditId = $bank->credits_chf->firstWhere('is_published', true)?->id)

            <div class="flex-1 space-y-4" x-data="{ openItem: @js($firstPublishedChfCreditId) }">
                @foreach($bank->credits_chf as $credit)

                    @if($credit->is_published)

                    <div class="{{ $accordionCardClasses }}">
                        <button
                            class="{{ $accordionButtonClasses }}"
                            @click="openItem = openItem === {{ $credit->id }} ? null : {{ $credit->id }}"
                            :aria-expanded="openItem === {{ $credit->id }}"
                        >
                            <span class="{{ $accordionTitleClasses }}">
                                Umowa z {{ $credit->credit_year }} r. {{ mos($credit->credit_name !== '_' ? ' - '.$credit->credit_name : '') }}
                            </span>
                            <svg
                                class="{{ $accordionIconClasses }}"
                                :class="{ 'rotate-180': openItem === {{ $credit->id }} }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            x-cloak
                            x-show="openItem === {{ $credit->id }}"
                            x-collapse
                            :aria-hidden="openItem !== {{ $credit->id }}"
                            :inert="openItem !== {{ $credit->id }}"
                            class="{{ $accordionPanelClasses }}"
                        >

                            <div class="{{ $accordionBodyClasses }}">

                                @foreach($credit->clauses as $clause)

                                    <h4>
                                        {{  $clause['item'] }}
                                    </h4>

                                    <p>
                                        {{ mos($clause['clause']) }}
                                    </p>

                                @endforeach

                            </div>

                        </div>
                    </div>

                    @endif

                @endforeach
            </div>
        </div>

    </x-section::frame>


    <!-- Klauzule - EUR -->
    @if($bank->hasEUR())

    <x-section::frame
        heading="{{ $bank->label }} - kredyty w euro"
        subheading="Klauzule niedozwolone w kredytach w euro {{ $bank->form_a }}"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
    >

        <div class="md:flex gap-12">

            <div class="flex-1 mb-12 prose">
                {!! $bank->desc_eur !!}
            </div>

            @php($firstPublishedEurCreditId = $bank->credits_eur->firstWhere('is_published', true)?->id)

            <div class="flex-1 space-y-4" x-data="{ openItem: @js($firstPublishedEurCreditId) }">
                @foreach($bank->credits_eur as $credit)

                    @if($credit->is_published)

                    <div class="{{ $accordionCardClasses }}">
                        <button
                            class="{{ $accordionButtonClasses }}"
                            @click="openItem = openItem === {{ $credit->id }} ? null : {{ $credit->id }}"
                            :aria-expanded="openItem === {{ $credit->id }}"
                        >
                            <span class="{{ $accordionTitleClasses }}">
                                Umowa z {{ $credit->credit_year }} r. {{ mos($credit->credit_name !== '_' ? ' - '.$credit->credit_name : '') }}
                            </span>
                            <svg
                                class="{{ $accordionIconClasses }}"
                                :class="{ 'rotate-180': openItem === {{ $credit->id }} }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            x-cloak
                            x-show="openItem === {{ $credit->id }}"
                            x-collapse
                            :aria-hidden="openItem !== {{ $credit->id }}"
                            :inert="openItem !== {{ $credit->id }}"
                            class="{{ $accordionPanelClasses }}"
                        >

                            <div class="{{ $accordionBodyClasses }}">

                                @foreach($credit->clauses as $clause)

                                    <h4>
                                        {{  $clause['item'] }}
                                    </h4>

                                    <p>
                                        {{ mos($clause['clause']) }}
                                    </p>

                                @endforeach

                            </div>

                        </div>
                    </div>

                    @endif

                @endforeach
            </div>
            <div class="flex-1 mb-12">
                {!! $bank->desc_eur !!}
            </div>
        </div>

    </x-section::frame>

    @endif


    <!-- Wyroki -->
    <x-section::frame
        heading="Zobacz wyroki w sprawach prowadzonych przez naszą kancelarię"
        subheading="Kredyty walutowe {{ $bank->form_w }} -  nasze wyroki"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
        :alternate="$bank->hasEUR()"
    >
        <livewire:website.sentences :more="true" :related-bank-id="$bank->id" />

    </x-section::frame>

    <!-- Argumenty -->
    <x-section::frame
        heading="Zobacz, jakie argumenty przemawiają za powierzeniem nam Twojej sprawy"
        subheading="Kredyty frankowe {{ $bank->city }} - dlaczego my?"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
        :alternate="!$bank->hasEUR()"
    >
        <x-section::arguments />

        <x-button.more-link href="{{  route('kancelaria') }}">
            Więcej o kancelarii
        </x-button.more-link>

    </x-section::frame>



    <x-website.element.cta :afterAlternate="!$bank->hasEUR()" />

    <x-theme::footer />

</x-theme::app>
