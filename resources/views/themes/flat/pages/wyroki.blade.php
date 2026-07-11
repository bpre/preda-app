<x-theme::app>

    <x-theme::header />

    <!-- Wyroki -->
    <x-section::frame
        :heading="$heading"
        :subheading="$subheading"
        :useH1="true"
        :subheadingIsPrimary="true"
        :displaySubheadingFirst="true"
        :full="true"
        :alternate="true"
        :extraMarginTop="true"
    >
        <livewire:website.sentences :more="false" />

    </x-section::frame>

    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
