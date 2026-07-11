<x-theme::app>

    <x-theme::header />

    <!-- Argumenty -->
    <x-section::frame
        :heading="$h1"
        :subheading="$h2"
        :full="true"
        :displaySubheadingFirst="true"
        :useH1="true"
        :alternate="true"
    >

        <div class="max-w-3xl prose prose-slate xl:text-xl">
            {!! $content !!}
        </div>



        <livewire:website.analysis-form :content="$content" />

    </x-section::frame>

    <x-theme::footer />

</x-theme::app>
