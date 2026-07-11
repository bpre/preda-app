<x-theme::app>

    <x-theme::header />

    <x-section::frame
        :heading="$h1"
        :subheading="$h2"
        :useH1="true"
        :alternate="true"
        :full="true"
    >

    </x-section::frame>

    <x-section::frame
        :full="true"
        class="pt-12"
    >


        <div class="prose prose-slate">
            {!! mos($content) !!}
        </div>

    </x-section::frame>

    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
