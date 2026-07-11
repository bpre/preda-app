<x-theme::app>

    <x-theme::header />

    <x-section::hero
        :heading="$h1"
        :subheading="$h2"
        :content="$content"
        :useH1="true"
        :alternate="true"
        :full="true"
        :extraMarginTop="true"
    >

    </x-section::hero>


    <!-- Czy nie jest za późno -->
    <x-section::frame
        heading="Myślisz, że jest za późno?|Banki właśnie na to liczą!"
        subheading="Spłacony kredyt frankowy - czy możesz jeszcze pozwać bank?"
        :displaySubheadingFirst="true"
        :full="true"
    >

    <div class="prose">
        <p>
            Wielu byłych Frankowiczów żyje w przekonaniu, że skoro kredyt został spłacony, a hipoteka wykreślona, temat jest zamknięty. To nieprawda.
        </p>
        <p>
            Orzecznictwo TSUE i Sądu Najwyższego stoi po stronie konsumenta. Termin przedawnienia nie biegnie od momentu podpisania umowy czy spłaty ostatniej raty, ale najczęściej od momentu, w którym dowiedziałeś się o wadliwości umowy.
        </p>
        <p>
            Co to oznacza dla Ciebie? Nawet jeśli spłaciłeś kredyt 2, 5 czy 8 lat temu – wciąż masz szansę na pozew i wygraną.
        </p>
    </div>


    </x-section::frame>


    <!-- Korzyści -->
    <x-section::frame
        heading="Przy kredycie spłaconym, wygrana oznacza realny zwrot środków na Twoje konto!"
        subheading="Spłacony kredyt frankowy - korzyści"
        :displaySubheadingFirst="true"
        alternate="true"
        :full="true"
    >

    <div class="prose">
        <p>
            Klienci z "aktywnymi" kredytami walczą głównie o zmniejszenie zadłużenia. Twoja sytuacja jest bardziej komfortowa. Ty walczysz o zwrot znacznych środków pieniążnych na Twoje konto.
        </p>
        <p>
            Wygrywając proces o unieważnienie umowy spłaconego kredytu, zyskujesz:
        </p>

        <p>
            <strong>Zwrot nadpłaty:</strong> Różnica między tym, co wpłaciłeś do banku, a kwotą, którą pożyczyłeś (często są to kwoty rzędu 100-300 tys. zł).
        </p>
        <p>
            <strong>Ustawowe odsetki za opóźnienie:</strong> To dodatkowe, ogromne korzyści finansowe liczone od momentu wezwania banku do zapłaty (obecnie to aż 11,25% w skali roku!).
        </p>
        <p>
            <strong>Spokój</strong>: Ostateczne rozliczenie z nieuczciwym produktem finansowym.
        </p>

    </div>

    </x-section::frame>



    <!-- Brak dokumentów -->
    <x-section::frame
        heading="Nie dysponujesz już dokumentami?|Poradzimy sobie z tym!"
        subheading="Spłacony kredyt frankowy - pomożemy Ci uzyskać potrzebne dokumenty"
        :displaySubheadingFirst="true"
        :full="true"
    >

    <div class="prose">
        <p>
            Obawiasz się, że brak dokumentów blokuje Ci drogę do sądu? Nie musisz się martwić!
        </p>
        <p>
            Przygotujemy niezbędne pisma o wydanie pełnej historii spłat oraz kopii umowy kredytowej.
        </p>
        <p>
            Bank ma prawny obowiązek wydać Ci dokumenty związane z Twoim kredytem - nawet jeśli kredyt został spłacony wiele lat temu.
        </p>
        <p>
            Jeżeli bank nie będzie chciał wydać dokumentów - przygotujemy w Twoim imieniu wniosek o interwencję do Rzecznika Finansowego.
        </p>
    </div>

    </x-section::frame>


    <!-- Wyroki -->
    <x-section::frame
        heading="Zobacz wyroki w sprawach spłaconych kredytów frankowych"
        subheading="Spłacone kredyty frankowe - wyroki naszej kancelarii"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
        :alternate="true"
    >
        <livewire:website.sentences :more="true" more_url="wyroki/splacone" :is_paid_off="true" />

    </x-section::frame>


    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
