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
        :full="true"
        :alternate="true"
    >

        <div class="prose prose-slate pt-12 pb-24">

            {!! mos('<p>Wygrywamy sprawy kredytów w euro reprezentując klientów '.$city->form_z.'. Osoby mające kredyty w euro i mieszkające '.$city->form_w.' coraz częściej zgłaszają się do nas po pomoc prawną. Najczęściej trafiają do nas z poleceń naszych wcześniejszych klientów '.$city->form_z.', którzy również mieli kredyt w euro i którzy z pomocą naszej kancelarii wygrali z bankiem.</p>') !!}

            {!! mos('<p>Wykorzystujemy nasze doświadczenie i ekspercką wiedzę prowadząc skutecznie sprawy kredytobiorców '.$city->form_z.' - od analizy umowy kredytu w euro, przez proces sądowy, aż do rozliczenia nieważnej umowy kredytu w euro i wykreślenia hipoteki. Korzystne wyroki w sprawach kredytobiorców '.$city->form_z.' potwierdzają naszą skuteczność.</p>') !!}

            {!! mos('<p>Z opinii, które otrzymujemy wynika, że nasi klienci '.$city->form_z.' posiadający kredyty w euro, cenią nas także za przejrzyste zasady rozliczeń oraz doskonały kontakt przez cały czas trwania sprawy.</p>') !!}

            {!! mos('<p>Sprawy kredytobiorców mieszkających '.$city->form_w.' i okolicach najczęściej toczą się przed Sądem Okręgowym '.$city->so.'. Sądem drugiej instancji jest w takim wypadku Sąd Apelacyjny '.$city->sa.'.</p>') !!}

            {!! mos('<p>Jeśli mieszkasz '.$city->form_w.' lub okolicach i masz kredyt w euro - skontaktuj się z nami. Z pewnością Tobie również będziemy mogli pomóc wygrać z bankiem sprawę o kredyt w euro i odzyskać nawet kilkaset tysięcy złotych.</p>') !!}

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

            <x-section::office :office="$office" currency="EUR" />

        </x-section::frame>
    @endif

    <!-- Wyroki -->
    <x-section::frame
        heading="Zobacz wyroki w sprawach prowadzonych przez naszą kancelarię"
        subheading="Kredyt w euro {{ $city->city }} -  nasze wyroki"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
        :alternate="true"
    >
        <livewire:website.sentences :more="true" currency="EUR" more_url="wyroki/kredyty-euro" />

    </x-section::frame>

    <!-- Argumenty -->
    <x-section::frame
        heading="Zobacz, jakie argumenty przemawiają za powierzeniem nam Twojej sprawy"
        subheading="Kredyt w euro {{ $city->city }} - dlaczego my?"
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
        heading="Dlaczego zajmujemy się kredytami w euro?"
        subheading="Kredyty w euro {{  $city->city }} - motywacja"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        image="content/0001.webp"
        figcaption="Banki zarabiały krocie na nieuczciwych powiązanych z euro."
        :alternate="true"
    >
        <x-section::motivation currency="EUR" />

    </x-section::frame>

    <!-- Opinie -->
    <x-section::frame
        heading="Kredyt w euro {{ $city->city }} - zobacz, co mówią o nas nasi Klienci"
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
        subheading="Pomoc „eurowiczom” {{ $city->city }}"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        image="content/0001.webp"
        figcaption="Kompleksowo poprowadzimy Twoją sprawę kredytu w euro."
        :alternate="true"
    >
        <x-section::how-can-we-help />
    </x-section::frame>

    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
