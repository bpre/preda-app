<x-theme::app>

    <x-theme::header />

    <x-section::frame
        :heading="$h2"
        :subheading="$h1"
        :displaySubheadingFirst="true"
        :useH1="true"
        :alternate="true"
        :full="true"
    >
        <div class="max-w-3xl prose prose-slate xl:text-xl">
            {!! mos($content) !!}
        </div>

    </x-section::frame>

    <!-- Zespół -->
    <x-section::frame
        heading="Poznaj nasz zespół"
        subheading="Zobacz, kto pracuje na Twój sukces"
        :displaySubheadingFirst="true"
        :full="true"
    >
        <x-section::team />

    </x-section::frame>

    <!-- Argumenty -->
    <x-section::frame
        heading="Zobacz, jakie argumenty przemawiają za powierzeniem nam Twojej sprawy"
        subheading="Dlaczego my?"
        :displaySubheadingFirst="true"
        :alternate="true"
        :full="true"
    >
        <x-section::arguments />
    </x-section::frame>

    <!-- Oddziały -->
    <x-section::frame
        heading="Spotkaj się z nami w jednej z naszych lokalizacji"
        subheading="Kontakt"
        :displaySubheadingFirst="true"
        :full="true"
    >

        <x-partial::headquarters-offices />

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
