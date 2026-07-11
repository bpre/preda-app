<x-theme::app>

    <x-theme::header />

    <x-section::hero
        :heading="$h1"
        :subheading="$h2"
        :subheadingIsPrimary="false"
        :content="$content"
        :useH1="true"
    />

    <!-- Argumenty -->
    <x-section::frame
        heading="Zobacz, jakie argumenty przemawiają za powierzeniem nam Twojej sprawy"
        subheading="Dlaczego my?"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
    >
        <x-section::arguments />

        <x-button.more-link href="{{  route('kancelaria') }}">
            Więcej o kancelarii
        </x-button.more-link>

    </x-section::frame>

    <!-- Wyroki -->
    <x-section::frame
        heading="Zobacz wyroki w sprawach prowadzonych przez naszą kancelarię"
        subheading="Wyroki"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
        :alternate="true"
    >
        <livewire:website.sentences :more="true" dark />

    </x-section::frame>

    <!-- Opinie -->
    <x-section::frame
        heading="Zobacz, co mówią o nas nasi Klienci"
        subheading="Opinie"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
    >
        <x-section::reviews :more="true" />

        <x-button.more-link href="{{  route('opinie') }}">
            Zobacz więcej opinii
        </x-button.more-link>

    </x-section::frame>

    <!-- Banki -->
    <x-section::frame
        heading="Sprawdź, umowy których banków dotychczas unieważniliśmy"
        subheading="Z którymi bankami wygrywamy?"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
        :alternate="true"
    >
        <livewire:website.banks />
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

    <!-- FAQ -->
    <x-section::frame
        heading="Częste pytania dot. kredytów frankowych i kredytów w euro"
        subheading="FAQ"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        image="content/0002.webp"
        figcaption="Kredyty frankowe i kredyty w euro - zobacz odpowiedzi na częste pytania"
        :alternate="true"
    >
        <x-section::faq prefix="homepage" />
    </x-section::frame>

    <!-- CTA - analiza -->
    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
