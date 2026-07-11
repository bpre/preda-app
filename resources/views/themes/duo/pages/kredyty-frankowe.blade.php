<x-theme::app>

    <x-theme::header />

    <x-section::hero
        :heading="$h1"
        :subheading="$h2"
        :content="$content"
        :useH1="true"
    />

    <x-section::frame
        heading="Unieważnienie kredytu frankowego"
        subheading="Kredyty frankowe - unieważnienie umowy"
        image="content/0003.webp"
        figcaption="Unieważnienie kredytu frankowego"
        :displaySubheadingFirst="true"
    >

        <div class="prose prose-slate">
            <p>
                Praktycznie wszystkie umowy kredytów powiązanych z frankiem szwajcarskim (CHF) zawierają postanowienia, które pozwalają na ich unieważnienie (a ściślej: ustalenie, że umowa kredytowa jest nieważna od samego początku).
            </p>
            <p>
                Jeżeli masz kredyt powiązany z frankiem szwajcarskim z lat 2001-2012, który został wypłacony w złotych polskich - to jest wysoce prawdopodobne, że Twoja umowa zawiera postanowienia niedozwolone pozwalające na jej unieważnienie.
            </p>
            <p>
                Możesz samodzielnie sprawdzić, czy Twoja umowa zawiera <a href="{{ route('klauzule-niedozwolone') }}">postanowienia niedozwolone</a>, korzystając naszej <a href="{{ route('klauzule-niedozwolone') }}">bazy klauzul niedozwolonych</a>.
            </p>
            <p>
                Możesz również przesłać nam swoją umowę do <a href="{{ route('analiza') }}">bezpłatnej analizy</a>. Dokładnie sprawdzimy postanowienia Twojej umowy i poinformujemy Cię, czy możliwe jest unieważnienie Twojego kredytu frankowego.
            </p>
            <p>
                Pamiętaj, że unieważnienie kredytu frankowego to najczęściej korzyść rzędu setek tysięcy złotych.
            </p>
        </div>

    </x-section::frame>


    <x-section::frame
        heading="Czym skutkuje unieważnienie kredytu frankowego?"
        subheading="Kredyt frankowy - unieważnienie"
        :displaySubheadingFirst="true"
        image="content/0007.webp"
        figcaption="Jakie są skutki unieważnienia kredytu frankowego?"
        :alternate="true"
        :right="true"
    >

        <div class="prose prose-slate">
            <p>
                Jeżeli umowa jest nieważna, to nie wywołuje skutków od samego początku. Sytuacja wygląda zatem tak, jak gdyby strony nigdy nie zawarły umowy kredytowej.</p>
            <p>
                Ponieważ jednak na podstawie umowy jej strony spełniały świadczenia (bank wypłacił kredyt, kredytobiorca spłacał raty i ponosił inne opłaty) - wszystkie te świadczenia strony powinny sobie zwrócić, jako świadczenia nienależne. Kredytobiorca powinien zatem zwrócić bankowi otrzymany od banku kapitał, a bank powinien zwrócić kredytobiorcy wszystko to, co kredytobiorca zapłacił bankowi (raty, prowizję, dodatkowe opłaty, składki ubezpieczeń, itp.).
            </p>

            <p>
                Zwrot świadczeń nienależnych po ustaleniu nieważności umowy kredytowej oznacza <i>de facto</i> rozliczenie się z bankiem wyłącznie z otrzymanego od banku kapitału w kwocie nominalnej - bez żadnych dodatkowych opłat, w szczególności odsetek.
            </p>
        </div>

    </x-section::frame>


    <x-section::frame
        heading="Unieważnienie kredytu frankowego - ile możesz zyskać?"
        subheading="Kredyty frankowe - korzyści z unieważnienia umowy"
        :displaySubheadingFirst="true"
        image="content/0008.webp"
        figcaption="Ile możesz zyskać na unieważnieniu kredytu frankowego?"
    >

        <div class="prose prose-slate">
            <p>
                To, ile możesz zyskać unieważniając kredyt frankowy zależy od wielu czynników, m.in. od kwoty kredytu i kursu PLN/CHF w chwili uruchomienia kredytu, okresu kredytowania, aktualnego kursu PLN/CHF, okresu karencji, spreadów stosowanych przez konkretny bank, ewentualnych wcześniejszych spłat i wielu innych.</p>

            <p>
                W każdym razie, w przypadku ustalenia nieważności umowy, korzyści kredytobiorcy są bardzo konkretne - najczęściej rzędu setek tysięcy złotych. Przy kredycie udzielonym na 30 lat co do zasady przewyższają kwotę udostępnionego kredytobiorcy przez bank kapitału. Przykładowo - jeśli bank udzielił Ci kredytu indeksowanego lub denominowanego w CHF na kwotę 150.000 zł na okres 30 lat, to prawdopodobnie Twoja korzyść z unieważnienia kredytu przekroczy 150.000 zł.
            </p>

            <p>
                W prowadzonych przez nas sprawach zakończonych wyrokami średnia korzyść kredytobiorcy wynosi ok. 220.000 PLN.
            </p>

            <p>
                Wysokość korzyści w Twojej sprawie jesteśmy w stanie dokładnie określić dysponując zaświadczeniem banku, natomiast szacunkowo - dysponując treścią Twojej umowy kredytowej i kilkoma dodatkowymi informacjami.
            </p>
        </div>

    </x-section::frame>


    <!-- Wyroki -->
    <x-section::frame
        heading="Zobacz wyroki w sprawach prowadzonych przez naszą kancelarię"
        subheading="Kredyty frankowe -  nasze wyroki"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
        :alternate="true"
    >
        <livewire:website.sentences :more="true" currency="CHF" more_url="wyroki/kredyty-frankowe" />

    </x-section::frame>

    <!-- Argumenty -->
    <x-section::frame
        heading="Zobacz, jakie argumenty przemawiają za powierzeniem nam Twojej sprawy"
        subheading="Kredyty frankowe - dlaczego my?"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
    >
        <x-section::arguments />

        <x-button.more-link href="{{  route('kancelaria') }}">
            Więcej o kancelarii
        </x-button.more-link>

    </x-section::frame>



    <!-- Motywacja -->
    <x-section::frame
        heading="Dlaczego zajmujemy się kredytami frankowymi?"
        subheading="Kredyty frankowe - motywacja"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        image="content/0001.webp"
        figcaption="Banki zarabiały krocie na nieuczciwych „kredytach frankowych”."
        :alternate="true"
    >
        <x-section::motivation />

    </x-section::frame>

    <!-- Opinie -->
    <x-section::frame
        heading="Kredyty frankowe - zobacz, co mówią o nas nasi Klienci"
        subheading="Opinie naszych klientów"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
    >
        <x-section::reviews :more="true" />

        <x-button.more-link href="{{  route('opinie') }}">
            Zobacz więcej opinii
        </x-button.more-link>

    </x-section::frame>

    <!-- W czym możemy pomóc? -->
    <x-section::frame
        heading="W czym możemy Ci pomóc?"
        subheading="Pomoc „frankowiczom"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        image="content/0001.webp"
        figcaption="Kompleksowo poprowadzimy Twoją sprawę kredytu frankowego."
        :alternate="true"
    >
        <x-section::how-can-we-help />
    </x-section::frame>

        <!-- Oddziały -->
    <x-section::frame
        heading="Spotkaj się z nami w jednej z naszych lokalizacji"
        subheading="Kontakt"
        :displaySubheadingFirst="true"
        :full="true"
    >

        <x-partial::headquarters-offices />

    </x-section::frame>

    <x-website.element.cta :afterAlternate="true" />
    <x-theme::footer />

</x-theme::app>
