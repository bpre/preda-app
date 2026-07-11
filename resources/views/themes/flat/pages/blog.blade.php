<x-theme::app>

    <x-theme::header />

    <x-section::frame
        :heading="$h1"
        :subheading="$h2"
        :useH1="true"
        :displaySubheadingFirst="false"
        :full="true"
        :alternate="true"
        :extraMarginTop="true"
    >

    </x-section::frame>

    <x-section::frame
        :displaySubheadingFirst="true"
        :full="true"
    >

        <livewire:website.posts />

    </x-section::frame>

    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
