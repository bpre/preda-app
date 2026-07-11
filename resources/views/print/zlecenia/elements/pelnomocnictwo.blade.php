<div style="page-break-after: always">

    <h1 style="text-align: center; margin-bottom: 0">Pełnomocnictwo</h1>

    <p style="margin-bottom: 10px; margin-top: 0; text-align: center">udzielone w dniu {{ bp_human_date($data_pelnomocnictwa, 'dot') }} r.

        @if(false)

        @if($miejsce_podpisania == 'Głogów')w Głogowie
        @elseif($miejsce_podpisania == 'Legnica')w Legnicy
        @elseif($miejsce_podpisania == 'Leszno')w Lesznie
        @elseif($miejsce_podpisania == 'Wrocław')we Wrocławiu
        @elseif($miejsce_podpisania == 'Zielona Góra')w Zielonej Górze
        @endif
        (woj.
        @if($miejsce_podpisania == 'Głogów' || $miejsce_podpisania == 'Legnica' || $miejsce_podpisania == 'Wrocław')dolnośląskie
        @elseif($miejsce_podpisania == 'Zielona Góra')lubuskie
        @elseif($miejsce_podpisania == 'Leszno')wielkopolskie
        @endif
        , Polska)

        @endif

    </p>

    <div style="margin-left: 0px">
        @if($kredytobiorcy)

            <div class="bold">Mocodawc{{ $kredytobiorcy->count() === 1 ? 'a' : 'y' }}</div>
            @foreach($kredytobiorcy as $klient)
                {{ $klient->label }}, PESEL: {{ $klient->pesel  }}<br>
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

    <dl class="mt-5">
        <dt>1.</dt>
        <dd>
            {{ $kredytobiorcy->count() === 1 ? 'Mocodawca upoważnia' : 'Mocodawcy upoważniają' }} {{ $pelnomocnicy->count() === 1 ? 'adw.' : 'adwokatów:' }}
            @if(false)
            __________________________________________________
            @else

            @foreach($pelnomocnicy as $pelnomocnik)
                {{ $pelnomocnik->name_genitive }}
                @if($loop->first && $pelnomocnicy->count() === 2)
                    oraz
                @endif
            @endforeach

            {{-- adwokat Wiktorię Rajzynger --}}
@endif z kancelarii PRĘDA Kancelaria Adwokacka do dochodzenia w {{ $kredytobiorcy->count() === 1 ? 'jego' : 'ich' }} imieniu wszelkich roszczeń związanych z
@if($pozyczka){{ $e->credits->count() === 1 ? 'opisaną wyżej umową pożyczki' : 'każdą z opisanych wyżej umów pożyczki' }}@else{{ $e->credits->count() === 1 ? 'opisaną wyżej umową kredytową' : 'każdą z opisanych wyżej umów kredytowych' }}@endif, w szczególności roszczenia o ustalenie nieważności umowy, o ustalenie bezskuteczności poszczególnych postanowień umowy, ustalenie braku obowiązku zapłaty wynagrodzenia za korzystanie z kapitału i tym podobnych roszczeń, roszczeń o zapłatę, w tym również wynikających ze stwierdzenia nieważności umowy, a także do prowadzenia postępowania o wykreślenie hipoteki zabezpieczającej wierzytelności wynikające z @if($pozyczka){{ $e->credits->count() === 1 ? 'opisanej wyżej umowy pożyczki' : 'każdej z opisanych wyżej umów pożyczki' }}@else{{ $e->credits->count() === 1 ? 'opisanej wyżej umowy kredytowej' : 'każdej z opisanych wyżej umów kredytowych' }}@endif.

        </dd>
    </dl>


    @if($pelnomocnictwo_powodztwo_banku)

    <dl class="mt-5">
        <dt>2.</dt>
        <dd>

            Niniejsze pełnomocnictwo obejmuje również umocowanie do prowadzenia wszelkich spraw z powództwa banku {{ $umowa->current_banks->organization }} przeciwko {{ $pozyczka ? 'pożyczkobiorc' : 'kredytobiorc' }}{{ $kredytobiorcy->count() === 1 ? 'y' : 'om' }} (mocodawc{{ $kredytobiorcy->count() === 1 ? 'y' : 'om' }}), związanych z opisaną wyżej umową {{ $pozyczka ? 'pożyczki' : 'kredytową' }}, w szczególności sprawy o zwrot kapitału, zapłatę wynagrodzenia za korzystanie z kapitału, waloryzację kapitału, itp.

        </dd>
    </dl>

    @endif


    <dl class="mt-5">
        <dt>{{ $pelnomocnictwo_powodztwo_banku ? 3 : 2 }}.</dt>
        <dd>
            Niniejsze pełnomocnictwo upoważnia {{ $pelnomocnicy->count() > 1 ? 'każdego z pełnomocników' : '' }}do wszelkich czynności procesowych, w tym do reprezentacji przed sądem I i II instancji oraz przed Sądem Najwyższym, do reprezentowania w postępowaniu egzekucyjnym, a nadto do wszelkich czynności pozaprocesowych i polubownych, odbioru świadczeń, wskazania numeru rachunku bankowego, na które świadczenia mają być przelane, odbioru wszelkiej korespondencji w sprawach dotyczących przedmiotu pełnomocnictwa, odbioru dokumentacji związanej z umową {{ $pozyczka ? 'pozyczki' : 'kredytu' }} o numerze wyżej wskazanym od banku oraz udzielania dalszych pełnomocnictw.
        </dd>
    </dl>

    <dl class="mt-5" style="margin-bottom: 70px">
        <dt>{{ $pelnomocnictwo_powodztwo_banku ? 4: 3 }}.</dt>
        <dd>
            {{ $kredytobiorcy->count() === 1 ? 'Mocodawca' : 'Mocodawcy' }} – zgodnie z art. 104 ust. 3 Ustawy z dnia 29.09.1997 r. Prawo Bankowe (Dz. U. 2016.1988) – {{ $kredytobiorcy->count() === 1 ? 'upoważnia' : 'upoważniają' }} {{ $e->credits[0]->current_banks->organization }} do ujawnienia i przekazywania {{ $pelnomocnicy->count() > 1 ? 'każdemu z pełnomocników' : 'pełnomocnikowi' }} wszelkich dokumentów i informacji objętych tajemnicą bankową dotyczących @if($pozyczka){{ $e->credits->count() === 1 ? 'opisanej na wstępie umowy pożyczki' : 'opisanych na wstępie umów pożyczki' }}@else{{ $e->credits->count() === 1 ? 'opisanej na wstępie umowy kredytowej' : 'opisanych na wstępie umów kredytowych' }}@endif, niezbędnych do wykonania niniejszego pełnomocnictwa, w tym zwłaszcza do dochodzenia roszczeń.
        </dd>
    </dl>

    @if($kredytobiorcy)

        @foreach($kredytobiorcy->sortBy('sort') as $klient)
            <div style="width: 40%; margin: 0 25px; float: left; border-top: 1px solid #000;" class="bold">
                <small>{{ $klient->label }}</small>
            </div>
            @if($loop->iteration % 2 == 0)<br><br><br><br>@endif
        @endforeach

    @endif


</div>
