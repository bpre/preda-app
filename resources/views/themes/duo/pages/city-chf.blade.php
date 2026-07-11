<x-theme::app>

    <x-theme::header />

    <x-section::hero
        :heading="$h1"
        :subheading="$h2"
        :content="$content"
        :useH1="true"
    />

    <x-section::frame
        :subheadingIsPrimary="true"
        :displaySubheadingFirst="true"
        :alternate="$office !== null"
        :full="true"
    >

        <div class="prose prose-slate pt-12 pb-24">

            <p>Wygrywamy sprawy frankowe reprezentując klientów {!! mos($city->form_z) !!}. Osoby mające kredyty frankowe i mieszkające {!! mos($city->form_w) !!} coraz częściej zgłaszają się do nas po pomoc prawną. Najczęściej trafiają do nas z poleceń naszych wcześniejszych klientów, którzy również mieli kredyt frankowy i którzy z pomocą naszej kancelarii wygrali z bankiem.</p>

            <p>Korzystając z z naszego doświadczenia i eksperckiej wiedzy, skutecznie prowadzimy sprawy kredytobiorców {!! mos($city->form_z) !!} - od analizy umowy kredytu frankowego, przez proces sądowy, aż do rozliczenia nieważnej umowy kredytu frankowego i wykreślenia hipoteki. Korzystne <a href="{{  route('wyroki') }}">wyroki w sprawach frankowych</a>, które uzyskujemy, potwierdzają naszą skuteczność.</p>

            <p>Nasi klienci {!! mos($city->form_z) !!} cenią nas także za przejrzyste zasady rozliczeń oraz doskonały kontakt przez cały czas trwania sprawy frankowej.</p>

            <p>Sprawy frankowe kredytobiorców mieszkających {!! mos($city->form_w) !!} i okolicach najczęściej toczą się przed Sądem Okręgowym {{ $city->so }}. Sądem drugiej instancji jest w takim wypadku Sąd Apelacyjny {!! mos($city->sa) !!}.</p>

            <p>Jeśli mieszkasz {!! mos($city->form_w) !!} lub okolicach i masz kredyt frankowy - skontaktuj się z nami. Tobie również możemy pomóc wygrać z bankiem sprawę o kredyt frankowy i odzyskać nawet kilkaset tysięcy złotych.</p>

        </div>

    </x-section::frame>


    @if($office)
        <!-- Oddział -->
        <x-section::frame
            heading="Spotkaj się z nami w oddziale naszej kancelarii {{ $city->form_w }}"
            subheading="PRĘDA Kancelaria Adwokacka - oddział {{ $city->form_w }}"
            :displaySubheadingFirst="true"
            image="team/{{ image_from_email($office->director->email) }}"
            imageClass="rounded-lg lg:w-2/3 aspect-[3/4]"
            figcaption="Oddziałem kancelarii {{ $city->form_w }} kieruje {{ $office->director->website_title . ' ' . $office->director->name }}"
        >

            <x-section::office :office="$office" />

        </x-section::frame>
    @endif


    <!-- Wyroki -->
    <x-section::frame
        heading="Zobacz wyroki w sprawach prowadzonych przez naszą kancelarię"
        subheading="Kredyty frankowe {{ $city->city }} -  nasze wyroki"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
        :alternate="true"
    >
        <livewire:website.sentences :more="true" currency="CHF" more_url="wyroki/kredyty-frankowe" />

    </x-section::frame>

    <!-- Argumenty -->
    <x-section::frame
        heading="Zobacz, jakie argumenty przemawiają za powierzeniem nam Twojej sprawy"
        subheading="Kredyty frankowe {{ $city->city }} - dlaczego my?"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
    >
        <x-section::arguments />

        <x-button.more-link href="{{  route('kancelaria') }}">
            Więcej o kancelarii
        </x-button.more-link>

    </x-section::frame>



    <!-- Motywacja -->
    <x-section::frame
        heading="Dlaczego zajmujemy się kredytami frankowymi?"
        subheading="Kredyty frankowe {{  $city->city }} - motywacja"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        image="content/0001.webp"
        figcaption="Banki zarabiały krocie na nieuczciwych „kredytach frankowych”."
        :alternate="true"
    >
        <x-section::motivation />

    </x-section::frame>

    <!-- Opinie -->
    <x-section::frame
        heading="Kredyty frankowe {{ $city->city }} - zobacz, co mówią o nas nasi Klienci"
        subheading="Opinie naszych klientów"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
    >
        <x-section::reviews :more="true" />

        <x-button.more-link href="{{  route('opinie') }}">
            Zobacz więcej opinii
        </x-button.more-link>

    </x-section::frame>

    <!-- W czym możemy pomóc? -->
    <x-section::frame
        heading="W czym możemy Ci pomóc?"
        subheading="Pomoc „frankowiczom” {{ $city->city }}"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        image="content/0001.webp"
        figcaption="Kompleksowo poprowadzimy Twoją sprawę kredytu frankowego."
        :alternate="true"
    >
        <x-section::how-can-we-help />
    </x-section::frame>

    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
