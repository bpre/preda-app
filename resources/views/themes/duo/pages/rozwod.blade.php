<x-theme::app>

    <x-theme::header />

    <x-section::hero
        :heading="$h1"
        :subheading="$h2"
        :content="$content"
        :useH1="true"
    />

    <x-section::frame
        heading="Jak możemy pomóc?"
        subheading="Zakres spraw"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
    >
        <div class="max-w-3xl prose prose-slate xl:text-xl">
            <p>
                Prowadzimy sprawy o rozwód, separację, alimenty, kontakty z dziećmi, władzę rodzicielską oraz podział majątku po ustaniu małżeństwa.
            </p>
            <p>
                Pomagamy uporządkować sytuację procesową, zebrać argumenty i przygotować strategię działania dopasowaną do konkretnej sprawy.
            </p>
        </div>
    </x-section::frame>

</x-theme::app>
