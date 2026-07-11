<x-theme::app>

    <x-theme::header />

    <x-section::frame
        :heading="$h1"
        :subheading="$h2"
        :useH1="true"
        :displaySubheadingFirst="true"
        :full="true"
        :alternate="true"
        :extraMarginTop="true"
    >

    </x-section::frame>

    <x-section::frame
        :full="true"
    >
        <div class="py-12 max-w-3xl">
            <x-section::faq prefix="homepage" />
        </div>


    </x-section::frame>

    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
