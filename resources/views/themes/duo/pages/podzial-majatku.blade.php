<x-theme::app>

    <x-theme::header />

    <x-section::hero
        :heading="$h1"
        :subheading="$h2"
        :content="$content"
        :useH1="true"
    />

    <x-section::frame
        heading="W czym możemy pomóc?"
        subheading="Podział majątku"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
    >
        <div class="max-w-3xl prose prose-slate xl:text-xl">
            <p>
                Pomagamy w sprawach dotyczących podziału majątku wspólnego, w tym nieruchomości, kredytów, oszczędności, udziałów w firmach oraz rozliczeń nakładów.
            </p>
            <p>
                Przygotowujemy strategię postępowania, analizujemy dokumenty i reprezentujemy klientów w negocjacjach oraz w postępowaniu sądowym.
            </p>
        </div>
    </x-section::frame>

</x-theme::app>
