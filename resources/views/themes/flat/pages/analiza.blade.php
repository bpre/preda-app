<x-theme::app>

    <x-theme::header />

    <!-- Argumenty -->
    <x-section::frame
        :heading="$h1"
        :subheading="$h2"
        :full="true"
        :alternate="true"
        :displaySubheadingFirst="true"
        :useH1="true"
        :extraMarginTop="true"
    >

        <div class="max-w-3xl prose prose-slate xl:text-xl">
            {!! $content !!}
        </div>

    </x-section::frame>

    <x-section::frame
        :full="true"
    >

    <livewire:website.analysis-form :content="$content" />

    </x-section::frame>

    <x-theme::footer />

</x-theme::app>
