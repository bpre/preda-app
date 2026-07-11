
<div  style="page-break-after: always;">

    <p style="margin-bottom: 10px; margin-top: 0; text-align: right">{{  $miejsce_podpisania }}, {{ bp_human_date($e->date, 'dot') }} r.</p>


    <div class="mt-5" style="margin-left: 0px">
        @if($kredytobiorcy)

            <div class="bold">{{ $pozyczka ? 'Pożyczk' : 'Kredyt' }}obiorc{{ $kredytobiorcy->count() === 1 ? 'a' : 'y' }}</div>
            @php
                $sortedKredytobiorcy = $kredytobiorcy->sortBy('sort');
                $renderKredytobiorcyInline = $sortedKredytobiorcy->count() > 3;
            @endphp
            @foreach($sortedKredytobiorcy as $klient)
                {{ $klient->label }}, PESEL: {{ $klient->pesel  }}{!! $renderKredytobiorcyInline ? ($loop->last ? '' : '; ') : '<br>' !!}
            @endforeach

        @else
            <span class="text-red-500">BRAK MOCODAWCÓW!</span>
        @endif

        @if($e->credits)
            <div class="mt-5 bold">
                @if($pozyczka)
                    {{ $e->credits->count() > 1 ? 'Umowy pożyczki' : 'Umowa pożyczki' }}
                @else
                    {{ $e->credits->count() > 1 ? 'Umowy kredytowe' : 'Umowa kredytowa' }}
                @endif
            </div>
            @foreach($e->credits as $umowa)
                Nr: {{ $umowa->number }} z dnia {{ bp_human_date($umowa->date, 'dot') }} r. zawarta z: {{ $umowa->former_banks->organization }}<br>
            @endforeach

        @else
            <span class="text-red-500">BRAK UMÓW!</span>
        @endif
    </div>

    <h1 style="text-align: center">
        Oświadczenie o świadomości skutków nieważności umowy
    </h1>

        <p>Oświadczam, że w pełni rozumiem i akceptuję wszelkie konsekwencje nieważności wskazanej wyżej {{ $pozyczka ? 'umowy pożyczki' : 'umowy kredytowej' }} (dalej: „Umowa {{ $pozyczka ? 'pożyczki' : 'kredytowa' }}”, przy czym przez pojęcie to należy rozumieć także ewentualne aneksy do umowy).	W szczególności mam świadomość, że:</p>

        <dl class="mt-5">
            <dt>1.</dt>
            <dd>
                w {{  $pozyczka ? 'Umowie pożyczki' : 'Umowie kredytowej' }} znajdują się klauzule abuzywne w rozumieniu art. 385<sup>1</sup> § 1 k.c., bez których {{  $pozyczka ? 'Umowa pożyczki' : 'Umowa kredytowa' }} nie mogłaby być wykonywana, co pociąga za sobą jej nieważność. Oznacza to, że {{  $pozyczka ? 'Umowa pożyczki' : 'Umowa kredytowa' }} traktowana powinna być tak, jakby nigdy nie została zawarta i nie wywołuje skutków od samego początku;
            </dd>
            <dt>2.</dt>
            <dd>
                skutkiem nieważności {{ $pozyczka ? 'Umowy pożyczki' : 'Umowy kredytowej' }} jest to, że strony tej umowy mają obowiązek zwrócić sobie to, co na podstawie umowy świadczyły;
            </dd>
            <dt>3.</dt>
            <dd>
                bank ma obowiązek zwrotu zapłaconych rat oraz innych opłat wynikających z {{ $pozyczka ? 'Umowy pożyczki' : 'Umowy kredytowej' }}, a ja – jako {{ $pozyczka ? 'pożyczkobiorca' : 'kredytobiorca' }} – mam obowiązek zwrotu na rzecz banku udostępnionego mi kapitału {{ $pozyczka ? 'pożyczki' : 'kredytu' }};
            </dd>
            <dt>4.</dt>
            <dd>
                rozliczenie stron nieważnej {{ $pozyczka ? 'Umowy pożyczki' : 'Umowy kredytowej' }} może nastąpić poprzez potrącenie wierzytelności i rozliczenie różnicy;
            </dd>
            <dt>5.</dt>
            <dd>
                zgodnie z aktualnym stanowiskiem Sądu Najwyższego bank może żądać zwrotu swojego świadczenia od momentu, w którym {{ $pozyczka ? 'pożyczkobiorca' : 'kredytobiorca' }} zakwestionował względem banku związanie postanowieniami umowy;
            </dd>
            <dt>6.</dt>
            <dd>
                bank może wystąpić przeciwko mnie o zwrot całego wypłaconego kapitału {{ $pozyczka ? 'pożyczki' : 'kredytu' }};
            </dd>
            <dt>7.</dt>
            <dd>
                bank może wystąpić przeciwko mnie z roszczeniem przekraczającym kwotę udostępnionego mi kapitału, w szczególności z roszczeniem o tzw. „wynagrodzenie za korzystanie z kapitału”, z roszczeniem o waloryzację kapitału, itp.;
            </dd>
            <dt>8.</dt>
            <dd>
                Trybunał Sprawiedliwości Unii Europejskiej w wyroku z 15.06.2023 r., wydanym w sprawie C-520/21, orzekł iż żądanie przez instytucję kredytową od konsumenta rekompensaty wykraczającej poza zwrot kapitału wypłaconego z tytułu wykonania tej umowy oraz poza zapłatę ustawowych odsetek za zwłokę od dnia wezwania do zapłaty, jest sprzeczne z przepisami dyrektywy 93/13;
            </dd>
            <dt>9.</dt>
            <dd>
                mogę zapobiec nieważności {{ $pozyczka ? 'Umowy pożyczki' : 'Umowy kredytowej' }} i jej skutkom poprzez wyrażenie zgody na stosowanie zawartych w niej niedozwolonych postanowień już od momentu zawarcia umowy (w takim przypadku {{  $pozyczka ? 'Umowa pożyczki' : 'Umowa kredytowa' }} wiązałaby mnie nadal, zgodnie z jej pierwotnym brzmieniem).
            </dd>
        </dl>
        <p style="margin-bottom: 70px">
            Mając świadomość wszystkich wskazanych wyżej kwestii, stanowczo odmawiam potwierdzenia wszelkich klauzul niedozwolonych zawartych w {{  $pozyczka ? 'Umowie pożyczki' : 'Umowie kredytowej' }}. Oświadczam, że nie godzę się na ich stosowanie i związanie Umową {{ $pozyczka ? 'pożyczki' : 'kredytową' }}. Akceptuję wszystkie skutki nieważności umowy. Moją wolą jest, by Sąd ustalił, że {{  $pozyczka ? 'Umowa pożyczki' : 'Umowa kredytowa' }} jest nieważna.
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
