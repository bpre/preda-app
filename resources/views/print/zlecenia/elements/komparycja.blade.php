@include('print.zlecenia.elements.style')

    <div>
        <div class="center">
            <span class="bold">Umowa o świadczenie usług prawnych</span>
            <br>zawarta w dniu {{ bp_human_date($e['date'], 'dot') }} r.
        </div>

        @if($kredytobiorcy)
            <div class="bold">Klient</div>
            @foreach($kredytobiorcy as $klient)
                {{ $klient->label }}, PESEL: {{ $klient->pesel  }}, {{ $klient->adr }}{{ $klient->email ? ', '.$klient->email : '' }}<br>
            @endforeach
        @else
            <span class="text-red-500">BRAK KLIENTÓW!</span>
        @endif

        <div class="mt-5 bold">Kancelaria</div>
        <div>
            PRĘDA Kancelaria Adwokacka - Adwokat Bartosz Pręda, ul. Szewska 7, 67-200 Głogów
            <br>NIP: 6922321750, nr rachunku bankowego: 97 1090 1290 0000 0001 3186 6931, reprezentowana przez {{  $reprezentant }}<br>
        </div>

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
