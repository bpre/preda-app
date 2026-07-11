<x-theme::app>

    <x-theme::header />

    <x-section::frame
        :heading="$h1"
        :subheading="$h2"
        :useH1="true"
        :alternate="true"
    >
        {{ mos($content) }}
    </x-section::frame>

    @if($office?->director)
        <!-- Oddział -->
        <x-section::frame
            heading="Pomagamy kredytobiorcom {{ $city->form_z }}"
            subheading="Kredyty frankowe i kredyty euro - kancelaria {{ $city->form_w }}"
            :displaySubheadingFirst="true"
            image="team/{{ image_from_email($office->director->email) }}"
            imageClass="rounded-lg lg:w-2/3 aspect-[3/4]"
            figcaption="Oddziałem kancelarii {{ $city->form_w }} kieruje {{ $office->director->website_title . ' ' . $office->director->name }}"
        >

            <x-section::office :office="$office" />

        </x-section::frame>
    @endif

    <!-- Oferta -->
    <x-section::frame
        heading="W czym możemy Ci pomóc?"
        subheading="Pomoc prawna w sprawach kredytów powiązanych z walutami obcymi"
        :displaySubheadingFirst="true"
        :alternate="true"
        :full="true"
    >

        <div class="grid md:flex gap-8">

            <div class="w-full group relative flex gap-x-6 rounded-lg p-4 hover:shadow-md bg-white">
                <div class="mt-1 flex size-11 flex-none items-center justify-center rounded-lg bg-secondary-100 ">

                <x-icon name="heroicon-o-document-text" class="size-6 text-secondary-400 group-hover:text-accent-600" />

                </div>
                <div>
                <a href="{{ url('kredyty-frankowe-' . $city->slug)}}" class="font-semibold text-primary-950 text-xl">
                    Kredyty frankowe
                    <span class="absolute inset-0"></span>
                </a>
                <p class="mt-1 text-secondary-600">

                    Unieważnij kredyt frankowy. Odzyskaj zwrot nadpłaconych rat, nawet jeśli kredyt został już dawno spłacony.

                </p>
                </div>
            </div>

            <div class="w-full group relative flex gap-x-6 rounded-lg p-4 hover:shadow-md bg-white">
                <div class="mt-1 flex size-11 flex-none items-center justify-center rounded-lg bg-secondary-100 ">

                <x-icon name="heroicon-o-currency-euro" class="size-6 text-secondary-400 group-hover:text-accent-600" />

                </div>
                <div>
                <a href="{{ url('kredyt-euro-kancelaria-' . $city->slug)}}" class="font-semibold text-primary-950 text-xl">
                    Kredyty w euro
                    <span class="absolute inset-0"></span>
                </a>
                <p class="mt-1 text-secondary-600">

                    Unieważnij kredyt indeksowany lub denominowany w euro.

                </p>
                </div>
            </div>

        </div>

    </x-section::frame>

    <!-- Argumenty -->
    <x-section::frame
        heading="Zobacz, jakie argumenty przemawiają za powierzeniem nam Twojej sprawy"
        subheading="Dlaczego my?"
        :displaySubheadingFirst="true"
        :full="true"
    >
        <x-section::arguments />
    </x-section::frame>


    <!-- Wyroki -->
    <x-section::frame
        heading="Zobacz wyroki w sprawach prowadzonych przez naszą kancelarię"
        subheading="Wygrywamy z bankami"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
        :alternate="true"
    >
        <livewire:website.sentences :more="true" />

    </x-section::frame>

    <!-- Motywacja -->
    <x-section::frame
        heading="Dlaczego zajmujemy się kredytami powiązanymi z walutami obcymi?"
        subheading="Motywacja"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        image="content/0001.webp"
        figcaption="Banki zarabiały krocie na nieuczciwych „kredytach frankowych”."
    >
        <x-section::motivation />

    </x-section::frame>

    <!-- Opinie -->
    <x-section::frame
        heading="Zobacz, co mówią o nas nasi Klienci"
        subheading="Opinie"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
        :alternate="true"
    >
        <x-section::reviews :alternate="true" :more="true" />

        <x-button.more-link href="{{  route('opinie') }}">
            Zobacz więcej opinii
        </x-button.more-link>

    </x-section::frame>

    <!-- W czym możemy pomóc? -->
    <x-section::frame
        heading="W czym możemy Ci pomóc?"
        subheading="Kredyty frankowe i kredyty w euro"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        image="content/0003.webp"
        figcaption="Kompleksowo poprowadzimy Twoją sprawę kredytu powiązanego z walutą obcą."
    >
        <x-section::how-can-we-help />
    </x-section::frame>

    <x-website.element.cta />
    <x-theme::footer />

</x-theme::app>
