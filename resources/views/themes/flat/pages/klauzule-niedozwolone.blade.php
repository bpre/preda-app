<x-theme::app>

    <x-section::frame
        :heading="$h1"
        :subheading="$h2"
        :displaySubheadingFirst="true"
        :useH1="true"
        :alternate="true"
        :full="true"
        :extraMarginTop="true"
    >
        <div class="max-w-3xl prose prose-slate xl:text-xl">
            {!! $content !!}
        </div>

    </x-section::frame>


    <div class="container relative content-center mx-auto overflow-hidden isolate py-12 lg:py-24">

        <livewire:website.banks />

    </div>

    <x-theme::header />
        <x-website.element.cta />
    <x-theme::footer />

</x-theme::app>
