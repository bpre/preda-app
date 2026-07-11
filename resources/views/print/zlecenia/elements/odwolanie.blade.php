<div style="page-break-after: always">


    <p style="margin-bottom: 10px; margin-top: 0; text-align: right"> {{ bp_human_date($data_pelnomocnictwa, 'dot') }} r.


    <h1 style="text-align: center; margin-bottom: 0">Odwołanie {{ $pelnomocnictwo_powodztwo_banku ? 'pełnomocnictw' : 'pełnomocnictwa' }}</h1>


    <br><br>

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
                {{ $e->credits->count() > 1 ? 'Umowy kredytowe' : 'Umowa kredytowa' }}
            </div>
            @foreach($e->credits as $umowa)
                Nr: {{ $umowa->number }} z dnia {{ bp_human_date($umowa->date, 'dot') }} r. zawarta z: {{ $umowa->former_banks->organization }}<br>
            @endforeach

        @else
            <span class="text-red-500">BRAK UMÓW!</span>
        @endif


    </div>

    <br><br>

    <p class="mt-5">
        {{ $kredytobiorcy->count() === 1 ? 'Mocodawca odwołuje' : 'Mocodawcy odwołują' }}

        {{ $pelnomocnictwo_powodztwo_banku ? 'pełnomocnictwa' : 'pełnomocnictwo' }} z dnia {{ bp_human_date($e->date, 'dot') }} r. udzielone radcy prawnemu Mateuszowi Wilkowi do prowadzenia {{ $pelnomocnictwo_powodztwo_banku ? 'spraw związanych' : 'sprawy związanej' }} ze

        {{ $e->credits->count() === 1 ? 'wskazaną wyżej umową kredytową' : 'wskazanymi wyżej umowami kredytowymi' }},

        zwalniając radcę prawnego Mateusza Wilka z obowiązku dalszego działania z dniem 29.12.2025 r.
    </p>


    <br><br><br>


    @if($kredytobiorcy)

        @foreach($kredytobiorcy->sortBy('sort') as $klient)
            <div style="width: 40%; margin: 0 25px; float: left; border-top: 1px solid #000;" class="bold">
                <small>{{ $klient->label }}</small>
            </div>
            @if($loop->iteration % 2 == 0)<br><br><br><br>@endif
        @endforeach

    @endif


</div>
