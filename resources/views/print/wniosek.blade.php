<div>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>body { font-family: 'RobotoCondensed'; font-size: 13px; margin: 0.5cm 1cm 0.5cm 1.5cm; }</style>

    <p style="text-align: right">@if($wnioskodawca->city){{  $wnioskodawca->city }}, @endif{{ bp_human_date($date, 'dot') }} r.</p>
    <p>
       {{ $wnioskodawca->label }}@if($wnioskodawca->pesel) (PESEL: {{  $wnioskodawca->pesel }})@endif
        @if($wnioskodawca->address && $wnioskodawca->city)
            <br />{{ $wnioskodawca->address }}
            <br />{{ trim(($wnioskodawca->zip_code ? $wnioskodawca->zip_code.' ' : '').$wnioskodawca->city) }}
        @else
            <span style="font-weight: bold; color: red"><br>UZUPEŁNIJ ADRES WNIOSKODAWCY!</span>
        @endif

    </p>

    <p>

        Numer umowy: {{ $e->number }}<br />
        Bank: {{ $e->former_banks->organization }}<br />
        Data na umowie: {{ bp_human_date($e->date, 'dot') }} r.<br />

    </p>

    <p style="margin-left: 350px; font-weight: bold">
            {{ $e->current_banks->organization }}
            @if($e->current_banks->address && $e->current_banks->zip_code && $e->current_banks->city)
                <br />{{ $e->current_banks->address }}
                <br />{{ $e->current_banks->zip_code }} {{ $e->current_banks->city }}
            @else
                <span style="font-weight: bold; color: red"><br>UZUPEŁNIJ ADRES WNIOSKODAWCY!</span>
            @endif
    </p>

    <h1 style="margin: 50px 0; text-align: center">Wniosek o wydanie zaświadczenia
        @if($dokumenty)
        i dokumentów
        @elseif($regulamin)
        i regulaminu
        @endif
    </h1>

    <p>Uprzejmie proszę o wydanie zaświadczenia o zaciągniętym przeze mnie kredycie hipotecznym o wskazanym wyżej numerze, zawierającego następujące informacje:</p>
    <ol>
        <li>dane kredytobiorcy,</li>
        <li>numer umowy kredytowej,</li>
        <li>wysokość udzielonego kredytu,</li>
        <li>daty i kwoty wypłaty kredytu (poszczególnych transz), ze wskazaniem numeru i posiadacza rachunku bankowego, na który nastąpiła wypłata,</li>
        <li>historię zmian oprocentowania,</li>
        <li>aktualne saldo zadłużenia,</li>
        <li>okres kredytowania,</li>
        <li>historię wszystkich dokonywanych wpłat związanych z kredytem (ze wskazaniem czy wpłata została dokonana w PLN czy w {{ $waluta }}, dat i kwot poszczególnych wpłat oraz informacji, czy wpłata stanowiła spłatę kapitału, spłatę odsetek, zapłatę prowizji, zapłatę innych opłat okołokredytowych, zapłatę składki ubezpieczeniowej, itd. – a także ze wskazaniem kursu {{ $waluta }} banku z dnia dokonania wpłaty),</li>
        <li>koszty okołokredytowe, w tym koszty ubezpieczeń wskazanych w umowie kredytowej, pobrane przez bank (w szczególności prowizje i składki ubezpieczeń), ze wskazaniem, czy koszty te zostały zapłacone przez kredytobiorcę (jeśli tak – ze wskazaniem daty i kwoty płatności), czy też zostały potrącone z kwoty udzielonego kredytu,</li>
        <li>wysokości opłaty pobranej za wydanie zaświadczenia.</li>
    </ol>

    @if($dokumenty || $regulamin)
        <p>Ponadto wnoszę o wydanie

            @if($dokumenty && $regulamin)
                kopii wniosku kredytowego, stanowiącego podstawę udzielenia kredytu,     <strong> kopii regulaminu do umowy</strong> oraz oświadczeń podpisywanych przy zawieraniu umowy.
            @elseif($dokumenty && !$regulamin)
                kopii wniosku kredytowego, stanowiącego podstawę udzielenia kredytu oraz oświadczeń podpisywanych przy zawieraniu umowy.
            @elseif($regulamin && !$dokumenty)
                <strong>kopii regulaminu do umowy</strong>.
            @endif
    @endif

    <p>Zobowiązuję się pokryć ewentualne koszty związane z wydaniem zaświadczenia i dokumentów.</p>


</div>
