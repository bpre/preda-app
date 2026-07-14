<div style="page-break-after: always">

    <h1 style="text-align: center">
        Przetwarzanie danych osobowych - RODO
    </h1>

        <dl class="mt-5">
            <dt>1.</dt>
            <dd>
                Administratorem danych osobowych Klienta jest Bartosz Pręda działający pod firmą „PRĘDA Kancelaria Adwokacka - Adwokat Bartosz Pręda” z siedzibą w Głogowie (67-200), przy ul. Szewskiej 7, NIP: 6922321750, REGON: 020169060.
            </dd>
            <dt>2.</dt>
            <dd>
                Administrator będzie przetwarzać dane osobowe Klienta w celu świadczenia na rzecz Klienta pomocy prawnej, w tym także dane szczególnej kategorii w rozumieniu Rozporządzenia Parlamentu Europejskiego i Rady (UE) 2016/679 z dnia 27 kwietnia 2016 r. w sprawie ochrony osób fizycznych w związku z przetwarzaniem danych osobowych i w sprawie swobodnego przepływu takich danych oraz uchylenia dyrektywy 95/46/WE (dalej: „RODO”), które Administrator chroni także zgodnie z obowiązującą tajemnicą zawodową.
            </dd>
            <dt>3.</dt>
            <dd>
                Administrator będzie przetwarzać następujące dane Klienta: imię i nazwisko, PESEL, adres zamieszkania, adres korespondencyjny, numer telefonu, adres e-mail, a także wszelkie powierzone dane w związku ze świadczeniem przez Administratora na rzecz Klienta pomocy prawnej, w szczególności dane zawarte w przekazywanej przez Klienta dokumentacji kredytowej.
            </dd>
            <dt>4.</dt>
            <dd>
                Podstawą prawną przetwarzania przez Administratora danych osobowych Klienta jest: art. 6 ust. 1 lit. b i c RODO – w zakresie w jakim jest to konieczne do realizacji zawartych umów o świadczenie usług prawnych i wypełnienia obowiązków ciążących na Kancelarii; art. 6 ust. 1 lit. f RODO – w zakresie w jakim wymaga tego uzasadniony interes prawny Kancelarii, w tym także w zakresie dochodzenia roszczeń wynikających z umów o świadczenie usług prawnych, art. 9 ust. 2 lit. f RODO – w zakresie w jakim przetwarzanie danych jest niezbędne do ustalenia lub dochodzenia roszczeń Klienta w ramach świadczonej pomocy prawnej.
            </dd>
            <dt>5.</dt>
            <dd>
                Odbiorcami danych osobowych są: podmioty świadczące na rzecz Administratora usługi prawne, usługi w zakresie sporządzania analiz ekonomicznych, usługi księgowo-kadrowe, usługi IT, usługi administracyjne, usługi marketingowe, banki, instytucje pożyczkowe/kredytowe, z którymi Klient zawarł umowę o kredyt/pożyczkę, organy państwowe, operatorzy pocztowi oraz firmy kurierskie.
            </dd>
            <dt>6.</dt>
            <dd>
                Dane osobowe Klienta będą przechowywane do momentu zakończenia świadczenia usług na rzecz Klienta, a także przez okres przedawnienia roszczeń mogących wyniknąć z tytułu świadczenia usług prawnych przez Administratora na rzecz Klienta.
            </dd>
            <dt>7.</dt>
            <dd>
                Klientowi przysługuje prawo do: dostępu do treści danych, sprostowania treści danych, usunięcia treści danych, ograniczenia przetwarzania danych, wniesienia sprzeciwu wobec przetwarzania danych, przenoszenia danych osobowych, cofnięcia zgody na przetwarzania (w zakresie w jakim przetwarzanie odbywa się na podstawie), wniesienia skargi do organu nadzorczego – Prezesa Urzędu Ochrony Danych Osobowych (Urząd Ochrony Danych Osobowych, ul. Stawki 2, 00-193 Warszawa, kancelaria@uodo.gov.pl).
            </dd>
            <dt>8.</dt>
            <dd>
                Podanie danych jest dobrowolne, lecz konieczne w celu skorzystania z usług świadczonych przez Administratora.
            </dd>
            <dt>9.</dt>
            <dd>
                W celu realizacji uprawnień związanych z danymi osobowymi Klient może skontaktować się z Administratorem za pośrednictwem poczty elektronicznej, wysyłając wiadomość na adres: kancelaria@preda.info.
            </dd>
        </dl>
        <p style="margin: 70px 0; font-style: italic">
            Potwierdzam otrzymanie niniejszego dokumentu w dniu {{ bp_human_date($e->date, 'dot') }} r.:
        </p>





    @if($kredytobiorcy)

        @foreach($kredytobiorcy->sortBy('sort') as $klient)
            <div style="width: 40%; margin: 0 25px; float: left; border-top: 1px solid #000;" class="bold">
                <small>{{ $klient->label }}</small>
            </div>
            @if($loop->iteration % 2 == 0)<br><br><br><br>@endif
        @endforeach

    @endif


</div>
