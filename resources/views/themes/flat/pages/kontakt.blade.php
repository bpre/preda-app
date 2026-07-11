<x-theme::app>

    <x-theme::header />

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
            {!! mos($content) !!}
        </div>

    </x-section::frame>


    <div class="container relative content-center mx-auto overflow-hidden isolate py-12 lg:py-24">

        <x-partial::headquarters-offices />

    </div>

    <x-section::frame
        heading="Chcesz powierzyć nam sprawę swojego kredytu frankowego lub kredytu w euro?"
        subheading="Współpraca"
        :displaySubheadingFirst="true"
        :alternate="true"
        :full="true"
    >

        <div class="prose prose-slate">

            <p>
                Prześlij nam do bezpłatnej analizy swoją umowę kredytową. Możesz ją wysłać na adres: <a href="mailto:kancelaria@preda.info">kancelaria@preda.info</a> lub skorzystać ze specjalnie do tego przygotowanego <a href="{{  route('analiza') }}">formularza</a>.
            </p>
            <p>
                Kopię umowy kredytowej możesz również dostarczyć do siedziby kancelarii, która mieści się w Głogowie przy ul. Szewskiej 7 (Stare Miasto). Sekretariat kancelarii czynny jest od poniedziałku do piątku od godz. 8:00 do 16:00.
            </p>
            @if($offices->isNotEmpty())
                <p>
                    Po wcześniejszym ustaleniu terminu możesz się z nami spotkać także w jednym z oddziałów naszej kancelarii - @foreach($offices as $office){!!  $office->form_w !!}{{ $loop->last ? '.' : ($loop->iteration+1 == $loop->count ? ' lub ' : ', ') }}@endforeach
                </p>
            @endif
            <p>
                Po przeanalizowaniu Twojej umowy skontaktujemy się z Tobą i umówimy się na spotkanie w dogodnej dla Ciebie formie (osobiście - w siedzibie kancelarii lub jednym z oddziałów albo poprzez wideokonferencję).
            </p>

        </div>

    </x-section::frame>

    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
