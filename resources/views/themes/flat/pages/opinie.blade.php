<x-theme::app>

    <x-theme::header />

    <x-section::frame
        :heading="$h2"
        :subheading="$h1"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :useH1="true"
        :alternate="true"
        :full="true"
        :extraMarginTop="true"
    >
        <x-section::reviews />

    </x-section::frame>

    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
