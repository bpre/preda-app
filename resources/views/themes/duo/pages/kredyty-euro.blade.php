<x-theme::app>

    <x-theme::header />

    <x-section::hero
        :heading="$h1"
        :subheading="$h2"
        :content="$content"
        :useH1="true"
    />

    <x-section::frame
        heading="Czy można unieważnić kredyt w euro?"
        subheading="Unieważnienie kredytu w euro"
        :subheadingIsPrimary="true"
        :displaySubheadingFirst="true"
        image="content/0003.webp"
        figcaption="Unieważnienie kredytu w euro"
    >

        <div class="prose prose-slate">

            <p>
                Kredyt w euro można unieważnić. Większość kredytów powiązanych z euro zawiera te same wady prawne, co kredyty frankowe. Unieważnienie kredytu w euro jest zatem jak najbardziej możliwe.
            </p>
            <p>
                Podstawy do unieważnienia kredytu w euro są takie same, jak w przypadku kredytów frankowych, czyli:
            </p>
            <ul>
                <li>
                    abuzywność postanowień pozwalających bankowi dowolnie ustalać kursy stosowane do przeliczeń, a w konsekwencji - decydować o wysokości świadczeń kredytobiorców;
                </li>
                <li>
                    abuzywność klauzuli ryzyka kursowego.
                </li>
            </ul>
            <p>
                Kredyty w euro zawierają często dokładnie te same postanowienia, co kredyty frankowe. Różnią się jedynie wskazaniem w umowie kredytowej waluty, z którą powiązany jest kredyt (EUR zamiast CHF) oraz wskaźnika, w oparciu o który ustalane jest oprocentowanie (Euribor, a nie Libor). Poza tym kredyty powiązane z kursem euro (zarówno indeksowane, jak i denominowane) nie różnią się od kredytów powiązanych z frankiem szwajcarskim.
            </p>
            <p>
                Skoro są to niemal identyczne umowy, to również w przypadku kredytów w euro istnieją wszelkie podstawy do ich podważenia i dochodzenia przed sądem roszczeń o ustalenie nieważności takich umów. Sądy uwzględniają roszczenia kredytobiorców i ustalają, że umowy kredytowe powiązane z euro są nieważne.
            </p>

        </div>

    </x-section::frame>


    <x-section::frame
        heading="Kredyt w euro i kredyt frankowy - czym różni się sytuacja kredytobiorców?"
        subheading="Kredyty w euro a kredyty frankowe"
        :displaySubheadingFirst="true"
        image="content/0008.webp"
        figcaption="Kredyt w euro można unieważnić podobnie jak kredyt frankowy"
        :alternate="true"
        :right="true"
    >

        <div class="prose prose-slate">

            <p>
                Sytuacja kredytobiorców posiadających kredyty frankowe tym przede wszystkim różni się od sytuacji kredytobiorców posiadających kredyty euro, że w przypadku tych pierwszych doszło do zdecydowanie większego wzrostu kursu waluty obcej. Innymi słowy - ryzyko kursowe zdążyło się zmaterializować, prowadząc do znacznego wzrostu salda zadłużenia kredytobiorców.
            </p>
            <p>
                W przypadku kredytobiorców posiadających kredyty w euro wzrost kursu waluty obcej pomiędzy zawarciem umowy a chwilą obecną nie była aż tak znaczny. Nie ma to jednak większego znaczenia w kontekście podstaw do dochodzenia roszczeń przeciwko bankowi.
            </p>
            <p>
                Postanowienia kształtujące mechanizm indeksacji / denominacji są niedozwolone już tylko z tego względu, że narażają kredytobiorcę na nieograniczone ryzyko kursowe (możliwość nieograniczonego wzrostu zadłużenia kredytobiorcy w przeliczeniu na PLN). Nie ma znaczenia to, czy ryzyko to zdążyło się już zmaterializować, czy też nie. Wystarczający jest już sam fakt narażania kredytobiorcy na to ryzyko.
            </p>

        </div>

    </x-section::frame>


    <x-section::frame
        heading="Unieważnienie kredytu w euro - dlaczego warto?"
        subheading="Kredyty w euro a kredyty frankowe"
        :displaySubheadingFirst="true"
        image="content/0007.webp"
        figcaption="Unieważnienie kredytu w euro niesie ze sobą znaczne korzyści"
        :alternate="false"
    >

        <div class="prose prose-slate">

            <p>
                W przypadku ustalenia, że umowa kredytu powiązanego z euro jest nieważna, nie wywołuje ona żadnych skutków od samego początku. Oznacza to, że zarówno bank, jak i kredytobiorca, powinni sobie zwrócić wszystkie świadczenia spełniane na podstawie nieważnej umowy.
            </p>
            <p>
                Kredytobiorca powinien zwrócić bankowi otrzymany kapitał, a bank powinien zwrócić kredytobiorcy wszystkie zapłacone raty oraz ewentualnie inne opłaty, które na rzecz banku płacił kredytobiorca (prowizje, składki ubezpieczeń, itp.).
            </p>
            <p>
                Finalny rezultat jest zatem taki, że kredytobiorca, nawet jeśli przez kilkanaście lat korzystał z kapitału banku, nie płaci od tego kapitału odsetek.
            </p>

        </div>

    </x-section::frame>


    <x-section::frame
        heading="Unieważnienie kredytu w euro - od czego zacząć?"
        subheading="Unieważnienie kredytu w euro - pierwsze kroki"
        :displaySubheadingFirst="true"
        image="content/0003.webp"
        figcaption="Kredyt w euro można unieważnić podobnie jak kredyt frankowy"
        :alternate="true"
        :right="true"
    >

        <div class="prose prose-slate">

            <p>
                Aby unieważnić kredyt w euro należy w pierwszej kolejności <a href="{{ route('analiza') }}">przesłać umowę kredytową do analizy</a>. Po potwierdzeniu, że umowa zawiera postanowienia niedozwolone konieczne jest ustalenie dodatkowych okoliczności związanych z jej zawieraniem i uzyskanie z banku zaświadczenia o obsłudze kredytu, które pozwala dokładnie ustalić wysokość roszczeń.
            </p>
            <p>
                Kolejnym krokiem jest przedsądowe wezwanie banku do zapłaty. Banki w 100% na takie wezwania odpowiadają odmownie - nie uznają roszczeń kredytobiorców. Konieczne w związku z tym jest złożenie do sądu pozwu, by to sąd rozstrzygnął o tym, że umowa kredytu powiązanego z euro jest nieważna.
            </p>
            <p>
                Nasza kancelaria prowadzi sprawy kredytobiorców posiadających kredyty powiązane z euro, uzyskując korzystne dla kredytobiorców wyroki.
            </p>

        </div>

    </x-section::frame>



    <!-- Argumenty -->
    <x-section::frame
        heading="Zobacz, jakie argumenty przemawiają za powierzeniem nam Twojej sprawy"
        subheading="Kredyt w euro - dlaczego my?"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
    >
        <x-section::arguments />

        <x-button.more-link href="{{  route('kancelaria') }}">
            Więcej o kancelarii
        </x-button.more-link>

    </x-section::frame>


    <!-- Wyroki -->
    <x-section::frame
        heading="Zobacz wyroki w sprawach prowadzonych przez naszą kancelarię"
        subheading="Kredyt w euro -  nasze wyroki"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        :full="true"
        :alternate="true"
    >
        <livewire:website.sentences :more="true" currency="EUR" more_url="wyroki/kredyty-euro" />

    </x-section::frame>

    <!-- Motywacja -->
    <x-section::frame
        heading="Dlaczego zajmujemy się kredytami w euro?"
        subheading="Kredyty w euro - motywacja"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        image="content/0001.webp"
        figcaption="Banki zarabiały krocie na nieuczciwych kredytach w euro."
    >
        <x-section::motivation currency="EUR" />

    </x-section::frame>

    <!-- Opinie -->
    <x-section::frame
        heading="Kredyt w euro - zobacz, co mówią o nas nasi Klienci"
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
        subheading="Pomoc „eurowiczom”"
        :displaySubheadingFirst="true"
        :subheadingIsPrimary="true"
        image="content/0001.webp"
        figcaption="Kompleksowo poprowadzimy Twoją sprawę kredytu w euro."
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
