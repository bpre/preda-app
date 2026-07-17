<x-theme::app>

    <x-theme::header />

    <x-section::frame
        heading="Orzecznictwo w sprawach kredytów powiązanych z walutami obcymi"
        subheading="Kredyty frankowe i kredyty w euro - orzecznictwo"
        :useH1="true"
        :subheadingIsPrimary="true"
        :displaySubheadingFirst="true"
        :full="true"
        :alternate="true"
    >

    </x-section::frame>

    <x-section::frame
        :displaySubheadingFirst="true"
        :full="true"
        class="pb-12 xl:pb-24"
    >

        <livewire:website.posts />

    </x-section::frame>

    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
